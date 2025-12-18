<?php

namespace App\Providers;

use App\Events\Campaigns\GiftvoucherCreated;
use App\Events\Document\DocumentCreated;
use App\Events\Document\DocumentStatusChanged;
use App\Listeners\Campaigns\GiftvoucherListener;
use App\Listeners\Campaigns\SendNewUserNotification;
use App\Listeners\Documents\LogDocumentStatusChange;
use App\Listeners\Documents\SendDocumentUploadNotification;
use App\Listeners\Systems\Backups\BackupEventListener;
use App\Listeners\Systems\LogToDatabase;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Log\Events\MessageLogged;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupHasSucceeded;
use Spatie\Backup\Events\BackupWasNotSuccessful;
use Spatie\Backup\Events\BackupWasSuccessful;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        Registered::class => [
            SendEmailVerificationNotification::class,
            SendNewUserNotification::class,
        ],

        GiftvoucherCreated::class => [
            GiftvoucherListener::class,
        ],

        // Eventos de Documentos
        DocumentCreated::class => [
            SendDocumentUploadNotification::class,
        ],

        DocumentStatusChanged::class => [
            LogDocumentStatusChange::class,
        ],

        // Eventos de Backup
        BackupHasSucceeded::class => [
            BackupEventListener::class.'@handleBackupHasSucceeded',
        ],

        BackupHasFailed::class => [
            BackupEventListener::class.'@handleBackupHasFailed',
        ],

        BackupWasSuccessful::class => [
            BackupEventListener::class.'@handleBackupWasSuccessful',
        ],

        BackupWasNotSuccessful::class => [
            BackupEventListener::class.'@handleBackupWasNotSuccessful',
        ],

        // SubscriberCheckatEvent::class => [
        // SubscriberCheckatListener::class,
        // ],

    ];

    public function boot(): void
    {
        parent::boot();

        // Listen to log messages and save important ones to database
        \Event::listen(MessageLogged::class, LogToDatabase::class);

        // Register backup event handlers to prevent notification errors
        $this->registerBackupEventHandlers();

        // Note: EventServiceProvider may be booted multiple times by Laravel's container,
        // but the PreventsDuplicateEventExecution trait in listeners prevents actual duplicate execution
    }

    /**
     * Register handlers for backup events to suppress notification errors
     */
    private function registerBackupEventHandlers(): void
    {
        // Catch backup events and log them instead of trying to send notifications
        \Event::listen(BackupHasFailed::class, function ($event) {
            \Log::error('Backup failed: '.($event->exception?->getMessage() ?? 'Unknown error'));
        });

        \Event::listen(BackupHasSucceeded::class, function ($event) {
            \Log::info('Backup succeeded');
        });

        \Event::listen(BackupWasSuccessful::class, function ($event) {
            \Log::info('Backup process was successful');
        });

        \Event::listen(BackupWasNotSuccessful::class, function ($event) {
            \Log::error('Backup process was not successful: '.($event->exception?->getMessage() ?? 'Unknown error'));
        });
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
