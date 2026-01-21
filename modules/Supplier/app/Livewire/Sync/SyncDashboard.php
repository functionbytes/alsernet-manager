<?php

namespace Modules\Supplier\Livewire\Sync;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Supplier\Models\SupplierSyncStatus;

/**
 * Main sync dashboard component
 *
 * Displays comprehensive sync statistics and activities with real-time updates
 * via broadcast events. Manages overall sync operation orchestration and displays
 * recent sync history in a dashboard format.
 *
 * Permissions required: can_sync_suppliers
 */
class SyncDashboard extends Component
{
    use AuthorizesRequests;

    public ?SupplierSyncStatus $activeSync = null;

    public Collection $syncHistory;

    public array $statistics = [
        'total_synced' => 0,
        'pending' => 0,
        'failed' => 0,
        'last_sync' => null,
    ];

    /**
     * Component initialization
     */
    public function mount(): void
    {
        $this->authorize('can_sync_suppliers');

        $this->syncHistory = collect();
        $this->refreshStats();
        $this->loadActiveSyncs();
    }

    /**
     * Load active sync operations
     */
    private function loadActiveSyncs(): void
    {
        $activeSyncs = SupplierSyncStatus::where('status', 'running')
            ->latest('started_at')
            ->first();

        if ($activeSyncs) {
            $this->activeSync = $activeSyncs;
        }
    }

    /**
     * Refresh sync statistics
     */
    public function refreshStats(): void
    {
        $this->statistics = [
            'total_synced' => SupplierSyncStatus::completed()->count(),
            'pending' => SupplierSyncStatus::where('status', 'pending')->count(),
            'failed' => SupplierSyncStatus::failed()->count(),
            'last_sync' => SupplierSyncStatus::completed()
                ->latest('completed_at')
                ->value('completed_at'),
        ];

        $this->loadSyncHistory();
    }

    /**
     * Load recent sync activities for the history display
     */
    private function loadSyncHistory(): void
    {
        $this->syncHistory = SupplierSyncStatus::query()
            ->with(['supplier:id,name'])
            ->whereIn('status', ['completed', 'failed'])
            ->latest('completed_at')
            ->limit(10)
            ->get()
            ->map(function (SupplierSyncStatus $sync) {
                return [
                    'id' => $sync->id,
                    'type' => $sync->sync_type_name,
                    'status' => $sync->status_name,
                    'started_at' => $sync->started_at,
                    'completed_at' => $sync->completed_at,
                    'total_items' => $sync->total_items,
                    'synced_items' => $sync->synced_items,
                    'failed_items' => $sync->failed_items,
                    'elapsed_seconds' => $sync->elapsed_seconds,
                    'success_rate' => $sync->success_rate,
                ];
            });
    }

    /**
     * Start a new sync operation
     *
     * Creates and dispatches a sync job for the specified type
     */
    public function startSync(string $type): void
    {
        try {
            $this->authorize('can_sync_suppliers');

            $batch = \Modules\Supplier\Models\SupplierSyncBatch::create([
                'sync_type' => $type,
                'sync_scope' => 'all',
                'status' => 'queued',
            ]);

            $this->dispatch('sync-started', syncType: $type, batchId: $batch->id);

            session()->flash('message', "Sincronización de {$type} iniciada correctamente.");
        } catch (\Exception $e) {
            session()->flash('error', "Error al iniciar sincronización: {$e->getMessage()}");
        }
    }

    /**
     * Stop/cancel active sync operation
     */
    public function stopSync(): void
    {
        try {
            $this->authorize('can_sync_suppliers');

            if ($this->activeSync) {
                $this->activeSync->update(['status' => 'paused']);

                $this->dispatch('sync-stopped', statusId: $this->activeSync->id);

                session()->flash('message', 'Sincronización pausada correctamente.');

                $this->loadActiveSyncs();
            }
        } catch (\Exception $e) {
            session()->flash('error', "Error al pausar sincronización: {$e->getMessage()}");
        }
    }

    /**
     * Handle sync progress updates from broadcast
     */
    #[On('sync-progress-updated')]
    public function handleSyncProgress(array $data): void
    {
        if ($this->activeSync && $this->activeSync->id == $data['statusId']) {
            $this->activeSync->refresh();
        }
    }

    /**
     * Handle sync completion from broadcast
     */
    #[On('sync-completed')]
    public function handleSyncCompleted(array $data): void
    {
        if ($this->activeSync && $this->activeSync->id == $data['statusId']) {
            $this->activeSync = null;
            $this->refreshStats();
        }
    }

    /**
     * Handle sync failure from broadcast
     */
    #[On('sync-failed')]
    public function handleSyncFailed(array $data): void
    {
        if ($this->activeSync && $this->activeSync->id == $data['statusId']) {
            $this->activeSync->refresh();
        }
    }

    public function render()
    {
        return view('supplier::livewire.sync.sync-dashboard');
    }
}
