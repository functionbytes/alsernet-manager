<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create missing translations for all layout + language combinations
     */
    public function up(): void
    {
        // Get all layouts
        $layouts = DB::table('layouts')->select('id')->get();

        // Get all available languages
        $langs = DB::table('langs')->where('available', true)->select('id')->get();

        foreach ($layouts as $layout) {
            foreach ($langs as $lang) {
                // Check if translation exists
                $exists = DB::table('layout_translations')
                    ->where('layout_id', $layout->id)
                    ->where('lang_id', $lang->id)
                    ->exists();

                // If not, create it with empty values
                if (! $exists) {
                    DB::table('layout_translations')->insert([
                        'uid' => Str::uuid(),
                        'layout_id' => $layout->id,
                        'lang_id' => $lang->id,
                        'subject' => null,
                        'preheader' => null,
                        'content' => '',
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
        DB::table('layout_translations')
            ->whereNull('subject')
            ->whereNull('content')
            ->delete();
    }
};
