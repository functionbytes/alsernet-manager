<?php

namespace App\Listeners\Systems;

use App\Models\Setting;
use Modules\Campaign\Events\CronJobExecuted;

class CronJobExecutedListener
{
    public function __construct() {}

    public function handle(CronJobExecuted $event)
    {
        Setting::set('cronjob_last_execution', \Carbon\Carbon::now()->timestamp);
    }
}
