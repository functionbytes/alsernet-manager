<?php

namespace Database\Seeders;

use App\Models\Mail\MailTemplate;
use Illuminate\Database\Seeder;

class MigrateDocumentEmailTemplatesSeeder extends Seeder
{
    /**
     * Migrar las plantillas de mailers/documents al nuevo sistema
     */
    public function run()
    {
        $this->command->info('Migrando plantillas de email de documentos...');

        // 1. Solicitud Inicial de Documentos (notification.blade.php)
        $this->createNotificationTemplate();

        // 2. Documentos Faltantes (missing.blade.php)
        $this->createMissingDocsTemplate();

        // 3. Recordatorio de Documentos (reminder.blade.php)
        $this->createReminderTemplate();

        // 4. Confirmación de Carga (uploaded.blade.php)
        $this->createUploadedTemplate();

        // 5. Email Personalizado (custom.blade.php)
        $this->createCustomTemplate();

        $this->command->info('✓ Plantillas de documentos migradas exitosamente');
    }

    /**
     * Plantilla: Solicitud Inicial de Documentos
     */
    private function createNotificationTemplate()
    {
        $template = MailTemplate::updateOrCreate(
            ['key' => 'document_upload_notification'],
            [
                'name' => 'Solicitud Inicial de Documentos',
                'subject' => 'Sube la documentación para tu pedido #{ORDER_REFERENCE}',
                'module' => 'documents',
                'description' => 'Email enviado cuando se solicita al cliente que cargue documentos por primera vez',
                'is_enabled' => true,
                'content' => $this->getNotificationContent(),
            ]
        );

        $this->command->info("  ✓ Solicitud Inicial creada (ID: {$template->id})");
    }

    /**
     * Plantilla: Documentos Faltantes
     */
    private function createMissingDocsTemplate()
    {
        $template = MailTemplate::updateOrCreate(
            ['key' => 'document_missing_notification'],
            [
                'name' => 'Documentos Faltantes o Incorrectos',
                'subject' => 'Documentación pendiente para tu pedido #{ORDER_REFERENCE}',
                'module' => 'documents',
                'description' => 'Email enviado cuando faltan documentos o necesitan ser corregidos',
                'is_enabled' => true,
                'content' => $this->getMissingDocsContent(),
            ]
        );

        $this->command->info("  ✓ Documentos Faltantes creada (ID: {$template->id})");
    }

    /**
     * Plantilla: Recordatorio
     */
    private function createReminderTemplate()
    {
        $template = MailTemplate::updateOrCreate(
            ['key' => 'document_upload_reminder'],
            [
                'name' => 'Recordatorio de Carga de Documentos',
                'subject' => 'Recordatorio: Documentación pendiente #{ORDER_REFERENCE}',
                'module' => 'documents',
                'description' => 'Email de recordatorio cuando el cliente no ha cargado documentos en el plazo esperado',
                'is_enabled' => true,
                'content' => $this->getReminderContent(),
            ]
        );

        $this->command->info("  ✓ Recordatorio creada (ID: {$template->id})");
    }

    /**
     * Plantilla: Confirmación de Carga
     */
    private function createUploadedTemplate()
    {
        $template = MailTemplate::updateOrCreate(
            ['key' => 'document_upload_confirmation'],
            [
                'name' => 'Confirmación de Documentos Recibidos',
                'subject' => 'Documentación recibida correctamente #{ORDER_REFERENCE}',
                'module' => 'documents',
                'description' => 'Email de confirmación cuando los documentos han sido cargados correctamente',
                'is_enabled' => true,
                'content' => $this->getUploadedContent(),
            ]
        );

        $this->command->info("  ✓ Confirmación de Carga creada (ID: {$template->id})");
    }

    /**
     * Plantilla: Email Personalizado
     */
    private function createCustomTemplate()
    {
        $template = MailTemplate::updateOrCreate(
            ['key' => 'document_custom_email'],
            [
                'name' => 'Email Personalizado de Documentos',
                'subject' => '{CUSTOM_SUBJECT}',
                'module' => 'documents',
                'description' => 'Plantilla base para emails personalizados sobre documentos',
                'is_enabled' => true,
                'content' => $this->getCustomContent(),
            ]
        );

        $this->command->info("  ✓ Email Personalizado creado (ID: {$template->id})");
    }

