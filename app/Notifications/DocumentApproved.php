<?php

namespace App\Notifications;

use App\Models\Notifications\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DocumentApproved extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public mixed $document,
        public mixed $approvedBy
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'in_app', 'document.approved')) {
            $channels[] = 'database';
        }

        if (NotificationPreference::isEnabled($notifiable->id, 'push', 'document.approved')) {
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
        return [
            'document_id' => $this->document->id,
            'document_number' => $this->document->number ?? $this->document->id,
            'approved_by' => $this->approvedBy->name ?? 'Sistema',
            'title' => 'Documento aprobado',
            'message' => "El documento #{$this->document->number} ha sido aprobado",
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
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
        return new BroadcastMessage([
            'document_id' => $this->document->id,
            'document_number' => $this->document->number ?? $this->document->id,
            'title' => 'Documento aprobado',
            'message' => "El documento #{$this->document->number} ha sido aprobado",
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
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
        return 'document.approved';
    }
}
