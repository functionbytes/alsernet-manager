<?php

namespace Modules\Document\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Document\Entities\Document;

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

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('documents.'.$this->document->id),
        ];
    }

    public function isFirstStage(): bool
    {
        return $this->stageNumber === 1;
    }

    public function isLastStage(): bool
    {
        return $this->isFinalApproval;
    }

    public function isIntermediateStage(): bool
    {
        return ! $this->isFirstStage() && ! $this->isLastStage();
    }

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
