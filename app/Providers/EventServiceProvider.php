<?php

namespace App\Providers;

use App\Events\Campaigns\GiftvoucherCreated;
use App\Events\Document\DocumentCreated;
use App\Events\Document\DocumentStatusChanged;
use App\Events\Documents\DocumentCreated as DocumentsDocumentCreated;
use App\Events\Documents\DocumentReminderRequested;
use App\Events\Documents\DocumentUploaded;
use App\Events\Subscribers\SubscriberCheckatEvent;
use App\Listeners\Backups\BackupEventListener;
use App\Listeners\Campaigns\GiftvoucherListener;
use App\Listeners\Documents\SendDocumentUploadConfirmation;
use App\Listeners\Documents\SendDocumentUploadNotification;
use App\Listeners\Documents\SendDocumentUploadReminder;
use App\Listeners\LogToDatabase;
use App\Listeners\SendApprovalEmailListener;
use App\Listeners\SendCompletionEmailListener;
use App\Listeners\SendInitialRequestEmailListener;
use App\Listeners\SendNewUserNotification;
use App\Listeners\SendRejectionEmailListener;
use App\Listeners\Subscribers\SubscriberCheckatListener;
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

        // Eventos de Documentos (antiguos)
        DocumentsDocumentCreated::class => [
            SendDocumentUploadNotification::class,
        ],

        DocumentUploaded::class => [
            SendDocumentUploadConfirmation::class,
        ],

        DocumentReminderRequested::class => [
            SendDocumentUploadReminder::class,
        ],

        // Eventos de Documentos (nuevos - con emails integrados)
        DocumentCreated::class => [
            SendInitialRequestEmailListener::class,
        ],

        DocumentStatusChanged::class => [
            SendApprovalEmailListener::class,
            SendRejectionEmailListener::class,
            SendCompletionEmailListener::class,
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
        return true;
    }
}
