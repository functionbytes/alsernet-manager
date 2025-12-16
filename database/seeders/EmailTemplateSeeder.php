<?php

namespace Database\Seeders;

use App\Models\Layout\Layout;
use App\Models\Mail\MailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener layouts existentes
        $documentLayout = Layout::where('alias', 'document_notification')->first();

        // Template: Documento Subido
        MailTemplate::create([
            'key' => 'document_uploaded',
            'name' => 'Documento Subido - Confirmación',
            'subject' => 'Confirmación: Tu documento ha sido recibido',
            'content' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .content { margin: 20px 0; }
        .footer { border-top: 1px solid #ddd; margin-top: 20px; padding-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Documento Recibido!</h1>
        </div>

        <div class="content">
            <p>Hola {CUSTOMER_NAME},</p>

            <p>Te confirmamos que hemos recibido correctamente tu documento de tipo <strong>{DOCUMENT_TYPE}</strong>.</p>

            <p><strong>Detalles:</strong></p>
            <ul>
                <li>Orden: {ORDER_REFERENCE}</li>
                <li>Tipo de documento: {DOCUMENT_TYPE}</li>
                <li>Fecha: {CURRENT_DAY}/{CURRENT_MONTH}/{CURRENT_YEAR}</li>
            </ul>

            <p>Nuestro equipo revisará tu documento y se pondrá en contacto contigo si requiere información adicional.</p>

            <p>Gracias por tu confianza.</p>
        </div>

        <div class="footer">
            <p>© {CURRENT_YEAR} - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
HTML,
            'layout_id' => $documentLayout?->id,
            'module' => 'documents',
            'description' => 'Confirmación cuando un cliente sube un documento',
            'variables' => [
                ['name' => 'CUSTOMER_NAME', 'required' => true, 'description' => 'Nombre del cliente'],
                ['name' => 'DOCUMENT_TYPE', 'required' => true, 'description' => 'Tipo de documento'],
                ['name' => 'ORDER_REFERENCE', 'required' => true, 'description' => 'Referencia de la orden'],
                ['name' => 'CURRENT_DAY', 'required' => false, 'description' => 'Día actual'],
                ['name' => 'CURRENT_MONTH', 'required' => false, 'description' => 'Mes actual'],
                ['name' => 'CURRENT_YEAR', 'required' => false, 'description' => 'Año actual'],
            ],
        ]);

        // Template: Recordatorio de Documentos
        MailTemplate::create([
            'key' => 'document_reminder',
            'name' => 'Recordatorio - Documentos Pendientes',
            'subject' => 'Recordatorio: Completa la carga de tus documentos',
            'content' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #fff3cd; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #ffc107; }
        .button { display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .content { margin: 20px 0; }
        .footer { border-top: 1px solid #ddd; margin-top: 20px; padding-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Documentos Pendientes</h1>
        </div>

        <div class="content">
            <p>Hola {CUSTOMER_NAME},</p>

            <p>Te recordamos que aún tienes documentos pendientes por cargar en tu orden.</p>

            <p>Por favor, completa la carga de tus documentos lo antes posible para continuar con el proceso.</p>

            <p style="text-align: center;">
                <a href="{UPLOAD_LINK}" class="button">Cargar Documentos</a>
            </p>

            <p><strong>Fecha límite:</strong> {EXPIRATION_DATE}</p>

            <p>Si tienes preguntas, no dudes en contactarnos.</p>
        </div>

        <div class="footer">
            <p>© {CURRENT_YEAR} - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
HTML,
            'layout_id' => $documentLayout?->id,
            'module' => 'documents',
            'description' => 'Recordatorio para cargar documentos pendientes',
            'variables' => [
                ['name' => 'CUSTOMER_NAME', 'required' => true, 'description' => 'Nombre del cliente'],
                ['name' => 'UPLOAD_LINK', 'required' => true, 'description' => 'Link para cargar documentos'],
                ['name' => 'EXPIRATION_DATE', 'required' => false, 'description' => 'Fecha de expiración'],
                ['name' => 'CURRENT_YEAR', 'required' => false, 'description' => 'Año actual'],
            ],
        ]);

        // Template: Documentos Faltantes
        MailTemplate::create([
            'key' => 'document_missing',
            'name' => 'Notificación - Documentos Faltantes',
            'subject' => 'Información: Faltan documentos por completar',
            'content' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8d7da; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        .button { display: inline-block; padding: 12px 30px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .content { margin: 20px 0; }
        .missing-docs { background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { border-top: 1px solid #ddd; margin-top: 20px; padding-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Documentos Incompletos</h1>
        </div>

        <div class="content">
            <p>Hola {CUSTOMER_NAME},</p>

            <p>Hemos revisado tu envío de documentos y encontramos que faltan algunos documentos requeridos:</p>

            <div class="missing-docs">
                <p><strong>Documentos Faltantes:</strong></p>
                <p>{MISSING_DOCUMENTS}</p>
            </div>

            <p>Por favor, carga estos documentos para completar tu solicitud.</p>

            <p style="text-align: center;">
                <a href="{UPLOAD_LINK}" class="button">Completar Carga</a>
            </p>

            <p>Si tienes alguna pregunta sobre los documentos requeridos, contáctanos.</p>
        </div>

        <div class="footer">
            <p>© {CURRENT_YEAR} - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
HTML,
            'layout_id' => $documentLayout?->id,
            'module' => 'documents',
            'description' => 'Notificación cuando faltan documentos',
            'variables' => [
                ['name' => 'CUSTOMER_NAME', 'required' => true, 'description' => 'Nombre del cliente'],
                ['name' => 'MISSING_DOCUMENTS', 'required' => true, 'description' => 'Lista de documentos faltantes'],
                ['name' => 'UPLOAD_LINK', 'required' => true, 'description' => 'Link para cargar documentos'],
                ['name' => 'CURRENT_YEAR', 'required' => false, 'description' => 'Año actual'],
            ],
        ]);

        // Template: Documentos Aprobados
        MailTemplate::create([
            'key' => 'document_approved',
            'name' => 'Confirmación - Documentos Aprobados',
            'subject' => '✓ Tus documentos han sido aprobados',
            'content' => <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #d4edda; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .content { margin: 20px 0; }
        .footer { border-top: 1px solid #ddd; margin-top: 20px; padding-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Documentos Aprobados!</h1>
        </div>

        <div class="content">
            <p>Hola {CUSTOMER_NAME},</p>

            <p>¡Buenas noticias! Todos tus documentos han sido revisados y aprobados exitosamente.</p>

            <p>Tu solicitud ahora está completa y procederemos con el siguiente paso.</p>

            <p><strong>Detalles:</strong></p>
            <ul>
                <li>Orden: {ORDER_REFERENCE}</li>
                <li>Fecha de aprobación: {CURRENT_DAY}/{CURRENT_MONTH}/{CURRENT_YEAR}</li>
            </ul>

            <p>Gracias por proporcionar la información necesaria.</p>
        </div>

        <div class="footer">
            <p>© {CURRENT_YEAR} - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
HTML,
            'layout_id' => $documentLayout?->id,
            'module' => 'documents',
            'description' => 'Confirmación cuando documentos son aprobados',
            'variables' => [
                ['name' => 'CUSTOMER_NAME', 'required' => true, 'description' => 'Nombre del cliente'],
                ['name' => 'ORDER_REFERENCE', 'required' => true, 'description' => 'Referencia de la orden'],
                ['name' => 'CURRENT_DAY', 'required' => false, 'description' => 'Día actual'],
                ['name' => 'CURRENT_MONTH', 'required' => false, 'description' => 'Mes actual'],
                ['name' => 'CURRENT_YEAR', 'required' => false, 'description' => 'Año actual'],
            ],
        ]);
    }
}
