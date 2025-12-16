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
        Schema::table('layouts', function (Blueprint $table) {
            // Remover campos que ahora estarán en layout_translations
            $table->dropColumn(['subject', 'content', 'lang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layouts', function (Blueprint $table) {
            // Restaurar campos si se hace rollback
            $table->string('subject')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedBigInteger('lang_id')->nullable();
        });
    }
};
