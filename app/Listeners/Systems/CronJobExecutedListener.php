<?php

namespace App\Listeners\Systems;

use App\Events\CronJobExecuted;
use App\Models\Setting;

class CronJobExecutedListener
{
    public function __construct()
    {
    }

    public function handle(CronJobExecuted $event)
    {
        Setting::set('cronjob_last_execution', \Carbon\Carbon::now()->timestamp);
    }

}
