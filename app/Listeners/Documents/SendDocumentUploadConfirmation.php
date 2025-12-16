<?php

namespace App\Listeners\Documents;

use App\Events\Document\DocumentStatusChanged;
use App\Events\Documents\DocumentUploaded;
use App\Jobs\Documents\SendDocumentUploadedConfirmationJob;
use App\Models\Document\DocumentStatus;
use App\Traits\PreventsDuplicateEventExecution;
use Illuminate\Support\Facades\Log;

class SendDocumentUploadConfirmation
{
    use PreventsDuplicateEventExecution;

    /**
     * Maneja el evento DocumentUploaded
     * 1. Set status to "Received" (Documentos Recibidos)
     * 2. Despacha un job en la cola para enviar email de confirmación de forma ASÍNCRONA
     * Evita bloquear la respuesta HTTP esperando a que se envíe el email
     */
    public function handle(DocumentUploaded $event): void
    {
        // Prevenir ejecución múltiple en el mismo request
        if ($this->preventDuplicateExecution($event)) {
            return;
        }

        $document = $event->document->fresh();

        if (! $document) {
            return;
        }

        // Set status to "received" (Documentos Recibidos) - Upload Confirmation email will be sent
        // Only update if current status is "pending" or "awaiting_documents" (not if already reviewed)
        if ($document->status_id) {
            $currentStatus = $document->status()->first();
            $allowedPreviousStatuses = ['pending', 'awaiting_documents'];

            if ($currentStatus && in_array($currentStatus->key, $allowedPreviousStatuses)) {
                $receivedStatus = DocumentStatus::where('key', 'received')->first();
                if ($receivedStatus) {
                    $document->update(['status_id' => $receivedStatus->id]);
                    Log::info('Document status set to Received', [
                        'document_uid' => $document->uid,
                        'previous_status' => $currentStatus->key,
                        'status_id' => $receivedStatus->id,
                    ]);

                    // Fire DocumentStatusChanged event
                    event(new \App\Events\Document\DocumentStatusChanged(
                        $document,
                        $currentStatus,
                        $receivedStatus,
                        'Automatic status change: documents uploaded'
                    ));
                }
            }
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
