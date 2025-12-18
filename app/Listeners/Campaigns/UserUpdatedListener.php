<?php

namespace App\Listeners\Campaigns;

use App\Events\UserUpdated;
use App\Jobs\UpdateUserJob;

class UserUpdatedListener
{
    public function __construct()
    {
    }

    public function handle(UserUpdated $event)
    {
        dispatch(new UpdateUserJob($event->customer));
    }

}
