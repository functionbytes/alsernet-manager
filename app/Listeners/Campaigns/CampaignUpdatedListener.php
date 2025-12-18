<?php

namespace App\Listeners\Campaigns;

use App\Events\CampaignUpdated;
use App\Jobs\UpdateCampaignJob;

class CampaignUpdatedListener
{

    public function __construct()
    {
    }

    public function handle(CampaignUpdated $event)
    {
        if ($event->delayed) {
            dispatch(new UpdateCampaignJob($event->campaign));
        } else {
            $event->campaign->updateCache();
        }
    }

}
