<?php

namespace App\Listeners\Documents;

use App\Events\Documents\DocumentUploaded;
use App\Jobs\Documents\SendDocumentUploadedConfirmationJob;
use Illuminate\Support\Facades\Log;

class SendDocumentUploadConfirmation
{
    /**
     * Maneja el evento DocumentUploaded
     * Despacha un job en la cola para enviar email de confirmación de forma ASÍNCRONA
     * Evita bloquear la respuesta HTTP esperando a que se envíe el email
     */
    public function handle(DocumentUploaded $event): void
    {
        $document = $event->document->fresh();

        if (! $document) {
            return;
        }

        $recipient = $document->customer_email ?? $document->customer?->email;

        if (! $recipient) {
            Log::warning('Document upload confirmation skipped: missing customer email', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
            ]);

            return;
        }

        try {
            // Despachar job en la cola para envío asíncrono (NO bloquea la respuesta)
            SendDocumentUploadedConfirmationJob::dispatch($document);

            Log::info('Document upload confirmation job queued', [
                'document_uid' => $document->uid,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'sent_method' => 'async',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Unable to queue document upload confirmation', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'exception' => $exception->getMessage(),
            ]);

            // No relanzar excepción - el fallo al encolar no debe bloquear el flujo de carga
        }
    }
}
