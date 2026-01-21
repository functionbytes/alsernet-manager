<?php

namespace Modules\Supplier\Livewire\Sync;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Supplier\Models\SupplierSyncStatus;

/**
 * Timeline/log of sync activities
 *
 * Displays recent sync operations in chronological order with real-time
 * updates. Supports loading more entries and clearing the log.
 *
 * Properties:
 * - $activities: Collection of sync activities
 * - $perPage: Items to load per page
 */
class SyncActivityLog extends Component
{
    use AuthorizesRequests;

    public Collection $activities;

    public int $perPage = 20;

    private int $loadedCount = 0;

    /**
     * Component initialization
     */
    public function mount(): void
    {
        $this->authorize('can_sync_suppliers');

        $this->activities = collect();
        $this->loadActivities();
    }

    /**
     * Load recent sync activities
     */
    private function loadActivities(): void
    {
        $offset = $this->loadedCount;

        $statuses = SupplierSyncStatus::query()
            ->with(['supplier:id,name', 'logs' => function ($query) {
                $query->latest('created_at')->limit(1);
            }])
            ->latest('created_at')
            ->offset($offset)
            ->limit($this->perPage)
            ->get();

        $newActivities = $statuses->map(function (SupplierSyncStatus $sync) {
            $lastLog = $sync->logs->first();

            return [
                'id' => $sync->id,
                'type' => $sync->sync_type_name,
                'status' => $sync->status_name,
                'status_value' => $sync->status,
                'timestamp' => $sync->created_at,
                'completed_at' => $sync->completed_at,
                'items_processed' => $sync->synced_items,
                'items_failed' => $sync->failed_items,
                'items_total' => $sync->total_items,
                'supplier' => $sync->supplier?->name ?? 'Todos los proveedores',
                'triggered_by' => $sync->triggered_by,
                'message' => $lastLog?->message ?? 'Sin mensajes',
                'duration' => $sync->elapsed_seconds ? $this->formatDuration($sync->elapsed_seconds) : null,
            ];
        });

        if ($offset === 0) {
            $this->activities = $newActivities;
        } else {
            $this->activities = $this->activities->merge($newActivities);
        }

        $this->loadedCount += count($newActivities);
    }

    /**
     * Format duration in seconds to human-readable format
     */
    private function formatDuration(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds).' seg';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1).' min';
        } else {
            return round($seconds / 3600, 1).' h';
        }
    }

    /**
     * Load more activities
     */
    public function loadMore(): void
    {
        $this->loadActivities();
    }

    /**
     * Clear the activity log
     */
    public function clearLog(): void
    {
        try {
            $this->authorize('can_sync_suppliers');

            SupplierSyncStatus::query()->delete();

            $this->activities = collect();
            $this->loadedCount = 0;

            session()->flash('message', 'Registro de actividades eliminado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', "Error al limpiar el registro: {$e->getMessage()}");
        }
    }

    /**
     * Handle new sync started
     */
    #[On('sync-started')]
    public function handleSyncStarted(array $data): void
    {
        $this->loadActivities();
    }

    /**
     * Handle sync progress updates
     */
    #[On('sync-progress-updated')]
    public function handleProgressUpdate(array $data): void
    {
        $activity = $this->activities->firstWhere('id', $data['statusId']);

        if ($activity) {
            $activity['items_processed'] = $data['processedItems'];
            $activity['items_failed'] = $data['failedItems'];
        }
    }

    /**
     * Handle sync completion
     */
    #[On('sync-completed')]
    public function handleSyncCompleted(array $data): void
    {
        $activity = $this->activities->firstWhere('id', $data['statusId']);

        if ($activity) {
            $activity['status'] = 'Completado';
            $activity['status_value'] = 'completed';
            $activity['items_processed'] = $data['processedItems'];
            $activity['items_failed'] = $data['failedItems'];
            $activity['completed_at'] = now();
        }
    }

    /**
     * Handle sync failure
     */
    #[On('sync-failed')]
    public function handleSyncFailed(array $data): void
    {
        $activity = $this->activities->firstWhere('id', $data['statusId']);

        if ($activity) {
            $activity['status'] = 'Fallido';
            $activity['status_value'] = 'failed';
            $activity['items_processed'] = $data['processedItems'];
            $activity['items_failed'] = $data['failedItems'];
            $activity['completed_at'] = now();
            $activity['message'] = $data['errorMessage'] ?? 'Error desconocido';
        }
    }

    public function render()
    {
        return view('supplier::livewire.sync.sync-activity-log', [
            'activities' => $this->activities,
        ]);
    }
}
