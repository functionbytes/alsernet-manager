<?php

namespace App\Listeners;

use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Events\CleanupWasSuccessful;

class BackupEventListener
{
    /**
     * Handle backup successful event
     */
    public function onBackupWasSuccessful(BackupWasSuccessful $event)
    {
        \Log::info('Backup was successful', [
            'backup' => $event->backupDestination->backupName(),
        ]);
    }

    /**
     * Handle backup failed event
     */
    public function onBackupHasFailed(BackupHasFailed $event)
    {
        \Log::error('Backup has failed', [
            'exception' => $event->exception->getMessage(),
        ]);
    }

    /**
     * Handle healthy backup found event
     */
    public function onHealthyBackupWasFound(HealthyBackupWasFound $event)
    {
        \Log::info('Healthy backup was found');
    }

    /**
     * Handle unhealthy backup found event
     */
    public function onUnhealthyBackupWasFound(UnhealthyBackupWasFound $event)
    {
        \Log::warning('Unhealthy backup was found', [
            'issues' => $event->issues,
        ]);
    }

    /**
     * Handle cleanup successful event
     */
    public function onCleanupWasSuccessful(CleanupWasSuccessful $event)
    {
        \Log::info('Backup cleanup was successful');
    }

    /**
     * Handle cleanup failed event
     */
    public function onCleanupHasFailed(CleanupHasFailed $event)
    {
        \Log::error('Backup cleanup has failed', [
            'exception' => $event->exception->getMessage(),
        ]);
    }
}
