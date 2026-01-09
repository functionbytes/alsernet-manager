<?php

namespace Modules\Document\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Modules\Document\Entities\Document;
use Modules\Notification\Models\NotificationPreference;

class DocumentStageAdvanced extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Document $document,
        public int $previousStage,
        public int $currentStage,
        public string $currentValidatorGroup
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        try {
            // Check notification preferences if the system is available
            if (NotificationPreference::isEnabled($notifiable->id, 'in_app', 'document.stage.advanced')) {
                $channels[] = 'database';
            }

            if (NotificationPreference::isEnabled($notifiable->id, 'push', 'document.stage.advanced')) {
                $channels[] = 'broadcast';
            }
        } catch (\Exception $e) {
            // If notification preferences system is not available, send both types by default
            $channels = ['database', 'broadcast'];
        }

        return $channels;
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $stageLabel = $this->document->getCurrentStageLabel();
        $orderRef = $this->document->order_reference ?? $this->document->order_id;

        return [
            'document_id' => $this->document->id,
            'document_uid' => $this->document->uid,
            'order_id' => $this->document->order_id,
            'order_reference' => $orderRef,
            'previous_stage' => $this->previousStage,
            'current_stage' => $this->currentStage,
            'total_stages' => $this->document->total_stages,
            'current_validator_group' => $this->currentValidatorGroup,
            'stage_label' => $stageLabel,
            'title' => 'Nuevo documento asignado',
            'message' => "El documento #{$orderRef} requiere validación en etapa {$this->currentStage}/{$this->document->total_stages} - {$stageLabel}",
            'icon' => 'fas fa-file-check',
            'color' => 'primary',
            'action_url' => route('documents.manage', $this->document->uid),
            'action_text' => 'Ver documento',
            'priority' => 'high',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $stageLabel = $this->document->getCurrentStageLabel();
        $orderRef = $this->document->order_reference ?? $this->document->order_id;

        return new BroadcastMessage([
            'document_id' => $this->document->id,
            'document_uid' => $this->document->uid,
            'order_id' => $this->document->order_id,
            'order_reference' => $orderRef,
            'current_stage' => $this->currentStage,
            'total_stages' => $this->document->total_stages,
            'stage_label' => $stageLabel,
            'title' => 'Nuevo documento asignado',
            'message' => "El documento #{$orderRef} requiere validación en etapa {$this->currentStage}/{$this->document->total_stages} - {$stageLabel}",
            'icon' => 'fas fa-file-check',
            'color' => 'primary',
            'action_url' => route('documents.manage', $this->document->uid),
            'created_at' => now()->toIso8601String(),
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function broadcastType(): string
    {
        return 'document.stage.advanced';
    }

    /**
     * Specify the channels that the notification should be broadcast on
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.'.$this->notifiable->id),
        ];
    }
}
