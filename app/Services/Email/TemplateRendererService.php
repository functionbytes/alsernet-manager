<?php

namespace App\Services\Email;

use App\Library\HtmlHandler\AddDoctype;
use App\Library\HtmlHandler\DecodeHtmlSpecialChars;
use App\Library\HtmlHandler\GenerateSpintax;
use App\Library\HtmlHandler\MakeInlineCss;
use App\Library\HtmlHandler\ParseRss;
use App\Library\HtmlHandler\TransformWidgets;
use App\Models\Mail\MailLayout;
use App\Models\Mail\MailTemplate;
use App\Models\Template\Template;
use League\Pipeline\PipelineBuilder;

/**
 * TemplateRendererService
 *
 * Servicio centralizado para renderizar plantillas de email (transaccionales y campañas)
 * Reemplaza variables y aplica layouts de forma consistente
 */
class TemplateRendererService
{
    /**
     * Renderizar plantilla de email transaccional (EmailTemplate)
     */
    public static function renderEmailTemplate(MailTemplate $template, array $variables = [], ?int $langId = null): string
    {
        return self::render($template, $variables, 'email', $langId);
    }

    /**
     * Renderizar plantilla de campaña (Template)
     */
    public static function renderCampaignTemplate(Template $template, array $variables = [], ?int $langId = null): string
    {
        return self::render($template, $variables, 'campaign', $langId);
    }

    /**
     * Renderizar plantilla genérica
     *
     * @param  MailTemplate|Template|MailLayout  $template
     * @param  string  $type  ('email', 'campaign', 'layout')
     * @param  int|null  $langId  ID del idioma para obtener traducciones
     */
    private static function render($template, array $variables = [], string $type = 'email', ?int $langId = null): string
    {
        // Si no se especifica langId, usar el idioma por defecto (1)
        $langId = $langId ?? 1;

        // 1. Obtener contenido base (usando traducción si está disponible)
        if (method_exists($template, 'translate')) {
            $translation = $template->translate($langId);
            $content = $translation?->content ?? $template->content ?? '';
        } else {
            $content = $template->content ?? '';
        }

        // 2. Reemplazar variables {TAG}
        $content = self::replaceVariables($content, $variables);

        // 3. Aplicar layout si existe
        if (method_exists($template, 'layout') && $template->layout) {
            // Obtener traducción del layout según el idioma
            $layoutTranslation = $template->layout->translate($langId);
            $layoutContent = $layoutTranslation?->content ?? $template->layout->content ?? '';

            // Reemplazar variables también en el layout
            $layoutContent = self::replaceVariables($layoutContent, $variables);

            // El layout debe tener {CONTENT} para insertar el contenido
            $layoutContent = str_replace('{{ content }}', $content, $layoutContent);

            // 3.1 Procesar tags especiales {{ header }} y {{ footer }} usando el langId
            $layoutContent = self::renderLayoutTags($layoutContent, $variables, $langId);

            $content = $layoutContent;
        }

        // 4. Aplicar pipeline de procesamiento (para campañas)
        if ($type === 'campaign' && $template instanceof Template) {
            $content = self::processPipeline($template, $content);
        }

        return $content;
    }

    /**
     * Procesar tags especiales en layouts: {{ header }} y {{ footer }}
     * Usa traducciones según el idioma especificado
     *
     * @param  string  $content  Contenido del layout con tags {{ header }} y {{ footer }}
     * @param  array  $variables  Variables para reemplazar
     * @param  int  $langId  ID del idioma para obtener traducciones
     */
    private static function renderLayoutTags(string $content, array $variables = [], int $langId = 1): string
    {
        // Procesar {{ header }}
        if (str_contains($content, '{{ header }}')) {
            $headerLayout = MailLayout::where('alias', 'email_template_header')->first();
            if ($headerLayout) {
                // Obtener traducción del header según el idioma
                $headerTranslation = $headerLayout->translate($langId);
                $headerContent = $headerTranslation?->content ?? $headerLayout->content ?? '';
                $headerContent = self::replaceVariables($headerContent, $variables);
                $content = str_replace('{{ header }}', $headerContent, $content);
            }
        }

        // Procesar {{ footer }}
        if (str_contains($content, '{{ footer }}')) {
            $footerLayout = MailLayout::where('alias', 'email_template_footer')->first();
            if ($footerLayout) {
                // Obtener traducción del footer según el idioma
                $footerTranslation = $footerLayout->translate($langId);
                $footerContent = $footerTranslation?->content ?? $footerLayout->content ?? '';
                $footerContent = self::replaceVariables($footerContent, $variables);
                $content = str_replace('{{ footer }}', $footerContent, $content);
            }
        }

        return $content;
    }

