<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Generate UUIDs for all existing email_template_translations that don't have one
        $translations = DB::table('email_template_translations')
            ->whereNull('uid')
            ->get();

        foreach ($translations as $translation) {
            DB::table('email_template_translations')
                ->where('id', $translation->id)
                ->update(['uid' => Str::uuid()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set UIDs to NULL for existing translations
        DB::table('email_template_translations')->update(['uid' => null]);
    }
};
