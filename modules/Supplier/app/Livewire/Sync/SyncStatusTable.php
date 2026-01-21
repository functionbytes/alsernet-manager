<?php

namespace Modules\Supplier\Livewire\Sync;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\Paginator;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Supplier\Models\SupplierSyncStatus;

/**
 * Table showing all sync statuses
 *
 * Displays paginated list of sync operations with real-time updates.
 * Supports filtering, sorting, and actions like retry and cancel.
 *
 * Properties:
 * - $perPage: Items per page for pagination
 * - $sortBy: Current sort field
 * - $filterStatus: Filter by status type
 */
class SyncStatusTable extends Component
{
    use AuthorizesRequests, WithPagination;

    public int $perPage = 15;

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public ?string $filterStatus = null;

    /**
     * Component initialization
     */
    public function mount(): void
    {
        $this->authorize('can_sync_suppliers');
    }

    /**
     * Get sync statuses with applied filters and sorting
     */
    public function getStatuses(): Paginator
    {
        return SupplierSyncStatus::query()
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->with(['supplier:id,name'])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /**
     * Update sort field and direction
     */
    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Filter by status
     */
    public function filterByStatus(?string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    /**
     * Retry a failed sync operation
     */
    public function retry(int $statusId): void
    {
        try {
            $this->authorize('can_sync_suppliers');

            $sync = SupplierSyncStatus::findOrFail($statusId);

            if (! $sync->canRetry()) {
                session()->flash('error', 'Esta sincronización no puede ser reiniciada.');

                return;
            }

            $sync->update([
                'status' => 'pending',
                'synced_items' => 0,
                'failed_items' => 0,
                'skipped_items' => 0,
                'completed_at' => null,
                'elapsed_seconds' => null,
            ]);

            $this->dispatch('sync-retry-initiated', statusId: $statusId);

            session()->flash('message', 'Sincronización reiniciada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', "Error al reiniciar sincronización: {$e->getMessage()}");
        }
    }

    /**
     * Cancel a running sync operation
     */
    public function cancel(int $statusId): void
    {
        try {
            $this->authorize('can_sync_suppliers');

            $sync = SupplierSyncStatus::findOrFail($statusId);

            if (! $sync->isRunning()) {
                session()->flash('error', 'Solo se pueden cancelar sincronizaciones en progreso.');

                return;
            }

            $sync->update(['status' => 'paused']);

            $this->dispatch('sync-cancelled', statusId: $statusId);

            session()->flash('message', 'Sincronización cancelada correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', "Error al cancelar sincronización: {$e->getMessage()}");
        }
    }

    /**
     * View details of a sync operation
     */
    public function view(int $statusId): void
    {
        $this->authorize('can_sync_suppliers');

        $this->dispatch('navigate-to-sync-detail', statusId: $statusId);
    }

    /**
     * Handle sync progress updates
     */
    #[On('sync-progress-updated')]
    public function handleProgressUpdate(array $data): void
    {
        $this->dispatch('$refresh');
    }

    /**
     * Handle sync completion
     */
    #[On('sync-completed')]
    public function handleSyncCompleted(array $data): void
    {
        $this->dispatch('$refresh');
    }

    /**
     * Handle sync failure
     */
    #[On('sync-failed')]
    public function handleSyncFailed(array $data): void
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('supplier::livewire.sync.sync-status-table', [
            'statuses' => $this->getStatuses(),
        ]);
    }
}
