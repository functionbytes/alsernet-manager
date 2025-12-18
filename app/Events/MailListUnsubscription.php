<?php

namespace App\Events;

use App\Models\Subscriber\Subscriber;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MailListUnsubscription
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $subscriber;

    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
