<?php

namespace App\Jobs\Documents;

use App\Jobs\Base;
use App\Mail\Documents\DocumentMissingNotificationMail;
use App\Models\Document\Document;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class SendDocumentMissingDocsNotificationJob extends Base
{
    /**
     * Create a new job instance.
     */
    public function __construct(protected Document $document) {}

    /**
     * Execute the job.
     * Envía email pidiendo que se reenvíen documentos específicos que faltan
     */
    public function handle(): void
    {
        // Verificar si la notificación de documentos específicos está habilitada
        if (Setting::get('documents.enable_missing_docs', 'yes') !== 'yes') {
            \Log::info('Missing docs notification disabled, skipping for document: '.$this->document->uid);

            return;
        }

        // Obtener el email del cliente
        $email = $this->document->customer_email ??
                 $this->document->customer?->email;

        if (! $email) {
            \Log::warning('No email found for document: '.$this->document->uid);

            return;
        }

        try {
            Mail::send(new DocumentMissingNotificationMail($this->document));

            \Log::info('Missing docs notification sent for document: '.$this->document->uid);
        } catch (\Exception $e) {
            \Log::error('Failed to send missing docs notification: '.$e->getMessage());
            throw $e;
        }
    }
}
