<?php

namespace Database\Seeders;

use App\Models\Lang;
use App\Models\Layout\Layout;
use App\Models\Mail\MailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all available languages for translations
        $langs = Lang::all();

        if ($langs->isEmpty()) {
            $this->command->warn('No language found in database. Please create at least one language first.');

            return;
        }

        // Get email layout wrapper
        $layoutWrapper = Layout::where('alias', 'email_template_wrapper')->first();

        // Email templates for documents
        $templates = [
            [
                'key' => 'document_initial_request',
                'name' => 'Solicitud de Documentación',
                'subject' => 'Solicitud de Documentación - pedido {ORDER_REFERENCE}',
                'content' => $this->getInitialRequestContent(),
                'description' => 'Email enviado cuando se solicita documentación al cliente',
            ],
            [
                'key' => 'document_missing_documents',
                'name' => 'Documentación Faltante',
                'subject' => 'Documentación Faltante - pedido {ORDER_REFERENCE}',
                'content' => $this->getMissingDocumentsContent(),
                'description' => 'Email enviado cuando se solicitan documentos específicos faltantes',
            ],
            [
                'key' => 'document_reminder',
                'name' => 'Recordatorio de documentación',
                'subject' => 'Recordatorio: documentación pendiente - pedido {ORDER_REFERENCE}',
                'content' => $this->getReminderContent(),
                'description' => 'Email de recordatorio enviado después de X días sin documentos',
            ],
            [
                'key' => 'document_approved',
                'name' => 'Documentos Aprobados',
                'subject' => 'Documentos Aprobados - pedido {ORDER_REFERENCE}',
                'content' => $this->getApprovedContent(),
                'description' => 'Email enviado cuando los documentos son aprobados',
            ],
            [
                'key' => 'document_rejected',
                'name' => 'Documentos Rechazados',
                'subject' => 'Documentos Rechazados - Acción Requerida - pedido {ORDER_REFERENCE}',
                'content' => $this->getRejectedContent(),
                'description' => 'Email enviado cuando los documentos son rechazados',
            ],
            [
                'key' => 'document_completed',
                'name' => 'Documentación Completa',
                'subject' => 'Documentación Completa - pedido {ORDER_REFERENCE}',
                'content' => $this->getCompletedContent(),
                'description' => 'Email enviado cuando la documentación está completa',
            ],
            [
                'key' => 'document_custom_email',
                'name' => 'Correo Personalizado',
                'subject' => '{EMAIL_SUBJECT}',
                'content' => $this->getCustomEmailContent(),
                'description' => 'Plantilla para enviar correos personalizados al cliente',
            ],
        ];

        foreach ($templates as $template) {
            // Create or get the EmailTemplate (without language-specific data)
            $emailTemplate = MailTemplate::firstOrCreate(
                [
                    'key' => $template['key'],
                    'module' => 'documents',
                ],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $template['name'],
                    'layout_id' => $layoutWrapper?->id,
                    'is_enabled' => true,
                    'description' => $template['description'],
                    'variables' => MailTemplate::defaultVariables('documents'),
                ]
            );

            // Create translations for each language
            foreach ($langs as $lang) {
                $emailTemplate->translations()->updateOrCreate(
                    [
                        'lang_id' => $lang->id,
                    ],
                    [
                        'uid' => (string) Str::uuid(),
                        'subject' => $template['subject'],
                        'content' => $template['content'],
                    ]
                );
            }
        }

        $this->command->info('✓ Document email templates created successfully');
    }

    private function getInitialRequestContent(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Estimado/a <strong>{CUSTOMER_NAME}</strong>,</p>

    <p>Gracias por realizar su compra en nuestro sitio. Para poder procesar su pedido <strong>(pedido {ORDER_REFERENCE})</strong>, necesitamos que cargue los siguientes documentos:</p>

    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h4 style="margin-top: 0; color: #2c3e50;">Documentos Requeridos para {DOCUMENT_TYPE_LABEL}</h4>
        {REQUIRED_DOCUMENTS_LIST}
    </div>

    <p><strong>Por favor, cargue los documentos antes del {EXPIRATION_DATE}</strong> a través del siguiente enlace:</p>

    <div style="text-align: center; margin: 20px 0;">
        <a href="{UPLOAD_LINK}" style="background-color: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Cargar Documentos</a>
    </div>

    <p>Si tiene alguna pregunta o necesita ayuda, contáctenos en <strong>{SUPPORT_EMAIL}</strong> o llamenos al <strong>{SUPPORT_PHONE}</strong>.</p>

    <p>Saludos cordiales,<br><strong>{COMPANY_NAME}</strong></p>
</div>
HTML;
    }

    private function getMissingDocumentsContent(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Estimado/a <strong>{CUSTOMER_NAME}</strong>,</p>

    <p>Hemos revisado su solicitud de documentos para la pedido <strong>{ORDER_REFERENCE}</strong> y detectamos que aún faltan los siguientes documentos:</p>

    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h4 style="margin-top: 0; color: #dc3545;">Documentos Faltantes</h4>
        {MISSING_DOCUMENTS}
    </div>

    {{-- Mostrar notas adicionales solo si existen --}}
    {NOTES_SECTION}

    <p><strong>Por favor, cargue los documentos faltantes en el siguiente enlace:</strong></p>

    <div style="text-align: center; margin: 20px 0;">
        <a href="{UPLOAD_LINK}" style="background-color: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Cargar Documentos Faltantes</a>
    </div>

    <p>Gracias por su colaboración,<br><strong>{COMPANY_NAME}</strong></p>
</div>
HTML;
    }

    private function getReminderContent(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Estimado/a <strong>{CUSTOMER_NAME}</strong>,</p>

    <p>Le recordamos que aún no ha cargado los documentos requeridos para su pedido <strong>{ORDER_REFERENCE}</strong>.</p>

    <p style="color: #e74c3c; font-weight: bold;">Hace {DAYS_SINCE_REQUEST} días que se le solicitó la documentación.</p>

    <p>{REMINDER_MESSAGE}</p>

    <p><strong>Por favor, cargue los documentos antes del {EXPIRATION_DATE}:</strong></p>

    <div style="text-align: center; margin: 20px 0;">
        <a href="{UPLOAD_LINK}" style="background-color: #ff9800; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Cargar Documentos Ahora</a>
    </div>

    <p>Si tiene preguntas, contáctenos:<br>
    Email: <strong>{SUPPORT_EMAIL}</strong><br>
    Teléfono: <strong>{SUPPORT_PHONE}</strong></p>

    <p>Saludos,<br><strong>{COMPANY_NAME}</strong></p>
</div>
HTML;
    }

    private function getApprovedContent(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Estimado/a <strong>{CUSTOMER_NAME}</strong>,</p>

    <p style="background-color: #d4edda; padding: 12px; border-radius: 5px; border-left: 4px solid #28a745;">
        <strong>¡Excelente!</strong> Sus documentos han sido revisados y <strong>aprobados</strong>.
    </p>

    <p>Su pedido <strong>{ORDER_REFERENCE}</strong> está siendo procesada y será completada en breve.</p>

    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h4 style="margin-top: 0;">Próximos Pasos</h4>
        <ul>
            <li>Su pedido está en proceso de cumplimiento</li>
            <li>Recibirá un email de confirmación cuando esté listo para entrega</li>
            <li>Puede rastrear su pedido en cualquier momento desde su cuenta</li>
        </ul>
    </div>

    <p>Gracias por confiar en nosotros,<br><strong>{COMPANY_NAME}</strong></p>
</div>
HTML;
    }

    private function getRejectedContent(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Estimado/a <strong>{CUSTOMER_NAME}</strong>,</p>

    <p style="background-color: #f8d7da; padding: 12px; border-radius: 5px; border-left: 4px solid #dc3545;">
        <strong>⚠ Acción Requerida:</strong> Hemos revisado los documentos enviados para su pedido <strong>{ORDER_REFERENCE}</strong> y necesitamos que realice algunas correcciones.
    </p>

    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h4 style="margin-top: 0; color: #dc3545;">Razón del Rechazo</h4>
        <p>{REJECTION_REASON}</p>
    </div>

    <p><strong>Por favor, cargue nuevamente los siguientes documentos:</strong></p>
    <div style="background-color: #fffbea; padding: 15px; border-radius: 5px; margin: 15px 0;">
        {REQUIRED_DOCUMENTS_LIST}
    </div>

    <div style="text-align: center; margin: 20px 0;">
        <a href="{UPLOAD_LINK}" style="background-color: #dc3545; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Recargar Documentos</a>
    </div>

    <p>Si tiene dudas sobre qué se debe corregir, contáctenos en <strong>{SUPPORT_EMAIL}</strong>.</p>

    <p>Saludos,<br><strong>{COMPANY_NAME}</strong></p>
</div>
HTML;
    }

    private function getCompletedContent(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <p>Estimado/a <strong>{CUSTOMER_NAME}</strong>,</p>

    <p style="background-color: #d4edda; padding: 12px; border-radius: 5px; border-left: 4px solid #28a745;">
        <strong>✓ ¡Felicidades!</strong> Su solicitud de documentación ha sido completada exitosamente.
    </p>

    <p>Nos complace informarle que su pedido <strong>{ORDER_REFERENCE}</strong> está lista para ser procesada.</p>

    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h4 style="margin-top: 0;">Estado Actual</h4>
        <ul>
            <li><strong>✓ Documentación:</strong> Completa y aprobada</li>
            <li><strong>Status:</strong> Pedido en cumplimiento</li>
            <li><strong>Próximo Paso:</strong> Entrega y seguimiento</li>
        </ul>
    </div>

    <p>Puede seguir el estado de su pedido en cualquier momento desde su cuenta en nuestro sitio.</p>

    <p>¡Gracias por su compra!<br><strong>{COMPANY_NAME}</strong></p>
</div>
HTML;
    }

    private function getCustomEmailContent(): string
    {
        return <<<'HTML'
<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto;">
        {{ content }}

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; text-align: center;">
            <p>
                Cordialmente,<br>
                <strong>{COMPANY_NAME}</strong>
            </p>
            <p style="margin-top: 15px;">
                Para soporte: <a href="mailto:{SUPPORT_EMAIL}" style="color: #007bff; text-decoration: none;">{SUPPORT_EMAIL}</a>
            </p>
        </div>
    </div>
</div>
HTML;
    }
}
