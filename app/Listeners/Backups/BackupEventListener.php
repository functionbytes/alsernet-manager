<?php

namespace App\Listeners\Backups;

use Spatie\Backup\Events\BackupHasSucceeded;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupWasSuccessful;
use Spatie\Backup\Events\BackupWasNotSuccessful;

/**
 * Silent listener for backup events.
 *
 * Handles all backup events from Spatie Laravel Backup package
 * without throwing errors or sending notifications.
 * This prevents "There is no notification class that can handle event" errors.
 */
class BackupEventListener
{
    /**
     * Handle backup successful event
     */
    public function handleBackupHasSucceeded(BackupHasSucceeded $event): void
    {
        // Log backup success
        \Log::info('Backup created successfully: ' . $event->backupDestination->disk() . ' disk');
    }

    /**
     * Handle backup failed event
     */
    public function handleBackupHasFailed(BackupHasFailed $event): void
    {
        // Log backup failure
        \Log::error('Backup failed: ' . $event->exception->getMessage());
    }

    /**
     * Handle backup was successful event
     */
    public function handleBackupWasSuccessful(BackupWasSuccessful $event): void
    {
        // Log backup completion
        \Log::info('Backup process completed successfully');
    }

    /**
     * Handle backup was not successful event
     */
    public function handleBackupWasNotSuccessful(BackupWasNotSuccessful $event): void
    {
        // Log backup completion error
        \Log::error('Backup process had errors: ' . $event->exception->getMessage());
    }
}
