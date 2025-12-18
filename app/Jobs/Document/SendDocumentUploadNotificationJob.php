<?php

namespace App\Jobs\Documents;

use App\Jobs\Base;
use App\Mail\Documents\DocumentUploadNotificationMail;
use App\Models\Document\Document;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class SendDocumentUploadNotificationJob extends Base
{
    /**
     * Create a new job instance.
     */
    public function __construct(protected Document $document) {}

    /**
     * Execute the job.
     * Envía email inicial pidiendo al cliente que cargue la documentación
     */
    public function handle(): void
    {
        // Verificar si la notificación de solicitud inicial está habilitada
        if (Setting::get('documents.enable_initial_request', 'yes') !== 'yes') {
            \Log::info('Initial request notification disabled, skipping for document: '.$this->document->uid);

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
            Mail::send(new DocumentUploadNotificationMail($this->document));

            \Log::info('Document upload notification sent for document: '.$this->document->uid);
        } catch (\Exception $e) {
            \Log::error('Failed to send document upload notification: '.$e->getMessage());
            throw $e;
        }
    }
}