    /**
     * Contenido: Solicitud Inicial
     */
    private function getNotificationContent(): string
    {
        return <<<'HTML'
<tr>
    <td class="bb-content bb-pb-0" align="center">
        <table class="bb-icon bb-icon-lg bb-bg-blue" cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td valign="middle" align="center">
                        <img src="https://cdn-icons-png.flaticon.com/512/3143/3143609.png" class="bb-va-middle" width="40" height="40" alt="Documentos">
                    </td>
                </tr>
            </tbody>
        </table>
        <h1 class="bb-text-center bb-m-0 bb-mt-md">Sube tu documentación</h1>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <p>¡Hola <strong>{CUSTOMER_NAME}</strong>!</p>
        <p>Gracias por tu compra. Para completar tu pedido <strong>#{ORDER_REFERENCE}</strong>, necesitamos que cargues la documentación solicitada en los próximos días.</p>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 0; font-weight: bold; color: #856404;">RECUERDA:</p>
            <p style="margin: 8px 0 0 0; color: #856404;">Para poder enviar tu pedido, necesitamos que nos envíes la siguiente documentación:</p>
            <ul style="margin: 8px 0; padding-left: 20px; color: #856404;">
                <li>Fotocopia de tu DNI (ambas caras)</li>
                <li>Fotocopia de tu licencia correspondiente</li>
            </ul>
        </div>
    </td>
</tr>
<tr>
    <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
        <p style="margin-bottom: 20px;"><strong>Por favor, haz clic en el siguiente enlace y sigue las instrucciones:</strong></p>
        <table cellspacing="0" cellpadding="0" style="margin: 0 auto;">
            <tbody>
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-blue bb-rounded bb-w-auto">
                            <tbody>
                                <tr>
                                    <td align="center" valign="top" class="lh-1">
                                        <a href="{UPLOAD_LINK}" class="bb-btn bb-bg-blue bb-border-blue">
                                            <span class="btn-span">Subir documentación</span>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
<tr>
    <td class="bb-content bb-pt-0">
        <div style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; border-radius: 5px;">
            <p style="margin: 0; color: #0c5460;"><strong>⏰ Importante:</strong> Te enviaremos un recordatorio si no completas la carga de documentación en 1 día.</p>
        </div>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <p><strong>¿Necesitas ayuda?</strong> Si tienes dudas sobre qué documentación enviar, responde a este correo y te asistiremos con gusto.</p>
        <p>Saludos,<br><strong>El equipo de Soporte</strong></p>
    </td>
</tr>
HTML;
    }

    /**
     * Contenido: Documentos Faltantes
     */
    private function getMissingDocsContent(): string
    {
        return <<<'HTML'
<tr>
    <td class="bb-content bb-pb-0" align="center">
        <table class="bb-icon bb-icon-lg" cellspacing="0" cellpadding="0" style="background-color: #dc3545;">
            <tbody>
                <tr>
                    <td valign="middle" align="center">
                        <img src="https://cdn-icons-png.flaticon.com/512/3119/3119338.png" class="bb-va-middle" width="40" height="40" alt="Alerta">
                    </td>
                </tr>
            </tbody>
        </table>
        <h1 class="bb-text-center bb-m-0 bb-mt-md">Documentación pendiente</h1>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <p>¡Hola <strong>{CUSTOMER_NAME}</strong>!</p>
        <p>Hemos revisado la documentación para tu pedido <strong>#{ORDER_REFERENCE}</strong> y hemos notado que falta información o algunos documentos no son legibles.</p>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <div style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; border-radius: 5px;">
            <p style="margin: 0; font-weight: bold; color: #721c24;">IMPORTANTE:</p>
            <p style="margin: 8px 0 0 0; color: #721c24;">Necesitamos que nos envíes o reenvíes los siguientes documentos para poder procesar tu pedido:</p>
            <div style="background-color: #fff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin-top: 10px;">
                <p style="margin: 0; color: #dc2626; font-weight: bold;">{MISSING_DOCUMENTS_LIST}</p>
            </div>
        </div>
    </td>
</tr>
<tr>
    <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
        <p style="margin-bottom: 20px;"><strong>Por favor, sube los documentos faltantes haciendo clic en el siguiente botón:</strong></p>
        <table cellspacing="0" cellpadding="0" style="margin: 0 auto;">
            <tbody>
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-blue bb-rounded bb-w-auto">
                            <tbody>
                                <tr>
                                    <td align="center" valign="top" class="lh-1">
                                        <a href="{UPLOAD_LINK}" class="bb-btn bb-bg-blue bb-border-blue">
                                            <span class="btn-span">Subir documentos faltantes</span>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
<tr>
    <td class="bb-content bb-pt-0">
        <p><strong>¿Tienes dudas?</strong> Responde a este correo y te ayudaremos.</p>
        <p>Saludos,<br><strong>El equipo de Soporte</strong></p>
    </td>
</tr>
HTML;
    }

