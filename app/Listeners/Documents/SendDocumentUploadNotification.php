<?php

namespace App\Listeners\Documents;

use App\Events\Documents\DocumentCreated;
use App\Services\Documents\DocumentMailService;
use App\Jobs\Documents\SendDocumentReminderJob;
use Illuminate\Support\Facades\Log;

class SendDocumentUploadNotification
{

    /**
     * Maneja el evento DocumentCreated
     * Envía email inicial pidiendo la carga de documentos (síncrono)
     * Y programa el recordatorio para 1 día después (asíncrono)
     */
    public function handle(DocumentCreated $event): void
    {
        $document = $event->document->fresh();

        if (!$document) {
            return;
        }

        $recipient = $document->customer_email ?? $document->customer?->email;

        if (!$recipient) {
            Log::warning('Document upload notification skipped: missing customer email', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
            ]);
            return;
        }

        try {
            // Enviar notificación inicial de forma SÍNCRONA (directa, sin cola)
            DocumentMailService::sendUploadNotification($document);

            // Programar recordatorio para 1 día después (asíncrono en la cola)
            dispatch(new SendDocumentReminderJob($document))
                ->delay(now()->addDay())
                ->onQueue('emails');

            Log::info('Document notification sent and reminder scheduled', [
                'document_uid' => $document->uid,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'notification_sent' => 'sync',
                'reminder_scheduled' => 'async_+1day',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Unable to send document notifications', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
