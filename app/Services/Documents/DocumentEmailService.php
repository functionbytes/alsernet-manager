<?php

namespace App\Services\Documents;

use App\Jobs\Document\MailTemplateJob;
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
}