    /**
     * Contenido: Recordatorio
     */
    private function getReminderContent(): string
    {
        return <<<'HTML'
<tr>
    <td class="bb-content bb-pb-0" align="center">
        <table class="bb-icon bb-icon-lg" cellspacing="0" cellpadding="0" style="background-color: #ffc107;">
            <tbody>
                <tr>
                    <td valign="middle" align="center">
                        <img src="https://cdn-icons-png.flaticon.com/512/3412/3412636.png" class="bb-va-middle" width="40" height="40" alt="Recordatorio">
                    </td>
                </tr>
            </tbody>
        </table>
        <h1 class="bb-text-center bb-m-0 bb-mt-md">Recordatorio de documentación</h1>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <p>¡Hola <strong>{CUSTOMER_NAME}</strong>!</p>
        <p>Te recordamos que aún no hemos recibido la documentación para tu pedido <strong>#{ORDER_REFERENCE}</strong>.</p>
        <p>Para que podamos procesar tu pedido lo antes posible, necesitamos que cargues los documentos solicitados.</p>
    </td>
</tr>
<tr>
    <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
        <table cellspacing="0" cellpadding="0" style="margin: 0 auto;">
            <tbody>
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-blue bb-rounded bb-w-auto">
                            <tbody>
                                <tr>
                                    <td align="center" valign="top" class="lh-1">
                                        <a href="{UPLOAD_LINK}" class="bb-btn bb-bg-blue bb-border-blue">
                                            <span class="btn-span">Subir documentación ahora</span>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
<tr>
    <td class="bb-content bb-pt-0">
        <p><strong>¿Necesitas ayuda?</strong> Si tienes alguna pregunta, no dudes en responder a este correo.</p>
        <p>Saludos,<br><strong>El equipo de Soporte</strong></p>
    </td>
</tr>
HTML;
    }

    /**
     * Contenido: Confirmación de Carga
     */
    private function getUploadedContent(): string
    {
        return <<<'HTML'
<tr>
    <td class="bb-content bb-pb-0" align="center">
        <table class="bb-icon bb-icon-lg" cellspacing="0" cellpadding="0" style="background-color: #28a745;">
            <tbody>
                <tr>
                    <td valign="middle" align="center">
                        <img src="https://cdn-icons-png.flaticon.com/512/190/190411.png" class="bb-va-middle" width="40" height="40" alt="Éxito">
                    </td>
                </tr>
            </tbody>
        </table>
        <h1 class="bb-text-center bb-m-0 bb-mt-md">Documentación recibida correctamente</h1>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <p>¡Hola <strong>{CUSTOMER_NAME}</strong>!</p>
        <p>¡Excelente noticia! Hemos recibido correctamente los documentos para tu pedido <strong>#{ORDER_REFERENCE}</strong>.</p>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <div style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; border-radius: 5px;">
            <p style="margin: 0; color: #155724;">Nuestro equipo revisará la documentación en las próximas horas y te mantendremos informado sobre el estado de tu pedido.</p>
        </div>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <p><strong>¿Tienes preguntas?</strong> Estamos aquí para ayudarte. Responde a este correo si necesitas asistencia.</p>
        <p>Saludos,<br><strong>El equipo de Soporte</strong></p>
    </td>
</tr>
HTML;
    }

    /**
     * Contenido: Email Personalizado
     */
    private function getCustomContent(): string
    {
        return <<<'HTML'
<tr>
    <td class="bb-content bb-pb-0" align="center">
        <h1 class="bb-text-center bb-m-0">{CUSTOM_TITLE}</h1>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <p>¡Hola <strong>{CUSTOMER_NAME}</strong>!</p>
        <div>{CUSTOM_MESSAGE}</div>
    </td>
</tr>
<tr>
    <td class="bb-content">
        <p>Saludos,<br><strong>El equipo de Soporte</strong></p>
    </td>
</tr>
HTML;
    }
}
