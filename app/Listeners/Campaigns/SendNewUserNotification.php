<?php

namespace App\Listeners\Campaigns;

use App\Traits\PreventsDuplicateEventExecution;

class SendNewUserNotification
{
    use PreventsDuplicateEventExecution;

    public function __construct() {}

    public function handle($event)
    {
        // Prevent duplicate execution within the same request
        if ($this->preventDuplicateExecution($event)) {
            return;
        }

        // TODO: Implement SendNewUserNotification logic
    }
}
