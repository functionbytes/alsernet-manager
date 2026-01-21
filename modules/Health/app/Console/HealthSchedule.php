<?php

namespace Modules\Health\Console;

use Illuminate\Console\Scheduling\Schedule;
use Spatie\Health\Jobs\HealthQueueJob;

/**
 * Register health check scheduled tasks
 *
 * These tasks ensure that:
 * - Schedule heartbeat is written to cache every minute
 * - Queue health check job is dispatched every minute
 */
class HealthSchedule
{
    /**
     * Register health check scheduled tasks
     */
    public static function register(Schedule $schedule): void
    {
        // Health check heartbeat for scheduler
        $schedule->call(function () {
            cache()->put(
                'health:checks:schedule:latestHeartbeatAt',
                now()->timestamp,
                now()->addMinutes(10)
            );
        })
            ->name('health:schedule-heartbeat')
            ->everyMinute()
            ->withoutOverlapping();

        // Health check job for queue
        $schedule->call(function () {
            $check = new \Spatie\Health\Checks\Checks\QueueCheck();
            HealthQueueJob::dispatch($check);
        })
            ->name('health:queue-job')
            ->everyMinute()
            ->withoutOverlapping();
    }
}
