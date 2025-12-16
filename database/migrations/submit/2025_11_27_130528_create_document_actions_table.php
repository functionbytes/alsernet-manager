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
        Schema::create('request_document_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->index();
            $table->string('action_type'); // email_sent, reminder_sent, upload_confirmed, admin_note, status_changed, etc
            $table->string('action_name'); // Nombre legible de la acción
            $table->text('description')->nullable(); // Descripción de la acción
            $table->json('metadata')->nullable(); // Datos adicionales (email_to, subject, etc)
            $table->unsignedBigInteger('performed_by')->nullable(); // ID del usuario que realizó la acción
            $table->string('performed_by_type')->default('system'); // 'admin', 'customer', 'system'
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('request_documents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_document_actions');
    }
};
