<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing email_templates data to email_template_translations
        // This ensures existing templates work with the new translation system

        $templates = DB::table('email_templates')->get();

        foreach ($templates as $template) {
            // Skip if translation already exists (for idempotency)
            $exists = DB::table('email_template_translations')
                ->where('email_template_id', $template->id)
                ->where('lang_id', 1) // Default language
                ->exists();

            if ($exists) {
                continue;
            }

            // Create translation for default language
            DB::table('email_template_translations')->insert([
                'email_template_id' => $template->id,
                'lang_id' => 1, // Default language
                'subject' => $template->subject ?? '',
                'preheader' => '', // Empty for now, can be filled later
                'content' => $template->content ?? '',
                'created_at' => $template->created_at ?? now(),
                'updated_at' => $template->updated_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove translations created by this migration (translations with lang_id=1)
        // This preserves any other language translations
        DB::table('email_template_translations')
            ->where('lang_id', 1)
            ->delete();
    }
};
