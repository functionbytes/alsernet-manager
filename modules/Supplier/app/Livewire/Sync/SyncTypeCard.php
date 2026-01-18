<?php

namespace Modules\Supplier\Livewire\Sync;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Supplier\Models\SupplierSyncStatus;

/**
 * Card for individual sync type (reusable)
 *
 * Displays sync type information with progress bar, metrics, and action buttons.
 * Updates in real-time via broadcast events for a specific sync type.
 *
 * Properties:
 * - $syncType: Type of sync ('product', 'category', 'price', 'provider')
 * - $status: Current status record
 * - $metrics: Array of metric values
 */
class SyncTypeCard extends Component
{
    use AuthorizesRequests;

    public string $syncType;

    public ?SupplierSyncStatus $status = null;

    public array $metrics = [
        'total_items' => 0,
        'synced_items' => 0,
        'failed_items' => 0,
        'pending_items' => 0,
        'percent_complete' => 0,
        'success_rate' => 0,
        'last_sync_at' => null,
    ];

    public array $syncTypeConfig = [
        'product' => [
            'label' => 'Productos',
            'icon' => 'fas fa-box',
            'color' => 'primary',
        ],
        'category' => [
            'label' => 'Categorías',
            'icon' => 'fas fa-list',
            'color' => 'info',
        ],
        'price' => [
            'label' => 'Precios',
            'icon' => 'fas fa-tag',
            'color' => 'warning',
        ],
        'provider' => [
            'label' => 'Proveedores',
            'icon' => 'fas fa-truck',
            'color' => 'success',
        ],
    ];

    /**
     * Component initialization
     */
    public function mount(string $syncType): void
    {
        $this->authorize('can_sync_suppliers');

        $this->syncType = $syncType;
        $this->loadStatus();
    }

    /**
     * Load the latest sync status for this type
     */
    private function loadStatus(): void
    {
        $this->status = SupplierSyncStatus::byType($this->syncType)
            ->latest('created_at')
            ->first();

        if ($this->status) {
            $this->updateMetrics();
        }
    }

    /**
     * Update metrics from current status
     */
    private function updateMetrics(): void
    {
        if ($this->status) {
            $pendingItems = $this->status->total_items - $this->status->synced_items - $this->status->failed_items;

            $this->metrics = [
                'total_items' => $this->status->total_items,
                'synced_items' => $this->status->synced_items,
                'failed_items' => $this->status->failed_items,
                'pending_items' => max(0, $pendingItems),
                'percent_complete' => $this->status->progress_percentage,
                'success_rate' => $this->status->success_rate,
                'last_sync_at' => $this->status->completed_at ?? $this->status->started_at,
            ];
        }
    }

    /**
     * Get sync type configuration
     */
    public function getConfig(): array
    {
        return $this->syncTypeConfig[$this->syncType] ?? [
            'label' => ucfirst($this->syncType),
            'icon' => 'fas fa-cog',
            'color' => 'secondary',
        ];
    }

    /**
     * Trigger a new sync for this type
     */
    public function triggerSync(): void
    {
        try {
            $this->authorize('can_sync_suppliers');

            $batch = \Modules\Supplier\Models\SupplierSyncBatch::create([
                'sync_type' => $this->syncType,
                'sync_scope' => 'all',
                'status' => 'queued',
            ]);

            $this->dispatch('sync-initiated', syncType: $this->syncType, batchId: $batch->id);

            session()->flash('message', "Sincronización de {$this->getConfig()['label']} iniciada.");
        } catch (\Exception $e) {
            session()->flash('error', "Error al iniciar sincronización: {$e->getMessage()}");
        }
    }

    /**
     * Open details view for this sync type
     */
    public function openDetails(): void
    {
        $this->authorize('can_sync_suppliers');

        $this->dispatch('navigate-to-sync-type-details', syncType: $this->syncType);
    }

    /**
     * Handle progress updates for this sync type
     */
    #[On('sync-progress-updated')]
    public function handleProgressUpdate(array $data): void
    {
        if ($data['syncType'] === $this->syncType) {
            $this->metrics = [
                'total_items' => $data['totalItems'],
                'synced_items' => $data['processedItems'],
                'failed_items' => $data['failedItems'],
                'pending_items' => max(0, $data['totalItems'] - $data['processedItems'] - $data['failedItems']),
                'percent_complete' => $data['percentComplete'],
                'success_rate' => $data['totalItems'] > 0
                    ? round(($data['processedItems'] / $data['totalItems']) * 100, 2)
                    : 0,
                'last_sync_at' => now(),
            ];
        }
    }

    /**
     * Handle completion for this sync type
     */
    #[On('sync-completed')]
    public function handleCompletion(array $data): void
    {
        if ($data['syncType'] === $this->syncType) {
            $this->loadStatus();
        }
    }

    /**
     * Handle failure for this sync type
     */
    #[On('sync-failed')]
    public function handleFailure(array $data): void
    {
        if ($data['syncType'] === $this->syncType) {
            $this->loadStatus();
        }
    }

    public function render()
    {
        return view('supplier::livewire.sync.sync-type-card');
    }
}
