<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remover la constraint única anterior en 'key' de email_templates
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropUnique('email_templates_key_unique');
        });

        // Agregar constraint única compuesta (key, lang_id)
        Schema::table('email_templates', function (Blueprint $table) {
            $table->unique(['key', 'lang_id'], 'email_templates_key_lang_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropUnique('email_templates_key_lang_unique');
            $table->unique('key', 'email_templates_key_unique');
        });
    }
};
