<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create missing translations for all template + language combinations
     */
    public function up(): void
    {
        // Get all templates
        $templates = DB::table('email_templates')->select('id')->get();

        // Get all available languages
        $langs = DB::table('langs')->where('available', true)->select('id')->get();

        foreach ($templates as $template) {
            foreach ($langs as $lang) {
                // Check if translation exists
                $exists = DB::table('email_template_translations')
                    ->where('email_template_id', $template->id)
                    ->where('lang_id', $lang->id)
                    ->exists();

                // If not, create it with empty values
                if (! $exists) {
                    DB::table('email_template_translations')->insert([
                        'uid' => Str::uuid(),
                        'email_template_id' => $template->id,
                        'lang_id' => $lang->id,
                        'subject' => null,
                        'preheader' => null,
                        'content' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete translations that have null subject and content (created by this migration)
        // But preserve translations with actual content
        DB::table('email_template_translations')
            ->whereNull('subject')
            ->whereNull('content')
            ->delete();
    }
};
