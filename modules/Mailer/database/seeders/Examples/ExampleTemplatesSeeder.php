<?php

namespace Modules\Mailer\Database\Seeders\Examples;

use Modules\Mailer\Models\MailerTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ExampleTemplatesSeeder
 *
 * Seeds example email templates to provide templates developers/admins can use
 * or use as references for creating their own templates.
 */
class ExampleTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'key' => 'welcome_email',
                'name' => 'Correo de Bienvenida',
                'description' => 'Correo de bienvenida enviado a nuevos usuarios',
                'module' => 'core',
            ],
            [
                'key' => 'password_reset',
                'name' => 'Restablecimiento de Contraseña',
                'description' => 'Correo enviado cuando un usuario solicita restablecer su contraseña',
                'module' => 'core',
            ],
            [
                'key' => 'email_verification',
                'name' => 'Verificación de Email',
                'description' => 'Correo de verificación de dirección de email',
                'module' => 'core',
            ],
        ];

        foreach ($templates as $template) {
            $mailerTemplate = MailerTemplate::updateOrCreate(
                ['key' => $template['key']],
                [
                    'uid' => (string) Str::ulid(),
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'module' => $template['module'],
                    'is_enabled' => true,
                ]
            );

            $this->command->info("✓ Example template: {$template['name']} (ID: {$mailerTemplate->id})");
        }

        $this->command->info('✓ All example templates seeded successfully (' . count($templates) . ' templates)');
    }
}
