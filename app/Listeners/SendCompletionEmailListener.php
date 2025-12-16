<?php

namespace App\Listeners;

use App\Events\Document\DocumentStatusChanged;
use App\Services\Documents\DocumentEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCompletionEmailListener implements ShouldQueue
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
        // Send completion email when status changes to COMPLETED
        if ($event->toStatus->key === 'completed') {
            $this->emailService->sendCompletionEmail($event->document);
        }
    }
}
