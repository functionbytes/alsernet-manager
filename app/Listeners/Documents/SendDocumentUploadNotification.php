<?php

namespace App\Listeners\Documents;

use App\Events\Document\DocumentCreated;
use App\Jobs\Document\MailTemplateJob;
use Illuminate\Support\Facades\Log;

class SendDocumentUploadNotification
{
    /**
     * Maneja el evento DocumentCreated
     * 1. Set status to "Pending" (Solicitado)
     * 2. Envía email inicial pidiendo la carga de documentos (síncrono)
     * 3. Programa el recordatorio con delay según reminder_days (asíncrono)
     */
    public function handle(DocumentCreated $event): void
    {
        $document = $event->document->fresh();

        if (! $document) {
            return;
        }

        // Log entry to track how many times listener is triggered
        Log::info('SendDocumentUploadNotification listener triggered', [
            'document_uid' => $document->uid,
            'document_id' => $document->id,
            'order_id' => $document->order_id,
            'status_id' => $document->status_id,
        ]);

        $recipient = $document->customer_email ?? $document->customer?->email;

        if (! $recipient) {
            Log::warning('Document upload notification skipped: missing customer email', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
            ]);

            return;
        }

        try {
            // Despachar correo inicial de solicitud de documentación (request)
            // No enviar síncrono para evitar duplicados
            MailTemplateJob::dispatch($document, 'request')
                ->onQueue('emails');

            // Programar recordatorio con delay según reminder_days (asíncrono en la cola)
            $reminderDays = (int) setting('documents.reminder_days', 7);
            MailTemplateJob::dispatch($document, 'reminder')
                ->delay(now()->addDays($reminderDays))
                ->onQueue('emails');

            Log::info('Document notification and reminder scheduled', [
                'document_uid' => $document->uid,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'notification_scheduled' => 'request_email',
                'reminder_scheduled' => "async_+{$reminderDays}days",
            ]);
        } catch (\Throwable $exception) {
            Log::error('Unable to schedule document notifications', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
