<?php

namespace Modules\Documents\Providers;

use App\Listeners\Campaigns\SendNewUserNotification;
use App\Listeners\Documents\LogDocumentStatusChange;
use App\Listeners\Documents\SendDocumentUploadNotification;
use App\Listeners\Documents\SendStageNotifications;
use App\Listeners\Systems\Backups\BackupEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Documents\Events\DocumentCreated;
use Modules\Documents\Events\DocumentStatusChanged;
use Modules\Documents\Events\DocumentValidationStageApproved;
use Spatie\Backup\Events\BackupHasSucceeded;
use Spatie\Backup\Events\BackupWasNotSuccessful;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [


        // Eventos de Documentos
        DocumentCreated::class => [
            SendDocumentUploadNotification::class,
        ],

        DocumentStatusChanged::class => [
            LogDocumentStatusChange::class,
        ],

        DocumentValidationStageApproved::class => [
            SendStageNotifications::class,
        ],

        //

    ];

    public function boot(): void
    {
        parent::boot();


    }

    /**
     * Register handlers for backup events to suppress notification errors
     */
    private function registerBackupEventHandlers(): void
    {

    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
