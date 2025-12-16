<?php

namespace App\Listeners\Documents;

use App\Events\Documents\DocumentCreated;
use App\Jobs\Document\SendDocumentReminderJob;
use App\Models\Document\DocumentStatus;
use App\Services\Documents\DocumentMailService;
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

        // Set status to "pending" (Solicitado) - Initial Request email sent
        if (! $document->status_id) {
            $pendingStatus = DocumentStatus::where('key', 'pending')->first();
            if ($pendingStatus) {
                $document->update(['status_id' => $pendingStatus->id]);
                Log::info('Document status set to Pending', [
                    'document_uid' => $document->uid,
                    'status_id' => $pendingStatus->id,
                ]);
            }
        }

        $recipient = $document->customer_email ?? $document->customer?->email;

        if (! $recipient) {
            Log::warning('Document upload notification skipped: missing customer email', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
            ]);

            return;
        }

        try {
            // Enviar notificación inicial de forma SÍNCRONA (directa, sin cola)
            DocumentMailService::sendUploadNotification($document);

            // Programar recordatorio con delay según reminder_days (asíncrono en la cola)
            $reminderDays = (int) setting('documents.reminder_days', 7);
            dispatch(new SendDocumentReminderJob($document))
                ->delay(now()->addDays($reminderDays))
                ->onQueue('emails');

            Log::info('Document notification sent and reminder scheduled', [
                'document_uid' => $document->uid,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'notification_sent' => 'sync',
                'reminder_scheduled' => "async_+{$reminderDays}days",
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
