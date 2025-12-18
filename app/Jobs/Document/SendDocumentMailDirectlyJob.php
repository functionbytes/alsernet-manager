<?php

namespace App\Jobs\Documents;

use App\Jobs\Base;
use App\Models\Document\Document;
use App\Services\Documents\DocumentMailService;

class SendDocumentMailDirectlyJob extends Base
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Document $document,
        protected string $type = 'notification'
    ) {
    }

    /**
     * Execute the job.
     * Envía email de forma síncrona sin pasar por la cola
     * Tipos soportados: 'notification', 'reminder', 'confirmation'
     */
    public function handle(): void
    {
        try {
            $sent = match ($this->type) {
                'notification' => DocumentMailService::sendUploadNotification($this->document),
                'reminder' => DocumentMailService::sendReminder($this->document),
                'confirmation' => DocumentMailService::sendUploadedConfirmation($this->document),
                default => false,
            };

            if (!$sent) {
                \Log::warning('Document email not sent', [
                    'document_uid' => $this->document->uid,
                    'type' => $this->type,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send document email via direct job', [
                'document_uid' => $this->document->uid,
                'type' => $this->type,
                'exception' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
