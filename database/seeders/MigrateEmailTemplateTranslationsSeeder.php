<?php

namespace Database\Seeders;

use App\Models\Mail\MailTemplate;
use App\Models\Mail\MailTemplateTranslation;
use Illuminate\Database\Seeder;

class MigrateEmailTemplateTranslationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all email templates
        $templates = MailTemplate::all();

        foreach ($templates as $template) {
            // Get the language ID from the template (or default to 1)
            $langId = $template->lang_id ?? 1;

            // Check if translation already exists
            $exists = MailTemplateTranslation::where('email_template_id', $template->id)
                ->where('lang_id', $langId)
                ->exists();

            if (! $exists && ($template->subject || $template->content)) {
                // Create translation from template
                MailTemplateTranslation::create([
                    'email_template_id' => $template->id,
                    'lang_id' => $langId,
                    'subject' => $template->subject,
                    'preheader' => null,
                    'content' => $template->content,
                ]);

                $this->command->info("Created translation for template ID: {$template->id}, Lang ID: {$langId}");
            }
        }

        $this->command->info('Email template translations migration completed!');
    }
}
