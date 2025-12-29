<?php

namespace App\Events\Campaigns;

use App\Models\Campaign\CampaignMaillist;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MailListImported
{
    use Dispatchable ,InteractsWithSockets ,SerializesModels;

    public $list;

    public $importBatchId;

    public function __construct(CampaignMaillist $list, $importBatchId)
    {
        $this->list = $list;
        $this->importBatchId = $importBatchId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