    /**
     * Reemplazar variables {TAG} con valores
     */
    public static function replaceVariables(string $content, array $variables = []): string
    {
        foreach ($variables as $key => $value) {
            // Manejar tanto claves con y sin llaves
            $placeholder = str_starts_with($key, '{') ? $key : '{'.$key.'}';

            // Reemplazar solo si valor no es array/object
            if (! is_array($value) && ! is_object($value)) {
                $content = str_replace($placeholder, (string) $value, $content);
            }
        }

        return $content;
    }

    /**
     * Procesar contenido a través del pipeline (para campañas)
     * Incluye: AddDoctype, ParseRss, MakeInlineCss, etc.
     */
    private static function processPipeline(Template $template, string $content): string
    {
        try {
            $pipeline = new PipelineBuilder;

            // Agregar procesadores en orden
            $pipeline->add(new AddDoctype);
            $pipeline->add(new ParseRss);

            // CSS Inlining (importante para email)
            $cssFiles = $template->findCssFiles();
            $pipeline->add(new MakeInlineCss($cssFiles));

            $pipeline->add(new TransformWidgets);
            $pipeline->add(new DecodeHtmlSpecialChars);
            $pipeline->add(new GenerateSpintax);

            // Ejecutar pipeline
            return $pipeline->build()->process($content);
        } catch (\Exception $e) {
            // Si hay error en pipeline, devolver contenido sin procesar
            \Log::warning('Error en TemplateRendererService pipeline: '.$e->getMessage());

            return $content;
        }
    }

    /**
     * Obtener HTML para preview (con variables reemplazadas por ejemplos)
     *
     * @param  MailTemplate|Template  $template
     */
    public static function getPreviewHtml($template, bool $includeLayout = true): string
    {
        // Obtener variables disponibles
        $variables = [];

        if ($template instanceof MailTemplate) {
            $vars = $template->getAvailableVariables();
            foreach ($vars as $var) {
                $variables[$var['name']] = self::getExampleValue($var['name']);
            }
        } elseif ($template instanceof Template) {
            $vars = Template::tags();
            foreach ($vars as $var) {
                $variables[$var['name']] = self::getExampleValue($var['name']);
            }
        }

        // Renderizar con variables de ejemplo
        return self::render($template, $variables);
    }

    /**
     * Obtener valor de ejemplo para una variable
     * Lee valores reales desde Settings cuando están disponibles
     */
    public static function getExampleValue(string $variableName): string
    {
        // Variables que se leen desde Settings
        $settingMappings = [
            'REMINDER_MESSAGE' => 'documents.reminder_message',
            'INITIAL_REQUEST_MESSAGE' => 'documents.initial_request_message',
            'REQUEST_REASON' => 'documents.missing_docs_message',
            'SUPPORT_EMAIL' => 'mail.from.address',
            'SUPPORT_PHONE' => 'documents.support_phone',
            'COMPANY_NAME' => 'app.name',
        ];

        // Si la variable tiene un mapping a Settings, usarlo primero
        if (isset($settingMappings[$variableName])) {
            $settingValue = \Illuminate\Support\Facades\Setting::get($settingMappings[$variableName], '');
            if ($settingValue) {
                return (string) $settingValue;
            }
        }

        // Ejemplos fallback para otras variables
        $examples = [
            // Globales
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),

            // Documentos
            'CUSTOMER_NAME' => 'Juan García',
            'CUSTOMER_EMAIL' => 'juan@example.com',
            'ORDER_ID' => '12345',
            'ORDER_NUMBER' => 'ORD-2025-001',
            'ORDER_DATE' => date('d/m/Y'),
            'ORDER_TOTAL' => '$5,000.00',
            'ORDER_STATUS' => 'Completada',
            'ORDER_REFERENCE' => 'ORD-2025-001',
            'DOCUMENT_TYPE' => 'Cédula de Ciudadanía',
            'DOCUMENT_TYPE_LABEL' => 'Cédula de Ciudadanía',
            'UPLOAD_LINK' => 'https://Alsernet.test/upload/doc-12345',
            'EXPIRATION_DATE' => date('d/m/Y', strtotime('+7 days')),
            'MISSING_DOCUMENTS' => '• Cédula de Ciudadanía<br>• Comprobante de domicilio',
            'REQUIRED_DOCUMENTS_LIST' => '<ul><li>Cédula de Ciudadanía</li><li>Comprobante de domicilio</li></ul>',
            'DAYS_SINCE_REQUEST' => '5',
            'REMINDER_MESSAGE' => 'Por favor no olvide subir sus documentos urgentemente.',
            'INITIAL_REQUEST_MESSAGE' => 'Gracias por su compra. Cargue los documentos requeridos en la plataforma.',
            'REQUEST_REASON' => 'Por favor cargue los documentos faltantes.',
            'REJECTION_REASON' => 'Los documentos no cumplen con los requisitos mínimos de calidad.',
            'NEXT_STEPS' => 'Su pedido está listo para ser entregado.',
            'UPLOADED_DOCUMENTS_COUNT' => '3',

            // Sistema
            'SITE_NAME' => 'Alsernet',
            'SITE_URL' => config('app.url'),
            'SITE_EMAIL' => 'info@Alsernet.test',
            'COMPANY_NAME' => config('app.name'),
            'SUPPORT_EMAIL' => config('mail.from.address'),
            'SUPPORT_PHONE' => '+1 (555) 000-0000',

            // Notificaciones
            'RECIPIENT_NAME' => 'María López',
            'RECIPIENT_EMAIL' => 'maria@example.com',
            'NOTIFICATION_TYPE' => 'Información General',
            'NOTIFICATION_DATE' => date('d/m/Y H:i'),

            // Campañas
            'SUBSCRIBER_EMAIL' => 'subscriber@example.com',
            'SUBSCRIBER_UID' => 'abc123def456',
            'UNSUBSCRIBE_URL' => 'https://Alsernet.test/unsubscribe/abc123',
            'WEB_VIEW_URL' => 'https://Alsernet.test/view/abc123',
            'UPDATE_PROFILE_URL' => 'https://Alsernet.test/profile/abc123',
            'CAMPAIGN_NAME' => 'Newsletter Noviembre 2025',
            'CAMPAIGN_UID' => 'campaign123',
            'CAMPAIGN_SUBJECT' => 'Novedad importantes',
            'CAMPAIGN_FROM_EMAIL' => 'noreply@Alsernet.test',
            'CAMPAIGN_FROM_NAME' => 'Alsernet',
            'CAMPAIGN_REPLY_TO' => 'support@Alsernet.test',
            'LIST_NAME' => 'Suscriptores Premium',
            'LIST_FROM_NAME' => 'Alsernet',
            'LIST_FROM_EMAIL' => 'info@Alsernet.test',
        ];

