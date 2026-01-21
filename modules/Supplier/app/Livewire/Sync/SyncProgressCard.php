<?php

namespace Modules\Supplier\Livewire\Sync;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Supplier\Models\SupplierSyncStatus;

/**
 * Shows progress for a single sync operation
 *
 * Displays real-time progress updates including progress bar, metrics,
 * and status indicators. Subscribes to private broadcast channel for
 * live progress updates.
 *
 * Properties:
 * - $statusId: The sync status ID being monitored
 * - $syncType: Type of sync operation
 * - $status: Current sync status record
 */
class SyncProgressCard extends Component
{
    use AuthorizesRequests;

    public string $statusId;

    public string $syncType = '';

    public ?SupplierSyncStatus $status = null;

    public array $metrics = [
        'total_items' => 0,
        'processed_items' => 0,
        'failed_items' => 0,
        'percent_complete' => 0,
        'current_phase' => 'Iniciando...',
    ];

    /**
     * Component initialization with status ID
     */
    public function mount(string $statusId): void
    {
        $this->statusId = $statusId;
        $this->loadStatus();
    }

    /**
     * Load sync status from database
     */
    private function loadStatus(): void
    {
        $this->status = SupplierSyncStatus::find($this->statusId);

        if ($this->status) {
            $this->syncType = $this->status->sync_type_name;
            $this->updateMetrics();
        }
    }

    /**
     * Update metrics from current status
     */
    private function updateMetrics(): void
    {
        if ($this->status) {
            $this->metrics = [
                'total_items' => $this->status->total_items,
                'processed_items' => $this->status->synced_items,
                'failed_items' => $this->status->failed_items,
                'percent_complete' => $this->status->progress_percentage,
                'current_phase' => $this->getPhaseLabel(),
            ];
        }
    }

    /**
     * Get human-readable phase label based on status
     */
    private function getPhaseLabel(): string
    {
        return match ($this->status?->status) {
            'pending' => 'Pendiente',
            'running' => 'En progreso...',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            'paused' => 'Pausado',
            default => 'Desconocido',
        };
    }

    /**
     * Handle progress updates from broadcast
     */
    #[On('sync-progress-updated')]
    public function updateProgress(array $data): void
    {
        if ($data['statusId'] === $this->statusId) {
            $this->metrics = [
                'total_items' => $data['totalItems'],
                'processed_items' => $data['processedItems'],
                'failed_items' => $data['failedItems'],
                'percent_complete' => $data['percentComplete'],
                'current_phase' => $data['currentPhase'],
            ];
        }
    }

    /**
     * Handle sync completion from broadcast
     */
    #[On('sync-completed')]
    public function handleCompletion(array $data): void
    {
        if ($data['statusId'] === $this->statusId) {
            $this->loadStatus();
            $this->dispatch('sync-finished', statusId: $this->statusId);
        }
    }

    /**
     * Handle sync failure from broadcast
     */
    #[On('sync-failed')]
    public function handleFailure(array $data): void
    {
        if ($data['statusId'] === $this->statusId) {
            $this->loadStatus();
            session()->flash('error', $data['errorMessage'] ?? 'Error during sync');
            $this->dispatch('sync-failed-event', statusId: $this->statusId);
        }
    }

    public function render()
    {
        return view('supplier::livewire.sync.sync-progress-card');
    }
}
