<?php

namespace App\Notifications;

use App\Models\Notifications\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DocumentRejected extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public mixed $document,
        public mixed $rejectedBy,
        public ?string $reason = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'in_app', 'document.rejected')) {
            $channels[] = 'database';
        }

        if (NotificationPreference::isEnabled($notifiable->id, 'push', 'document.rejected')) {
            $channels[] = 'broadcast';
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
        $message = "El documento #{$this->document->number} ha sido rechazado";
        if ($this->reason) {
            $message .= ": {$this->reason}";
        }

        return [
            'document_id' => $this->document->id,
            'document_number' => $this->document->number ?? $this->document->id,
            'rejected_by' => $this->rejectedBy->name ?? 'Sistema',
            'reason' => $this->reason,
            'title' => 'Documento rechazado',
            'message' => $message,
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'action_url' => route('manager.documents.show', $this->document->id),
            'action_text' => 'Ver documento',
            'priority' => 'high',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $message = "El documento #{$this->document->number} ha sido rechazado";
        if ($this->reason) {
            $message .= ": {$this->reason}";
        }

        return new BroadcastMessage([
            'document_id' => $this->document->id,
            'document_number' => $this->document->number ?? $this->document->id,
            'title' => 'Documento rechazado',
            'message' => $message,
            'icon' => 'fas fa-times-circle',
            'color' => 'danger',
            'action_url' => route('manager.documents.show', $this->document->id),
            'created_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Determinar el tipo de notificación para broadcasting
     */
    public function broadcastType(): string
    {
        return 'document.rejected';
    }
}
