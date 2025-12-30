<?php

namespace Modules\Document\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Document\Events\DocumentCreated;
use Modules\Document\Events\DocumentStatusChanged;
use Modules\Document\Events\DocumentValidationStageApproved;
use Modules\Document\Listeners\LogDocumentStatusChange;
use Modules\Document\Listeners\SendDocumentUploadNotification;
use Modules\Document\Listeners\SendStageNotifications;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        DocumentCreated::class => [
            SendDocumentUploadNotification::class,
        ],

        DocumentStatusChanged::class => [
            LogDocumentStatusChange::class,
        ],

        DocumentValidationStageApproved::class => [
            SendStageNotifications::class,
        ],

    ];

    public function boot(): void
    {
        parent::boot();

    }

    private function registerBackupEventHandlers(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
