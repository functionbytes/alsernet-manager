<?php

namespace App\Listeners;

use App\Events\Document\DocumentStatusChanged;
use App\Services\Documents\DocumentEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRejectionEmailListener implements ShouldQueue
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
        // Send rejection email when status changes to INCOMPLETE or REJECTED
        if (in_array($event->toStatus->key, ['incomplete', 'rejected'])) {
            $reason = $event->reason ?? 'Los documentos no cumplen con los requisitos especificados.';
            $this->emailService->sendRejectionEmail($event->document, $reason);
        }
    }
}
