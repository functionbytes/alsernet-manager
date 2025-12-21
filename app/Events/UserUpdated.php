<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UserUpdated extends Event
{
    use SerializesModels;

    public $customer;

    public $delayed;

    public function __construct($customer, $delayed = true)
    {
        $this->customer = $customer;
        $this->delayed = $delayed;
    }

    public function broadcastOn()
    {
        return [];
    }
}
