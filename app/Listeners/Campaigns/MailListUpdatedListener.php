<?php

namespace App\Listeners\Campaigns;

use App\Events\Campaigns\MailListUpdated;
use App\Jobs\UpdateMailListJob;

class MailListUpdatedListener
{
    public function __construct() {}

    public function handle(MailListUpdated $event)
    {
        dispatch(new UpdateMailListJob($event->mailList));
    }
}
