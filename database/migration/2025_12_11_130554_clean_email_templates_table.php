<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove subject, content, and lang_id columns from email_templates
     * These are now stored in email_template_translations
     */
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // First, drop the unique constraint that uses lang_id
            $table->dropUnique('email_templates_key_lang_unique');
        });

        Schema::table('email_templates', function (Blueprint $table) {
            // Then drop the columns that are now in translations table
            $table->dropColumn([
                'subject',    // Moved to email_template_translations
                'content',    // Moved to email_template_translations
                'lang_id',    // No longer needed, all translations in separate table
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Restore the columns in case of rollback
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Restore columns
            $table->string('subject', 191)->default('')->after('name');
            $table->longText('content')->nullable()->after('subject');
            $table->unsignedBigInteger('lang_id')->nullable()->after('module');
        });
    }
};
