<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Renombrar tablas para consistencia
        Schema::rename('layouts', 'email_layouts');
        Schema::rename('layout_translations', 'email_layout_translations');
        Schema::rename('email_template_translations', 'email_template_langs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios
        Schema::rename('email_template_langs', 'email_template_translations');
        Schema::rename('email_layout_translations', 'layout_translations');
        Schema::rename('email_layouts', 'layouts');
    }
};
