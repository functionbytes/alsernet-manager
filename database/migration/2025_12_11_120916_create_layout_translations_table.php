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
        Schema::create('layout_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('layout_id');
            $table->unsignedBigInteger('lang_id');
            $table->string('subject')->nullable();
            $table->longText('content');
            $table->timestamps();

            // Foreign keys
            $table->foreign('layout_id')->references('id')->on('layouts')->onDelete('cascade');
            $table->foreign('lang_id')->references('id')->on('langs')->onDelete('cascade');

            // Unique constraint: un layout no puede tener dos traducciones para el mismo idioma
            $table->unique(['layout_id', 'lang_id']);

            // Índices para mejorar performance
            $table->index('layout_id');
            $table->index('lang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layout_translations');
    }
};
