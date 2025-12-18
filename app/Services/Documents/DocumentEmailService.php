<?php

namespace App\Services\Documents;

use App\Jobs\Documents\MailTemplateJob;
use App\Models\Document\Document;

class DocumentEmailService
{
    /**
     * Send initial document request email
     */
    public function sendInitialRequest(Document $document): void
    {
        MailTemplateJob::dispatch($document, 'request');
    }

    /**
     * Send reminder email for pending documents
     */
    public function sendReminder(Document $document): void
    {
        MailTemplateJob::dispatch($document, 'reminder');
    }

    /**
     * Send request for missing documents
     */
    public function sendMissingDocumentsRequest(Document $document, array $missingDocs, string $reason = ''): void
    {
        MailTemplateJob::dispatch($document, 'missing', [
            'missing_docs' => $missingDocs,
            'notes' => $reason,
        ]);
    }

    /**
     * Send approval email
     */
    public function sendApprovalEmail(Document $document): void
    {
        MailTemplateJob::dispatch($document, 'approval');
    }

    /**
     * Send rejection email
     */
    public function sendRejectionEmail(Document $document, string $reason): void
    {
        MailTemplateJob::dispatch($document, 'rejection', [
            'reason' => $reason,
        ]);
    }

    /**
     * Send completion email
     */
    public function sendCompletionEmail(Document $document): void
    {
        // TODO: Add 'completion' support to MailTemplateJob
        // For now, using 'approval' as fallback
        MailTemplateJob::dispatch($document, 'approval');
    }

    /**
     * Send upload confirmation email (sent when client uploads documents)
     */
    public function sendUploadConfirmation(Document $document): void
    {
        MailTemplateJob::dispatch($document, 'upload');
    }

    /**
     * Process document upload: update status to "received" and send confirmation email
     * Replaces DocumentUploaded event logic
     */
    public function processDocumentUpload(Document $document): void
    {
        $document = $document->fresh();

        if (! $document) {
            return;
        }

        // Set status to "received" (Documentos Recibidos) - Upload Confirmation email will be sent
        // Only update if current status is "pending" or "awaiting_documents" (not if already reviewed)
        if ($document->status_id) {
            $currentStatus = $document->status()->first();
            $allowedPreviousStatuses = ['pending', 'awaiting_documents'];

            if ($currentStatus && in_array($currentStatus->key, $allowedPreviousStatuses)) {
                $receivedStatus = \App\Models\Document\DocumentStatus::where('key', 'received')->first();
                if ($receivedStatus) {
                    $document->update(['status_id' => $receivedStatus->id]);

                    \Illuminate\Support\Facades\Log::info('Document status set to Received', [
                        'document_uid' => $document->uid,
                        'previous_status' => $currentStatus->key,
                        'status_id' => $receivedStatus->id,
                    ]);

                    // Fire DocumentStatusChanged event
                    \App\Events\Document\DocumentStatusChanged::dispatch(
                        $document,
                        $currentStatus,
                        $receivedStatus,
                        'Automatic status change: documents uploaded'
                    );
                }
            }
        }

        $recipient = $document->customer_email ?? $document->customer?->email;

        if (! $recipient) {
            \Illuminate\Support\Facades\Log::warning('Document upload confirmation skipped: missing customer email', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
            ]);

            return;
        }

        try {
            // Despachar MailTemplateJob directamente para envío asíncrono
            $this->sendUploadConfirmation($document);

            \Illuminate\Support\Facades\Log::info('Document upload confirmation job queued', [
                'document_uid' => $document->uid,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'sent_method' => 'async',
            ]);
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Log::error('Unable to queue document upload confirmation', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
                'recipient' => $recipient,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
