<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Mailer\Models\MailerTemplate;

/**
 * MigrateDocumentTemplatesSeeder
 *
 * Migrates document-related email templates to the Mailer module.
 * These templates are used for document upload notifications, reminders,
 * and confirmations related to order/customer documents.
 *
 * Templates migrated:
 * - Document upload notification (initial request)
 * - Missing/incorrect documents notification
 * - Upload reminder
 * - Upload confirmation
 * - Custom document email
 */
class MigrateDocumentTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Migrating document-related email templates...');

        $this->createNotificationTemplate();
        $this->createMissingDocsTemplate();
        $this->createReminderTemplate();
        $this->createUploadedTemplate();
        $this->createCustomTemplate();

        $this->command->info('✓ Document email templates migrated successfully');
    }

    /**
     * Template: Initial Document Upload Request
     */
    private function createNotificationTemplate(): void
    {
        $template = MailerTemplate::updateOrCreate(
            ['key' => 'document_upload_notification'],
            [
                'name' => 'Solicitud Inicial de Documentos',
                'module' => 'documents',
                'description' => 'Email enviado cuando se solicita al cliente que cargue documentos por primera vez',
                'is_enabled' => true,
            ]
        );

        $this->command->info("  ✓ Solicitud Inicial (ID: {$template->id})");
    }

    /**
     * Template: Missing/Incorrect Documents
     */
    private function createMissingDocsTemplate(): void
    {
        $template = MailerTemplate::updateOrCreate(
            ['key' => 'document_missing_notification'],
            [
                'name' => 'Documentos Faltantes o Incorrectos',
                'module' => 'documents',
                'description' => 'Email enviado cuando faltan documentos o necesitan ser corregidos',
                'is_enabled' => true,
            ]
        );

        $this->command->info("  ✓ Documentos Faltantes (ID: {$template->id})");
    }

    /**
     * Template: Upload Reminder
     */
    private function createReminderTemplate(): void
    {
        $template = MailerTemplate::updateOrCreate(
            ['key' => 'document_upload_reminder'],
            [
                'name' => 'Recordatorio de Carga de Documentos',
                'module' => 'documents',
                'description' => 'Email de recordatorio cuando el cliente no ha cargado documentos en el plazo esperado',
                'is_enabled' => true,
            ]
        );

        $this->command->info("  ✓ Recordatorio (ID: {$template->id})");
    }

    /**
     * Template: Upload Confirmation
     */
    private function createUploadedTemplate(): void
    {
        $template = MailerTemplate::updateOrCreate(
            ['key' => 'document_upload_confirmation'],
            [
                'name' => 'Confirmación de Documentos Recibidos',
                'module' => 'documents',
                'description' => 'Email de confirmación cuando los documentos han sido cargados correctamente',
                'is_enabled' => true,
            ]
        );

        $this->command->info("  ✓ Confirmación de Carga (ID: {$template->id})");
    }

    /**
     * Template: Custom Document Email
     */
    private function createCustomTemplate(): void
    {
        $template = MailerTemplate::updateOrCreate(
            ['key' => 'document_custom_email'],
            [
                'name' => 'Email Personalizado de Documentos',
                'module' => 'documents',
                'description' => 'Plantilla base para emails personalizados sobre documentos',
                'is_enabled' => true,
            ]
        );

        $this->command->info("  ✓ Email Personalizado (ID: {$template->id})");
    }
}