        return $examples[$variableName] ?? '{{'.$variableName.'}}';
    }

    /**
     * Validar que template tiene todas las variables requeridas
     *
     * @return array ['valid' => bool, 'missing' => array]
     */
    public static function validateTemplate(MailTemplate $template): array
    {
        $missing = $template->getMissingVariables();

        return [
            'valid' => count($missing) === 0,
            'missing' => $missing,
            'message' => count($missing) === 0
                ? 'Template completo'
                : 'Faltan '.count($missing).' variables requeridas',
        ];
    }

    /**
     * Obtener estadísticas del template
     *
     * @param  MailTemplate|Template  $template
     */
    public static function getStats($template): array
    {
        $content = $template->content ?? '';

        // Extraer variables usadas
        preg_match_all('/\{([A-Z_]+)\}/', $content, $matches);
        $usedVariables = array_unique($matches[1]);

        return [
            'html_size' => strlen($content),
            'html_size_kb' => round(strlen($content) / 1024, 2),
            'variables_used' => count($usedVariables),
            'variables_list' => $usedVariables,
            'has_layout' => method_exists($template, 'layout') && $template->layout !== null,
            'is_enabled' => $template->is_enabled ?? true,
        ];
    }

    /**
     * Enviar email de prueba
     * NOTA: Este método solo prepara el HTML. El envío real se hace en el controller
     *
     * @return array ['html' => string, 'subject' => string]
     */
    public static function prepareTestEmail(MailTemplate $template, string $recipient = ''): array
    {
        $html = self::getPreviewHtml($template);

        return [
            'to' => $recipient ?: config('app.support_email', 'admin@example.com'),
            'subject' => $template->subject ?? 'Test Email',
            'html' => $html,
            'template_name' => $template->name ?? 'Unknown',
        ];
    }

    /**
     * Convertir plantilla HTML a plain text (básico)
     */
    public static function htmlToPlainText(string $html): string
    {
        // Remover script y style
        $text = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
        $text = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $text);

        // Convertir line breaks
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<p[^>]*>/i', "\n", $text);
        $text = preg_replace('/<\/p>/i', "\n", $text);

        // Remover HTML tags
        $text = strip_tags($text);

        // Limpiar espacios
        $text = html_entity_decode($text);
        $text = trim(preg_replace('/\s+/', ' ', $text));

        return $text;
    }
}
