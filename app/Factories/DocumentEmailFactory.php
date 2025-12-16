<?php

namespace App\Factories;

use App\Mail\Documents\DocumentCustomMail;
use App\Models\Document\Document;
use App\Models\Mail\MailTemplate;
use Illuminate\Support\Facades\Mail;

/**
 * DocumentEmailFactory
 *
 * Factory para crear y enviar emails de documentos usando plantillas
 * Simplifica el proceso de enviar emails pre-diseñados con variables automáticas
 */
class DocumentEmailFactory
{
    /**
     * Enviar email usando plantilla de documento
     *
     * @param  string  $templateKey  - Clave del template: 'document_uploaded', 'document_reminder', etc.
     * @param  array  $additionalVariables  - Variables adicionales
     */
    public static function sendByTemplateKey(
        Document $document,
        string $templateKey,
        array $additionalVariables = []
    ): bool {
        $template = MailTemplate::where('key', $templateKey)
            ->where('is_enabled', true)
            ->where('module', 'documents')
            ->first();

        if (! $template) {
            \Log::warning("DocumentEmailFactory: Template '$templateKey' no encontrado");

            return false;
        }

        return self::sendByTemplate($document, $template, $additionalVariables);
    }

    /**
     * Enviar email usando plantilla específica
     *
     * @param  array  $additionalVariables  - Variables adicionales
     */
    public static function sendByTemplate(
        Document $document,
        MailTemplate $template,
        array $additionalVariables = []
    ): bool {
        try {
            $recipient = $document->customer_email ?? $document->customer?->email;

            if (! $recipient) {
                \Log::warning("DocumentEmailFactory: No hay email de destinatario para documento {$document->uid}");

                return false;
            }

            // Crear Mailable
            $mail = new DocumentCustomMail($document, null, null, $template);

            // Agregar variables adicionales
            if (! empty($additionalVariables)) {
                $mail->setVariables($additionalVariables);
            }

            // Enviar
            Mail::to($recipient)->send($mail);

            \Log::info("DocumentEmailFactory: Email enviado exitosamente. Template: {$template->key}, Recipient: {$recipient}");

            return true;
        } catch (\Exception $e) {
            \Log::error("DocumentEmailFactory: Error al enviar email. {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Enviar email personalizado (legacy)
     */
    public static function sendCustom(Document $document, string $subject, string $content): bool
    {
        try {
            $recipient = $document->customer_email ?? $document->customer?->email;

            if (! $recipient) {
                \Log::warning("DocumentEmailFactory: No hay email de destinatario para documento {$document->uid}");

                return false;
            }

            Mail::to($recipient)->send(new DocumentCustomMail($document, $subject, $content));

            return true;
        } catch (\Exception $e) {
            \Log::error("DocumentEmailFactory: Error al enviar email personalizado. {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Enviar email de confirmación (documento subido)
     */
    public static function sendUploadedNotification(Document $document): bool
    {
        return self::sendByTemplateKey($document, 'document_uploaded');
    }

    /**
     * Enviar recordatorio de documentos
     */
    public static function sendReminder(Document $document): bool
    {
        return self::sendByTemplateKey($document, 'document_reminder');
    }

    /**
     * Enviar notificación de documentos faltantes
     *
     * @param  string  $missingDocuments  - Lista de documentos faltantes
     */
    public static function sendMissingNotification(Document $document, string $missingDocuments = ''): bool
    {
        return self::sendByTemplateKey($document, 'document_missing', [
            'MISSING_DOCUMENTS' => $missingDocuments ?: 'Documentos no especificados',
        ]);
    }

    /**
     * Enviar confirmación de aprobación
     */
    public static function sendApprovedNotification(Document $document): bool
    {
        return self::sendByTemplateKey($document, 'document_approved');
    }

    /**
     * Enviar email de prueba de una plantilla
     */
    public static function sendTestEmail(MailTemplate $template, string $testEmail): bool
    {
        try {
            $mail = new DocumentCustomMail(
                new Document([
                    'customer_firstname' => 'Juan',
                    'customer_lastname' => 'Pérez',
                    'customer_email' => $testEmail,
                    'order_id' => '12345',
                    'document_type' => 'Cédula',
                ]),
                null,
                null,
                $template
            );

            Mail::to($testEmail)->send($mail);

            return true;
        } catch (\Exception $e) {
            \Log::error("DocumentEmailFactory: Error al enviar email de prueba. {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Obtener plantilla de documento disponible
     */
    public static function getTemplate(string $templateKey): ?MailTemplate
    {
        return MailTemplate::where('key', $templateKey)
            ->where('is_enabled', true)
            ->where('module', 'documents')
            ->first();
    }

    /**
     * Obtener todas las plantillas de documentos disponibles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAvailableTemplates()
    {
        return MailTemplate::where('is_enabled', true)
            ->where('module', 'documents')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtener plantillas de documentos con estadísticas
     */
    public static function getTemplatesWithStats(): array
    {
        return self::getAvailableTemplates()
            ->map(function (MailTemplate $template) {
                return [
                    'key' => $template->key,
                    'name' => $template->name,
                    'subject' => $template->subject,
                    'module' => $template->module,
                    'is_complete' => $template->isComplete(),
                ];
            })
            ->toArray();
    }
}
