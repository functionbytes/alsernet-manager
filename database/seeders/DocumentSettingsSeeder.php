<?php

namespace Database\Seeders;

use App\Models\Setting\Setting;
use Illuminate\Database\Seeder;

class DocumentSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Email Configuration Settings
            [
                'key' => 'documents.enable_initial_request',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Activar email de solicitud inicial',
                'description' => 'Enviar email automáticamente cuando se solicitan documentos',
                'group' => 'email',
            ],
            [
                'key' => 'documents.initial_request_subject',
                'value' => 'Solicitud de Documentación - Orden {order_reference}',
                'type' => 'string',
                'label' => 'Asunto de solicitud inicial',
                'description' => 'Asunto del email cuando se solicitan documentos inicialmente',
                'group' => 'email',
            ],
            [
                'key' => 'documents.initial_request_message',
                'value' => 'Estimado cliente,\n\nLe solicitamos cargue los documentos requeridos para procesar su orden.\n\nDocumentos requeridos:\n- Documento de identidad (DNI/Cédula)\n- Comprobante de domicilio\n\nDispone de 2 días para cargar los documentos.',
                'type' => 'text',
                'label' => 'Mensaje de solicitud inicial',
                'description' => 'Mensaje personalizado en el email de solicitud inicial',
                'group' => 'email',
            ],
            [
                'key' => 'documents.enable_missing_docs',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Activar email de documentos faltantes',
                'description' => 'Enviar email cuando faltan documentos en la carga',
                'group' => 'email',
            ],
            [
                'key' => 'documents.missing_docs_subject',
                'value' => 'Documentos Incompletos - Acción Requerida',
                'type' => 'string',
                'label' => 'Asunto de documentos faltantes',
                'description' => 'Asunto del email cuando hay documentos faltantes',
                'group' => 'email',
            ],
            [
                'key' => 'documents.missing_docs_message',
                'value' => 'Le informamos que faltan documentos en su carga.\n\nDocumentos faltantes:\n- {missing_documents}\n\nPor favor cargue los documentos faltantes lo antes posible.',
                'type' => 'text',
                'label' => 'Mensaje de documentos faltantes',
                'description' => 'Mensaje cuando hay documentos faltantes',
                'group' => 'email',
            ],
            [
                'key' => 'documents.enable_reminder',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Activar recordatorios automáticos',
                'description' => 'Enviar recordatorios a clientes que no han cargado documentos',
                'group' => 'email',
            ],
            [
                'key' => 'documents.reminder_days',
                'value' => '3',
                'type' => 'integer',
                'label' => 'Días para enviar recordatorio',
                'description' => 'Días después de la solicitud para enviar recordatorio',
                'group' => 'email',
            ],
            [
                'key' => 'documents.reminder_subject',
                'value' => 'Recordatorio: Carga de Documentos Pendiente',
                'type' => 'string',
                'label' => 'Asunto del recordatorio',
                'description' => 'Asunto del email de recordatorio',
                'group' => 'email',
            ],
            [
                'key' => 'documents.reminder_message',
                'value' => 'Le recordamos que aún tiene documentos pendientes de cargar.\n\nEs importante que complete esta acción para continuar con el procesamiento de su orden.\n\nDispone de {remaining_days} días para realizar la carga.',
                'type' => 'text',
                'label' => 'Mensaje del recordatorio',
                'description' => 'Mensaje personalizado en el email de recordatorio',
                'group' => 'email',
            ],
            [
                'key' => 'documents.enable_approval_email',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Enviar email de aprobación',
                'description' => 'Notificar al cliente cuando los documentos son aprobados',
                'group' => 'email',
            ],
            [
                'key' => 'documents.approval_subject',
                'value' => 'Documentos Aprobados - Próximos Pasos',
                'type' => 'string',
                'label' => 'Asunto de aprobación',
                'description' => 'Asunto cuando los documentos son aprobados',
                'group' => 'email',
            ],
            [
                'key' => 'documents.enable_rejection_email',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Enviar email de rechazo',
                'description' => 'Notificar al cliente cuando los documentos son rechazados',
                'group' => 'email',
            ],
            [
                'key' => 'documents.rejection_subject',
                'value' => 'Documentos Rechazados - Se Requiere Acción',
                'type' => 'string',
                'label' => 'Asunto de rechazo',
                'description' => 'Asunto cuando los documentos son rechazados',
                'group' => 'email',
            ],
            [
                'key' => 'documents.enable_completion_email',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Enviar email de finalización',
                'description' => 'Notificar al cliente cuando el proceso está completo',
                'group' => 'email',
            ],
            [
                'key' => 'documents.completion_subject',
                'value' => 'Documentación Completa - Orden {order_reference}',
                'type' => 'string',
                'label' => 'Asunto de finalización',
                'description' => 'Asunto cuando la documentación está completa',
                'group' => 'email',
            ],

            // Email Template Selection - Plantillas Personalizables
            [
                'key' => 'documents.email_template_initial_request_id',
                'value' => null,
                'type' => 'integer',
                'label' => 'Template de solicitud inicial',
                'description' => 'ID del template de email para solicitud inicial (usa default si está vacío)',
                'group' => 'email',
            ],
            [
                'key' => 'documents.email_template_reminder_id',
                'value' => null,
                'type' => 'integer',
                'label' => 'Template de recordatorio',
                'description' => 'ID del template de email para recordatorio automático',
                'group' => 'email',
            ],
            [
                'key' => 'documents.email_template_missing_docs_id',
                'value' => null,
                'type' => 'integer',
                'label' => 'Template de documentos faltantes',
                'description' => 'ID del template para solicitar documentos específicos',
                'group' => 'email',
            ],
            [
                'key' => 'documents.email_template_approval_id',
                'value' => null,
                'type' => 'integer',
                'label' => 'Template de aprobación',
                'description' => 'ID del template de email para aprobación de documentos',
                'group' => 'email',
            ],
            [
                'key' => 'documents.email_template_rejection_id',
                'value' => null,
                'type' => 'integer',
                'label' => 'Template de rechazo',
                'description' => 'ID del template de email para rechazo de documentos',
                'group' => 'email',
            ],
            [
                'key' => 'documents.email_template_completion_id',
                'value' => null,
                'type' => 'integer',
                'label' => 'Template de finalización',
                'description' => 'ID del template de email para completación del proceso',
                'group' => 'email',
            ],

            // SLA Configuration Settings
            [
                'key' => 'documents.sla_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Habilitar monitoreo SLA',
                'description' => 'Activar monitoreo automático de SLA en documentos',
                'group' => 'sla',
            ],
            [
                'key' => 'documents.default_upload_time',
                'value' => '2880',
                'type' => 'integer',
                'label' => 'Tiempo de carga (minutos)',
                'description' => 'Tiempo máximo para que el cliente cargue documentos (2880 = 2 días)',
                'group' => 'sla',
            ],
            [
                'key' => 'documents.default_review_time',
                'value' => '1440',
                'type' => 'integer',
                'label' => 'Tiempo de revisión (minutos)',
                'description' => 'Tiempo máximo para revisar documentos (1440 = 1 día)',
                'group' => 'sla',
            ],
            [
                'key' => 'documents.default_approval_time',
                'value' => '2880',
                'type' => 'integer',
                'label' => 'Tiempo de aprobación (minutos)',
                'description' => 'Tiempo máximo para aprobar documentos (2880 = 2 días)',
                'group' => 'sla',
            ],
            [
                'key' => 'documents.sla_enable_escalation',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Habilitar escalación de SLA',
                'description' => 'Escalar a supervisores cuando el SLA está próximo a vencer',
                'group' => 'sla',
            ],
            [
                'key' => 'documents.sla_escalation_threshold',
                'value' => '80',
                'type' => 'integer',
                'label' => 'Umbral de escalación (%)',
                'description' => 'Porcentaje del SLA en el que se debe escalar (80% = escalar cuando se usa 80% del tiempo)',
                'group' => 'sla',
            ],
            [
                'key' => 'documents.sla_business_hours_only',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Contar solo horas de negocio',
                'description' => 'Contar solo lunes a viernes 09:00-17:00 para SLA',
                'group' => 'sla',
            ],

            // General Document Settings
            [
                'key' => 'documents.require_documents_confirmation',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Requerir confirmación de documentos',
                'description' => 'El cliente debe confirmar que todos los documentos se cargaron correctamente',
                'group' => 'general',
            ],
            [
                'key' => 'documents.allow_bulk_upload',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Permitir carga masiva',
                'description' => 'Permitir que el cliente cargue múltiples documentos de una vez',
                'group' => 'general',
            ],
            [
                'key' => 'documents.max_file_size',
                'value' => '10',
                'type' => 'integer',
                'label' => 'Tamaño máximo de archivo (MB)',
                'description' => 'Tamaño máximo permitido para cada archivo en MB',
                'group' => 'general',
            ],
            [
                'key' => 'documents.allowed_file_types',
                'value' => 'json:["pdf","jpg","jpeg","png","docx","doc"]',
                'type' => 'json',
                'label' => 'Tipos de archivo permitidos',
                'description' => 'Extensiones de archivo permitidas para carga',
                'group' => 'general',
            ],
            [
                'key' => 'documents.send_confirmation_email',
                'value' => 'true',
                'type' => 'boolean',
                'label' => 'Enviar email de confirmación',
                'description' => 'Enviar email cuando el cliente confirma la carga de documentos',
                'group' => 'general',
            ],
            [
                'key' => 'documents.document_expiration_days',
                'value' => '90',
                'type' => 'integer',
                'label' => 'Días de expiración',
                'description' => 'Días después de los cuales el cliente ya no puede cargar documentos',
                'group' => 'general',
            ],
            [
                'key' => 'documents.support_email',
                'value' => 'soporte@alsernet.com',
                'type' => 'string',
                'label' => 'Email de soporte',
                'description' => 'Email de contacto para soporte de documentación',
                'group' => 'general',
            ],
            [
                'key' => 'documents.support_phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'string',
                'label' => 'Teléfono de soporte',
                'description' => 'Número telefónico para soporte de documentación',
                'group' => 'general',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✓ Document settings created/updated successfully');
        $this->command->info('Total settings: '.count($settings));
    }
}
