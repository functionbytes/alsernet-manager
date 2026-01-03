<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mailer\Models\MailerTemplate;

/**
 * MigrateDocumentTemplatesSeeder
 *
 * Migrates document-related email templates to the Mailer module.
 * These are legacy templates from previous versions.
 *
 * Note: DocumentEmailTemplateSeeder provides the complete set of templates.
 */
class MigrateDocumentTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Migrating legacy document-related email templates...');

        $templates = [
            ['key' => 'document_upload_notification', 'name' => 'Solicitud Inicial de Documentos'],
            ['key' => 'document_missing_notification', 'name' => 'Documentos Faltantes o Incorrectos'],
            ['key' => 'document_upload_reminder', 'name' => 'Recordatorio de Carga de Documentos'],
            ['key' => 'document_upload_confirmation', 'name' => 'Confirmación de Documentos Recibidos'],
            ['key' => 'document_custom_email', 'name' => 'Email Personalizado de Documentos'],
        ];

        foreach ($templates as $template) {
            MailerTemplate::updateOrCreate(
                ['key' => $template['key']],
                [
                    'name' => $template['name'],
                    'module' => 'documents',
                    'description' => 'Legacy document email template',
                    'is_enabled' => true,
                ]
            );
        }

        $this->command->info('✅ Legacy document email templates migrated successfully');
    }
}
