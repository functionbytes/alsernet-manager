<?php

namespace Modules\Document\Services;

use Illuminate\Support\Facades\Log;
use Modules\Document\Entities\Document;
use Modules\Document\Entities\DocumentStatus;
use Modules\Document\Events\DocumentStatusChanged;
use Modules\Document\Jobs\MailTemplateJob;

class DocumentEmailService
{
    public function sendInitialRequest(Document $document): void
    {
        MailTemplateJob::dispatch($document, 'request');
    }

    public function sendReminder(Document $document): void
    {
        MailTemplateJob::dispatch($document, 'reminder');
    }

    public function sendMissingDocumentsRequest(Document $document, array $missingDocs, string $reason = ''): void
    {
        MailTemplateJob::dispatch($document, 'missing', [
            'missing_docs' => $missingDocs,
            'notes' => $reason,
        ]);
    }

    public function sendApprovalEmail(Document $document): void
    {
        MailTemplateJob::dispatch($document, 'approval');
    }

    public function sendRejectionEmail(Document $document, string $reason): void
    {
        MailTemplateJob::dispatch($document, 'rejection', [
            'reason' => $reason,
        ]);
    }

    public function sendCompletionEmail(Document $document): void
    {
        MailTemplateJob::dispatch($document, 'approval');
    }

    public function sendUploadConfirmation(Document $document): void
    {
        MailTemplateJob::dispatch($document, 'upload');
    }

    public function processDocumentUpload(Document $document): void
    {
        $document = $document->fresh();

        if (! $document) {
            return;
        }

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

                    DocumentStatusChanged::dispatch(
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
            Log::warning('Document upload confirmation skipped: missing customer email', [
                'document_uid' => $document->uid ?? null,
                'order_id' => $document->order_id,
            ]);

            return;
        }

        try {
            // Despachar MailTemplateJob directamente para envío asíncrono
            $this->sendUploadConfirmation($document);

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
        }
    }
}
