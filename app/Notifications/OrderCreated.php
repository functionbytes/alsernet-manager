<?php

namespace App\Notifications;

use App\Models\Notifications\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderCreated extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public mixed $order
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
        if (NotificationPreference::isEnabled($notifiable->id, 'in_app', 'order.created')) {
            $channels[] = 'database';
        }

        if (NotificationPreference::isEnabled($notifiable->id, 'push', 'order.created')) {
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
            'order_id' => $this->order->id,
            'order_number' => $this->order->number ?? $this->order->id,
            'customer_name' => $this->order->customer->full_name ?? 'Cliente',
            'amount' => $this->order->total ?? 0,
            'title' => 'Nuevo pedido recibido',
            'message' => "El pedido #{$this->order->id} ha sido creado",
            'icon' => 'fas fa-shopping-cart',
            'color' => 'success',
            'action_url' => route('manager.orders.show', $this->order->id),
            'action_text' => 'Ver pedido',
            'priority' => 'normal',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'order_number' => $this->order->number ?? $this->order->id,
            'title' => 'Nuevo pedido recibido',
            'message' => "El pedido #{$this->order->id} ha sido creado",
            'icon' => 'fas fa-shopping-cart',
            'color' => 'success',
            'action_url' => route('manager.orders.show', $this->order->id),
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
        return 'order.created';
    }
}
