<?php

namespace App\Listeners;

use App\Events\Document\DocumentStatusChanged;
use App\Services\Documents\DocumentEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApprovalEmailListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private DocumentEmailService $emailService,
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DocumentStatusChanged $event): void
    {
        // Send approval email when status changes to APPROVED
        if ($event->toStatus->key === 'approved') {
            $this->emailService->sendApprovalEmail($event->document);
        }
    }
}
