<?php

namespace App\Jobs\Documents;

use App\Jobs\Base;
use App\Mail\Documents\DocumentReminderMail;
use App\Models\Document\Document;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class SendDocumentReminderJob extends Base
{
    /**
     * Create a new job instance.
     */
    public function __construct(protected Document $document) {}

    /**
     * Execute the job.
     * Envía email de recordatorio después de 1 día si no se ha cargado la documentación
     */
    public function handle(): void
    {
        // Verificar si el recordatorio está habilitado
        if (Setting::get('documents.enable_reminder', 'yes') !== 'yes') {
            \Log::info('Reminder notification disabled, skipping for document: '.$this->document->uid);

            return;
        }

        // Recargar el documento para verificar si ya fue cargado
        $document = Document::find($this->document->id);

        if (! $document) {
            \Log::warning('Document not found: '.$this->document->uid);

            return;
        }

        // Si no hay documentos faltantes, el documento está completo - no enviar recordatorio
        if (empty($document->getMissingDocuments())) {
            \Log::info('Document is complete, skipping reminder: '.$document->uid);

            return;
        }

        $email = $document->customer_email ?? $document->customer?->email;

        if (! $email) {
            \Log::warning('No email found for document: '.$document->uid);

            return;
        }

        try {
            Mail::send(new DocumentReminderMail($document));

            // Actualizar el campo reminder_at para rastrear cuando se envió el recordatorio
            $document->update(['reminder_at' => now()]);

            \Log::info('Document reminder sent for document: '.$document->uid);
        } catch (\Exception $e) {
            \Log::error('Failed to send document reminder: '.$e->getMessage());
            throw $e;
        }
    }
}
