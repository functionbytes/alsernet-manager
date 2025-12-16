<?php

namespace App\Listeners;

use App\Events\Document\DocumentCreated;
use App\Services\Documents\DocumentEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendInitialRequestEmailListener implements ShouldQueue
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
    public function handle(DocumentCreated $event): void
    {
        // Send initial request email when document is created
        $this->emailService->sendInitialRequest($event->document);
    }
}
