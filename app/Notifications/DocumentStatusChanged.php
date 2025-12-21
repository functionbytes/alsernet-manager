<?php

namespace App\Notifications;

use App\Models\Notifications\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DocumentStatusChanged extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public mixed $document,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        // Verificar preferencias del usuario para cada canal
        if (NotificationPreference::isEnabled($notifiable->id, 'in_app', 'document.status_changed')) {
            $channels[] = 'database';
        }

        if (NotificationPreference::isEnabled($notifiable->id, 'push', 'document.status_changed')) {
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
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'title' => 'Documento actualizado',
            'message' => "El documento #{$this->document->number} cambió de {$this->oldStatus} a {$this->newStatus}",
            'icon' => 'fas fa-file-alt',
            'color' => $this->getColorByStatus($this->newStatus),
            'action_url' => route('manager.documents.show', $this->document->id),
            'action_text' => 'Ver documento',
            'priority' => 'normal',
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
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'title' => 'Documento actualizado',
            'message' => "El documento #{$this->document->number} cambió a {$this->newStatus}",
            'icon' => 'fas fa-file-alt',
            'color' => $this->getColorByStatus($this->newStatus),
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
        return 'document.status_changed';
    }

    /**
     * Determinar color según el estado del documento
     */
    protected function getColorByStatus(string $status): string
    {
        return match (strtolower($status)) {
            'approved', 'completed', 'paid' => 'success',
            'pending', 'processing' => 'warning',
            'rejected', 'cancelled', 'failed' => 'danger',
            default => 'info',
        };
    }
}
