<?php

namespace App\Console;

use App\Events\CronJobExecuted;
use App\Models\Campaign\Automation\Automation;
use App\Models\Campaign\Campaign;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {

        if (! isInitiated()) {
            return;
        }

        // Make sure CLI process is NOT executed as root
        try {
            if (! exec_enabled()) {
                \Log::warning('The exec() function is missing or disabled on the hosting server');
            } elseif (@exec('whoami') == 'root') {
                \Log::warning("Cronjob process is executed by 'root' which might cause permission issues");
            }
        } catch (\Exception $e) {
            \Log::error('CronJob issue: '.$e->getMessage());
        }

        $schedule->call(function () {
            event(new CronJobExecuted);
        })->name('cronjob_event:log')->everyMinute();

        // Automation2
        $schedule->call(function () {
            Automation::run();
        })->name('automation:run')->everyFiveMinutes();

        // Bounce/feedback handler
        $schedule->command('handler:run')->everyThirtyMinutes();

        // Queued import/export/campaign
        // Allow overlapping: max 10 proccess as a given time (if cronjob interval is every minute)
        // Job is killed after timeout
        // $schedule->command('queue:work --queue=default,batch --timeout=120 --tries=1 --max-time=180')->everyMinute();

        // Make it more likely to have a running queue at any given time
        // Make sure it is stopped before another queue listener is created
        // $schedule->command('queue:work --queue=default,batch --timeout=120 --tries=1 --max-time=290')->everyFiveMinutes();

        // Sender verifying
        $schedule->command('sender:verify')->everyFiveMinutes();

        // System clean up
        $schedule->command('system:cleanup')->daily();

        // GeoIp database check
        $schedule->command('geoip:check')->everyMinute()->withoutOverlapping(60);

        // Check for scheduled campaign to execute
        $schedule->call(function () {
            Campaign::checkAndExecuteScheduledCampaigns();
        })->name('check_and_execute_scheduled_campaigns')->everyMinute();

        $schedule->command('imap:emailticket')->everyMinute();
        $schedule->command('ticket:autoclose')->everyMinute();
        $schedule->command('ticket:autooverdue')->everyMinute();
        $schedule->command('ticket:autoresponseticket')->everyMinute();
        $schedule->command('notification:autodelete')->everyMinute();
        $schedule->command('trashedticket:autodelete')->everyMinute();
        $schedule->command('disposable:update')->weekly();
        $schedule->command('customer:inactive_delete')->everyMinute();
        $schedule->command('cache:clear')->everyThirtyMinutes();
        $schedule->command('config:clear')->everyThirtyMinutes();
        $schedule->command('route:clear')->everyThirtyMinutes();
        $schedule->command('optimize:clear')->everyThirtyMinutes();
        $schedule->command('view:clear')->everyThirtyMinutes();
        // $schedule->command('Dataseed:updating')->everyMinute();

        $schedule->command('documents:send-reminders')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/document-reminders.log'));

        // Enviar recordatorios diariamente a las 10 AM
        $schedule->command('returns:send-reminders')
            ->dailyAt('10:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/return-reminders.log'));

        // Limpiar comunicaciones antiguas semanalmente
        $schedule->command('returns:cleanup-communications --days=90')
            ->weekly()
            ->sundays()
            ->at('02:00')
            ->withoutOverlapping();

        // Procesar notificaciones pendientes cada 5 minutos
        // NOTE: ProcessPendingNotifications class not found - commented out
        // $schedule->job(new ProcessPendingNotifications)
        //     ->everyFiveMinutes()
        //     ->withoutOverlapping();

        // Backup automático diariamente a las 3 AM
        $schedule->command('backup:run')
            ->dailyAt('03:00')
            ->withoutOverlapping(120)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/backup.log'));

        // Limpiar backups antiguos diariamente a las 4 AM
        $schedule->command('backup:clean')
            ->dailyAt('04:00')
            ->withoutOverlapping(60)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/backup-clean.log'));

        // Monitorear salud de backups diariamente a las 5 AM
        $schedule->command('backup:monitor')
            ->dailyAt('05:00')
            ->withoutOverlapping(60)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/backup-monitor.log'));

        // Ejecutar backups programados cada minuto
        $schedule->command('app:run-scheduled-backups')
            ->everyMinute()
            ->withoutOverlapping(2)
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/scheduled-backups.log'));

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
