<?php

namespace Modules\Document\Factories;

use Illuminate\Support\Facades\Mail;
use Modules\Document\Entities\Document;
use Modules\Document\Mail\DocumentCustomMail;
use Modules\Mailer\Models\MailerTemplate;

class DocumentEmailFactory
{
    public static function sendByTemplateKey(
        Document $document,
        string $templateKey,
        array $additionalVariables = []
    ): bool {
        $template = MailerTemplate::where('key', $templateKey)
            ->where('is_enabled', true)
            ->where('module', 'documents')
            ->first();

        if (! $template) {
            \Log::warning("DocumentEmailFactory: Template '$templateKey' no encontrado");

            return false;
        }

        return self::sendByTemplate($document, $template, $additionalVariables);
    }

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

    public static function sendUploadedNotification(Document $document): bool
    {
        return self::sendByTemplateKey($document, 'document_uploaded');
    }

    public static function sendReminder(Document $document): bool
    {
        return self::sendByTemplateKey($document, 'document_reminder');
    }

    public static function sendMissingNotification(Document $document, string $missingDocuments = ''): bool
    {
        return self::sendByTemplateKey($document, 'document_missing', [
            'MISSING_DOCUMENTS' => $missingDocuments ?: 'Documentos no especificados',
        ]);
    }

    public static function sendApprovedNotification(Document $document): bool
    {
        return self::sendByTemplateKey($document, 'document_approved');
    }

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

    public static function getTemplate(string $templateKey): ?MailTemplate
    {
        return MailTemplate::where('key', $templateKey)
            ->where('is_enabled', true)
            ->where('module', 'documents')
            ->first();
    }

    public static function getAvailableTemplates()
    {
        return MailTemplate::where('is_enabled', true)
            ->where('module', 'documents')
            ->orderBy('name')
            ->get();
    }

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
