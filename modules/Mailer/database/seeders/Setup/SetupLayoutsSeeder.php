<?php

namespace Modules\Mailer\Database\Seeders\Setup;

use Modules\Mailer\Models\MailerLayout;
use Modules\Mailer\Models\MailerLayoutLang;
use Illuminate\Database\Seeder;

/**
 * SetupLayoutsSeeder
 *
 * Seeds the base email layout components (header, footer, wrapper).
 * These are foundational layouts used by all email templates.
 */
class SetupLayoutsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Try to get a language, but don't fail if none exists
        $defaultLang = \App\Models\Lang::where('available', true)->first() ?? \App\Models\Lang::first();
        $defaultLangId = $defaultLang?->id;

        // HEADER Layout
        $headerLayout = MailerLayout::updateOrCreate(
            ['alias' => 'email_template_header'],
            [
                'group_name' => 'email_templates',
                'code' => 'email_header',
                'type' => 'partial',
                'is_protected' => true,
                'is_enabled' => true,
            ]
        );

        if ($defaultLangId) {
            MailerLayoutLang::updateOrCreate(
                ['layout_id' => $headerLayout->id, 'lang_id' => $defaultLangId],
                [
                    'subject' => 'Encabezado de plantilla de correo',
                    'content' => $this->getHeaderContent(),
                ]
            );
        }

        // FOOTER Layout
        $footerLayout = MailerLayout::updateOrCreate(
            ['alias' => 'email_template_footer'],
            [
                'group_name' => 'email_templates',
                'code' => 'email_footer',
                'type' => 'partial',
                'is_protected' => true,
                'is_enabled' => true,
            ]
        );

        if ($defaultLangId) {
            MailerLayoutLang::updateOrCreate(
                ['layout_id' => $footerLayout->id, 'lang_id' => $defaultLangId],
                [
                    'subject' => 'Pie de página de plantilla de correo',
                    'content' => $this->getFooterContent(),
                ]
            );
        }

        // WRAPPER Layout (complete layout)
        $wrapperLayout = MailerLayout::updateOrCreate(
            ['alias' => 'email_template_wrapper'],
            [
                'group_name' => 'email_templates',
                'code' => 'email_wrapper',
                'type' => 'layout',
                'is_protected' => true,
                'is_enabled' => true,
            ]
        );

        if ($defaultLangId) {
            MailerLayoutLang::updateOrCreate(
                ['layout_id' => $wrapperLayout->id, 'lang_id' => $defaultLangId],
                [
                    'subject' => 'Plantilla completa de correo',
                    'content' => $this->getWrapperContent(),
                ]
            );
        }

        $this->command->info('✓ Base email layouts seeded:');
        $this->command->info("  - Header (ID: {$headerLayout->id})");
        $this->command->info("  - Footer (ID: {$footerLayout->id})");
        $this->command->info("  - Wrapper (ID: {$wrapperLayout->id})");
        if (!$defaultLangId) {
            $this->command->line('  ⚠ Note: Language translations skipped (no languages found)');
        }
    }

    private function getHeaderContent(): string
    {
        return <<<'HTML'
<div style="background-color: #ffffff; padding: 20px; border-bottom: 1px solid #e0e0e0; text-align: center;">
    <h1 style="margin: 0; font-size: 24px; color: #333;">Bienvenido</h1>
</div>
HTML;
    }

    private function getFooterContent(): string
    {
        return <<<'HTML'
<div style="background-color: #f5f5f5; padding: 20px; border-top: 1px solid #e0e0e0; text-align: center; font-size: 12px; color: #666;">
    <p style="margin: 0;">© {CURRENT_YEAR} {SITE_NAME}. Todos los derechos reservados.</p>
    <p style="margin: 5px 0 0 0; font-size: 11px;">Este es un correo automático, por favor no responda a este mensaje.</p>
</div>
HTML;
    }

    private function getWrapperContent(): string
    {
        return <<<'HTML'
{{ header }}
{{ content }}
{{ footer }}
HTML;
    }
}
