<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class CampaignUpdated extends Event
{
    use SerializesModels;

    public $campaign;

    public $delayed;

    public function __construct($campaign, $delayed = true)
    {
        $this->campaign = $campaign;
        $this->delayed = $delayed;
    }

    public function broadcastOn()
    {
        return [];
    }
}
