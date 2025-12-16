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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique(); // Identificador público único
            $table->string('key')->unique(); // Clave única: 'document_uploaded', 'order_confirmed'
            $table->string('name'); // Nombre visible: 'Documento Subido'
            $table->string('subject'); // Asunto del email
            $table->longText('content'); // Contenido HTML
            $table->unsignedBigInteger('layout_id')->nullable(); // FK a layouts para header/footer
            $table->boolean('is_enabled')->default(true); // Si está activo
            $table->json('variables')->nullable(); // Variables/tags disponibles en JSON
            $table->string('module')->default('core'); // Módulo: 'documents', 'orders', 'notifications', 'core'
            $table->text('description')->nullable(); // Descripción para admin
            $table->timestamps(); // created_at, updated_at

            // Foreign key a layouts
            $table->foreign('layout_id')
                ->references('id')
                ->on('layouts')
                ->onDelete('set null');

            // Indices para performance
            $table->index('module');
            $table->index('is_enabled');
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
