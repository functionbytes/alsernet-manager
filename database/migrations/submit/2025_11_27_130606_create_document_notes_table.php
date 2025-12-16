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
        Schema::create('request_document_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->index();
            $table->unsignedBigInteger('created_by'); // ID del administrador que crea la nota
            $table->text('content'); // Contenido de la nota
            $table->boolean('is_internal')->default(true); // Si es visible solo para admins o también para clientes
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('request_documents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_document_notes');
    }
};
