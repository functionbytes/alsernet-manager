<?php

namespace App\Jobs\Documents;

use App\Jobs\Base;
use App\Models\Document\Document;
use App\Services\Documents\DocumentEmailService;

class SendDocumentUploadedConfirmationJob extends Base
{
    /**
     * Create a new job instance.
     */
    public function __construct(protected Document $document) {}

    /**
     * Execute the job.
     * Envía email de confirmación cuando el cliente carga exitosamente la documentación
     * Usa plantilla configurable de BD
     */
    public function handle(): void
    {
        $email = $this->document->customer_email ??
                 $this->document->customer?->email;

        if (! $email) {
            \Log::warning('No email found for document: '.$this->document->uid);

            return;
        }

        try {
            $emailService = new DocumentEmailService;
            $emailService->sendUploadConfirmation($this->document);

            \Log::info('Document uploaded confirmation sent for document: '.$this->document->uid);
        } catch (\Exception $e) {
            \Log::error('Failed to send document uploaded confirmation: '.$e->getMessage());
            throw $e;
        }
    }
}
