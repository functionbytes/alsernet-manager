<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class AdminLoggedIn extends Event
{
    use SerializesModels;

    protected $admin;

    public function __construct($admin = null)
    {
        $this->admin = $admin;
    }

    public function broadcastOn()
    {
        return [];
    }
}
