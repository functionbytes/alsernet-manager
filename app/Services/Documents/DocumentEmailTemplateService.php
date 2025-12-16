<?php

namespace App\Services\Documents;

use App\Models\Document\Document;
use App\Models\Mail\MailTemplate;
use App\Models\Setting;
use App\Services\Email\TemplateRendererService;
use Illuminate\Support\Facades\Mail;

class DocumentEmailTemplateService
{
    /**
     * Enviar email de solicitud inicial usando plantilla de BD
     */
    public static function sendInitialRequest(Document $document): bool
    {
        try {

            $template = self::resolveTemplate('documents.email_template_initial_request_id', 'document_initial_request', ['document_request']);

            if (! $template) {
                return false;
            }

            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return false;
            }

            $variables = self::prepareDocumentVariables($document);

            // Get lang_id from document (defaults to 1 if not set)
            $langId = $document->lang_id ?? 1;

            // Get translation for the template using document's language
            $translation = $template->translate($langId);
            if (! $translation || ! $translation->subject) {
                return false;
            }

            $subject = TemplateRendererService::replaceVariables($translation->subject, $variables);
            $content = TemplateRendererService::renderEmailTemplate($template, $variables, $langId);

            Mail::html($content, function ($message) use ($subject, $recipient) {
                $message->to($recipient)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending initial request email', [
                'document_uid' => $document->uid,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enviar email de recordatorio usando plantilla de BD
     */
    public static function sendReminder(Document $document): bool
    {
        try {

            $template = self::resolveTemplate('documents.email_template_reminder_id', 'document_reminder');

            if (! $template) {
                return false;
            }

            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return false;
            }

            $variables = self::prepareDocumentVariables($document);

            // Calcular días desde la solicitud inicial
            $daysSinceRequest = $document->created_at
                ? now()->diffInDays($document->created_at)
                : 0;

            // Agregar variables específicas de recordatorio
            $variables['DAYS_SINCE_REQUEST'] = $daysSinceRequest;

            // Crear mensaje de recordatorio (solo texto, sin contenedor)
            // La plantilla ya tiene su propio contenedor con estilos
            $variables['REMINDER_MESSAGE'] = sprintf(
                'Han pasado <strong>%d día%s</strong> desde que solicitamos su documentación y aún no hemos recibido respuesta. Le recordamos que es importante que nos envíe los documentos lo antes posible para poder continuar con el procesamiento de su pedido.',
                $daysSinceRequest,
                $daysSinceRequest === 1 ? '' : 's'
            );

            // Get lang_id from document (defaults to 1 if not set)
            $langId = $document->lang_id ?? 1;

            // Get translation for the template using document's language
            $translation = $template->translate($langId);
            if (! $translation || ! $translation->subject) {
                return false;
            }

            $subject = TemplateRendererService::replaceVariables($translation->subject, $variables);
            $content = TemplateRendererService::renderEmailTemplate($template, $variables, $langId);

            Mail::html($content, function ($message) use ($subject, $recipient) {
                $message->to($recipient)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending reminder email', [
                'document_uid' => $document->uid,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enviar email de documentos faltantes usando plantilla de BD
     */
    public static function sendMissingDocuments(Document $document, array $missingDocs = [], ?string $notes = null): bool
    {
        try {

            $template = self::resolveTemplate('documents.email_template_missing_docs_id', 'document_missing_documents', ['document_missing']);

            if (! $template) {
                return false;
            }

            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return false;
            }

            $variables = self::prepareDocumentVariables($document, $missingDocs, $notes);

            // Get lang_id from document (defaults to 1 if not set)
            $langId = $document->lang_id ?? 1;

            // Get translation for the template using document's language
            $translation = $template->translate($langId);
            if (! $translation || ! $translation->subject) {
                return false;
            }

            $subject = TemplateRendererService::replaceVariables($translation->subject, $variables);
            $content = TemplateRendererService::renderEmailTemplate($template, $variables, $langId);

            Mail::html($content, function ($message) use ($subject, $recipient) {
                $message->to($recipient)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending missing documents email', [
                'document_uid' => $document->uid,
                'recipient' => $recipient ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    public static function sendCustomEmail(Document $document, string $subject, string $content): bool
    {
        try {
            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                \Log::error('Custom email: No recipient found', ['document_uid' => $document->uid]);

                return false;
            }

            $variables = self::prepareDocumentVariables($document);
            $langId = $document->lang_id ?? 1;

            // Procesar contenido del usuario (reemplazar variables que el usuario haya puesto)
            $userContent = TemplateRendererService::replaceVariables($content, $variables);

            // Agregar el contenido del usuario como variable especial
            $variables['custom_content'] = $userContent;
            $variables['CUSTOM_CONTENT'] = $userContent;

            // Procesar asunto (reemplazar variables del usuario)
            $processedSubject = TemplateRendererService::replaceVariables($subject, $variables);

            // Obtener plantilla configurada para correo personalizado
            $template = self::resolveTemplate('documents.mail_template_custom_email_id', 'document_custom_email');

            if (! $template) {
                \Log::warning('No custom email template configured, sending plain content');
                $finalContent = $userContent;
            } else {
                // Obtener traducción de la plantilla
                $translation = $template->translate($langId);
                if (! $translation || ! $translation->subject) {
                    \Log::error('Custom email template has no translation', [
                        'template_id' => $template->id,
                        'lang_id' => $langId,
                    ]);

                    return false;
                }

                // Usar renderEmailTemplate para aplicar layouts y reemplazar todas las variables
                // Esto es igual que los demás métodos (sendInitialRequest, sendReminder, etc.)
                $finalContent = TemplateRendererService::renderEmailTemplate($template, $variables, $langId);
            }

            Mail::html($finalContent, function ($message) use ($processedSubject, $recipient) {
                $message->to($recipient)
                    ->subject($processedSubject);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending custom email', [
                'document_uid' => $document->uid,
                'recipient' => $recipient ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Enviar email de confirmación de documentos cargados
     */
    public static function sendUploadConfirmation(Document $document): bool
    {
        try {
            $template = self::resolveTemplate('documents.email_template_upload_confirmation_id', 'document_upload_confirmation', ['document_confirmation']);

            if (! $template) {
                return false;
            }

            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return false;
            }

            $variables = self::prepareDocumentVariables($document);

            // Get lang_id from document (defaults to 1 if not set)
            $langId = $document->lang_id ?? 1;

            // Get translation for the template using document's language
            $translation = $template->translate($langId);
            if (! $translation || ! $translation->subject) {
                return false;
            }

            $subject = TemplateRendererService::replaceVariables($translation->subject, $variables);
            $content = TemplateRendererService::renderEmailTemplate($template, $variables, $langId);

            Mail::html($content, function ($message) use ($subject, $recipient) {
                $message->to($recipient)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending upload confirmation email', [
                'document_uid' => $document->uid,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enviar email de aprobación
     */
    public static function sendApprovalEmail(Document $document, ?string $notes = null): bool
    {
        try {
            $template = self::resolveTemplate('documents.mail_template_approval_id', 'document_approval', ['approval_notification']);

            if (! $template) {
                return false;
            }

            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return false;
            }

            $variables = self::prepareDocumentVariables($document, [], $notes);

            // Get lang_id from document (defaults to 1 if not set)
            $langId = $document->lang_id ?? 1;

            // Get translation for the template using document's language
            $translation = $template->translate($langId);
            if (! $translation || ! $translation->subject) {
                return false;
            }

            $subject = TemplateRendererService::replaceVariables($translation->subject, $variables);
            $content = TemplateRendererService::renderEmailTemplate($template, $variables, $langId);

            Mail::html($content, function ($message) use ($subject, $recipient) {
                $message->to($recipient)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending approval email', [
                'document_uid' => $document->uid,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enviar email de rechazo con razón personalizada
     */
    public static function sendRejectionEmail(Document $document, ?string $reason = null): bool
    {
        try {
            $template = self::resolveTemplate('documents.mail_template_rejection_id', 'document_rejection', ['rejection_notification']);

            if (! $template) {
                return false;
            }

            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return false;
            }

            $variables = self::prepareDocumentVariables($document, [], $reason);
            // Agregar variable específica para rechazo
            $variables['REJECTION_REASON'] = $reason ?? '';

            // Get lang_id from document (defaults to 1 if not set)
            $langId = $document->lang_id ?? 1;

            // Get translation for the template using document's language
            $translation = $template->translate($langId);
            if (! $translation || ! $translation->subject) {
                return false;
            }

            $subject = TemplateRendererService::replaceVariables($translation->subject, $variables);
            $content = TemplateRendererService::renderEmailTemplate($template, $variables, $langId);

            Mail::html($content, function ($message) use ($subject, $recipient) {
                $message->to($recipient)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending rejection email', [
                'document_uid' => $document->uid,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enviar email de finalización
     */
    public static function sendCompletionEmail(Document $document, ?string $notes = null): bool
    {
        try {
            $template = self::resolveTemplate('documents.mail_template_completion_id', 'document_completion', ['completion_notification']);

            if (! $template) {
                return false;
            }

            $recipient = $document->customer_email ?? $document->customer?->email;
            if (! $recipient) {
                return false;
            }

            $variables = self::prepareDocumentVariables($document, [], $notes);

            // Get lang_id from document (defaults to 1 if not set)
            $langId = $document->lang_id ?? 1;

            // Get translation for the template using document's language
            $translation = $template->translate($langId);
            if (! $translation || ! $translation->subject) {
                return false;
            }

            $subject = TemplateRendererService::replaceVariables($translation->subject, $variables);
            $content = TemplateRendererService::renderEmailTemplate($template, $variables, $langId);

            Mail::html($content, function ($message) use ($subject, $recipient) {
                $message->to($recipient)
                    ->subject($subject);
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending completion email', [
                'document_uid' => $document->uid,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Preparar variables para el documento
     */
    private static function prepareDocumentVariables(
        Document $document,
        array $missingDocs = [],
        ?string $notes = null
    ): array {
        // Obtener el código de idioma del documento
        $locale = $document->lang?->code ?? 'es';

        // Preparar nombre del cliente con fallback traducido
        $customerName = trim(sprintf(
            '%s %s',
            $document->customer_firstname ?? '',
            $document->customer_lastname ?? ''
        ));

        if (empty($customerName)) {
            $customerName = __('documents.labels.customer', [], $locale);
        }

        // Calcular fecha de vencimiento
        $uploadDeadline = $document->created_at
            ? $document->created_at->addDays(3)->format('d/m/Y')
            : null;

        // Generar URL de carga
        $uploadPortalTemplate = config('documents.upload_portal_url');
        $uploadUrl = $uploadPortalTemplate
            ? str_replace('{uid}', $document->uid, rtrim($uploadPortalTemplate))
            : null;

        // Traducir el tipo de documento
        $documentType = $document->type ?? 'general';
        $documentTypeLabel = self::translateDocumentType($documentType, $locale);
        $documentInstructions = __("documents.types.{$documentType}.instructions", [], $locale);

        // Variables base del sistema (siempre disponibles)
        $variables = self::getSystemVariables($locale);

        // Variables específicas del documento
        $variables = array_merge($variables, [
            // Información del cliente
            'CUSTOMER_NAME' => $customerName,
            'CUSTOMER_FIRSTNAME' => $document->customer_firstname ?? '',
            'CUSTOMER_LASTNAME' => $document->customer_lastname ?? '',
            'CUSTOMER_EMAIL' => $document->customer_email ?? '',

            // Información del pedido
            'ORDER_ID' => $document->order_id ?? '',
            'ORDER_REFERENCE' => $document->order_reference ?? '',

            // Información del documento
            'DOCUMENT_TYPE' => $documentType,
            'DOCUMENT_TYPE_LABEL' => $documentTypeLabel,
            'DOCUMENT_INSTRUCTIONS' => $documentInstructions,
            'DOCUMENT_UID' => $document->uid ?? '',

            // Enlaces y fechas
            'UPLOAD_LINK' => $uploadUrl ?? '',
            'UPLOAD_URL' => $uploadUrl ?? '',
            'EXPIRATION_DATE' => $uploadDeadline ?? '',
            'DEADLINE' => $uploadDeadline ?? '',
        ]);

        // Agregar variables de documentos faltantes si aplica
        if (! empty($missingDocs)) {
            $formattedDocs = self::formatMissingDocuments($missingDocs, $locale);
            $variables['MISSING_DOCUMENTS'] = $formattedDocs;
            $variables['MISSING_DOCUMENTS_LIST'] = $formattedDocs;
            $variables['REQUIRED_DOCUMENTS_LIST'] = $formattedDocs;
            $variables['REQUEST_REASON'] = $notes ?? '';
            $variables['NOTES'] = $notes ?? '';

            // Crear sección de notas HTML solo si existen notas
            if (! empty($notes)) {
                $variables['NOTES_SECTION'] = sprintf(
                    '<div style="background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #ff9800;">
                        <p style="margin: 0; font-weight: bold; color: #374151;">Nota adicional:</p>
                        <p style="margin-top: 10px; font-style: italic; color: #555;">"%s"</p>
                    </div>',
                    htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')
                );
            } else {
                $variables['NOTES_SECTION'] = '';
            }
        }

        return $variables;
    }

    /**
     * Obtener variables del sistema (siempre disponibles)
     */
    private static function getSystemVariables(string $locale = 'es'): array
    {
        return [
            // Información de la empresa
            'COMPANY_NAME' => config('app.name', 'Alsernet'),
            'SITE_NAME' => config('app.name', 'Alsernet'),
            'SITE_URL' => config('app.url', 'https://example.com'),

            // Contacto y soporte
            'SUPPORT_EMAIL' => config('mail.support.address', 'soporte@example.com'),
            'SUPPORT_PHONE' => config('app.support_phone', '+34 900 000 000'),
            'CONTACT_EMAIL' => config('mail.from.address', 'info@example.com'),

            // Fechas del sistema
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_DATE' => date('d/m/Y'),
            'CURRENT_DATETIME' => date('d/m/Y H:i'),

            // Idioma
            'LANG_CODE' => $locale,
            'LANGUAGE' => $locale,

            // Subject (se rellenará desde la plantilla)
            'EMAIL_SUBJECT' => '',
        ];
    }

    /**
     * Traducir tipo de documento con fallback
     */
    private static function translateDocumentType(string $documentType, string $locale = 'es'): string
    {
        $translationKey = "documents.types.{$documentType}.label";
        $translated = __($translationKey, [], $locale);

        // Si no existe traducción, usar el código con formato legible
        if ($translated === $translationKey) {
            return ucfirst(str_replace('_', ' ', $documentType));
        }

        return $translated;
    }

    /**
     * Formatear lista de documentos faltantes con traducciones
     */
    private static function formatMissingDocuments(array $missingDocs, string $locale = 'es'): string
    {
        if (empty($missingDocs)) {
            return '';
        }

        $html = '<ul style="margin: 10px 0; padding-left: 20px;">';

        foreach ($missingDocs as $docCode) {
            // Traducir el nombre del documento según el idioma
            $translationKey = "documents.requirements.{$docCode}.name";
            $docName = __($translationKey, [], $locale);

            // Si no existe traducción, usar el código como fallback con formato legible
            if ($docName === $translationKey) {
                $docName = ucwords(str_replace('_', ' ', $docCode));
            }

            $html .= '<li style="margin: 5px 0; color: #333; font-size: 15px;">'.$docName.'</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Resolver plantilla desde configuración o usar fallback por clave
     * Primero intenta obtener ID de plantilla desde Settings, luego busca por clave
     *
     * @param  string  $settingKey  Clave de configuración (ej: documents.email_template_reminder_id)
     * @param  string  $fallbackKey  Clave por defecto si no hay configuración (ej: document_reminder)
     * @param  array<string>  $alternativeKeys  Claves alternativas si la principal no existe
     */
    private static function resolveTemplate(string $settingKey, string $fallbackKey, array $alternativeKeys = []): ?MailTemplate
    {
        // Intentar obtener ID de plantilla configurado
        $configuredTemplateId = Setting::get($settingKey);

        if ($configuredTemplateId) {
            // Buscar por ID configurado
            $template = MailTemplate::find($configuredTemplateId);
            if ($template && $template->is_enabled) {
                return $template;
            }
        }

        // Fallback: buscar por clave principal
        $template = MailTemplate::where('key', $fallbackKey)
            ->where('is_enabled', true)
            ->first();

        if ($template) {
            return $template;
        }

        // Si no encontró, intentar con claves alternativas
        foreach ($alternativeKeys as $key) {
            $template = MailTemplate::where('key', $key)
                ->where('is_enabled', true)
                ->first();

            if ($template) {
                return $template;
            }
        }

        return null;
    }
}
