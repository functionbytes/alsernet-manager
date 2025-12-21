<?php

namespace App\Notifications;

use App\Models\Notifications\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UserMentioned extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public mixed $mentionedBy,
        public string $context,
        public string $contextUrl,
        public ?string $excerpt = null
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if (NotificationPreference::isEnabled($notifiable->id, 'in_app', 'user.mentioned')) {
            $channels[] = 'database';
        }

        if (NotificationPreference::isEnabled($notifiable->id, 'push', 'user.mentioned')) {
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
        $mentionedByName = $this->mentionedBy->name ?? 'Alguien';
        $message = "{$mentionedByName} te ha mencionado en {$this->context}";
        if ($this->excerpt) {
            $message .= ": \"{$this->excerpt}\"";
        }

        return [
            'mentioned_by_id' => $this->mentionedBy->id ?? null,
            'mentioned_by_name' => $mentionedByName,
            'context' => $this->context,
            'excerpt' => $this->excerpt,
            'title' => 'Te han mencionado',
            'message' => $message,
            'icon' => 'fas fa-at',
            'color' => 'info',
            'action_url' => $this->contextUrl,
            'action_text' => 'Ver mención',
            'priority' => 'normal',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $mentionedByName = $this->mentionedBy->name ?? 'Alguien';
        $message = "{$mentionedByName} te ha mencionado en {$this->context}";

        return new BroadcastMessage([
            'mentioned_by_id' => $this->mentionedBy->id ?? null,
            'mentioned_by_name' => $mentionedByName,
            'context' => $this->context,
            'title' => 'Te han mencionado',
            'message' => $message,
            'icon' => 'fas fa-at',
            'color' => 'info',
            'action_url' => $this->contextUrl,
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
        return 'user.mentioned';
    }
}
