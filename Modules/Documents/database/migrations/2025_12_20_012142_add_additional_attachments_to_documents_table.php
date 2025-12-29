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
        Schema::table('documents', function (Blueprint $table) {
            // Campo JSON para almacenar adjuntos adicionales cargados por administrativos
            // Estos documentos son visibles para contabilidad y otros grupos posteriores
            $table->json('additional_attachments')->nullable()->after('uploaded_documents');

            // Índice para consultas
            $table->index('validation_status', 'idx_validation_status_additional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_validation_status_additional');
            $table->dropColumn('additional_attachments');
        });
    }
};
