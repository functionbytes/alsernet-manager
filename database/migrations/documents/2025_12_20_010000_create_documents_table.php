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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->nullable()->constrained('document_types')->nullOnDelete();

            // Campo JSON para almacenar adjuntos adicionales cargados por administrativos
            // Estos documentos son visibles para contabilidad y otros grupos posteriores
            $table->json('additional_attachments')->nullable();

            // Usuario específico asignado para validar el documento en la etapa actual
            // NULL = asignado a todo el grupo
            $table->foreignId('assigned_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Índices
            $table->index('validation_status', 'idx_validation_status_additional');
            $table->index(['assigned_user_id', 'validation_status'], 'idx_assigned_validation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
