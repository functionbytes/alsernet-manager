<?php

namespace App\Listeners\Documents;

use App\Events\Document\DocumentReminderRequested;
use App\Services\Documents\DocumentMailService;
use Illuminate\Support\Facades\Log;

class SendDocumentUploadReminder
{

    /**
     * Maneja el evento DocumentReminderRequested
     * Envía recordatorio de forma SÍNCRONA
     */
    public function handle(DocumentReminderRequested $event): void
    {
        $document = $event->document->fresh();

        if (!$document) {
            return;
        }

        $recipient = $document->customer_email ?? $document->customer?->email;

        if (!$recipient) {
            Log::warning('Document upload reminder skipped: missing customer email', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
            ]);
            return;
        }

        try {
            // Enviar recordatorio de forma SÍNCRONA (directa, sin cola)
            DocumentMailService::sendReminder($document);

            Log::info('Document upload reminder sent successfully', [
                'document_uid' => $document->uid,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'sent_method' => 'sync',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Unable to send document upload reminder', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
