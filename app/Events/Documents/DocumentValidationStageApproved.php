<?php

namespace App\Events\Documents;

use App\Models\Document\Document;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * DocumentValidationStageApproved Event
 *
 * Fired when a document validation stage is approved.
 * Used to trigger stage-specific notifications and email sending.
 */
class DocumentValidationStageApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Document $document,
        public User $approvedBy,
        public int $stageNumber,
        public string $stageKey,
        public bool $isFinalApproval,
        public ?string $comments = null,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('documents.'.$this->document->id),
        ];
    }

    /**
     * Check if this is the first stage
     */
    public function isFirstStage(): bool
    {
        return $this->stageNumber === 1;
    }

    /**
     * Check if this is the last stage
     */
    public function isLastStage(): bool
    {
        return $this->isFinalApproval;
    }

    /**
     * Check if this is an intermediate stage
     */
    public function isIntermediateStage(): bool
    {
        return ! $this->isFirstStage() && ! $this->isLastStage();
    }

    /**
     * Get the stage label
     */
    public function getStageLabelAttribute(): string
    {
        return match ($this->stageKey) {
            'documentacion' => 'Documentación',
            'licencias' => 'Licencias',
            'contabilidad' => 'Contabilidad',
            default => ucfirst($this->stageKey),
        };
    }
}
