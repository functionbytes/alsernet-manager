<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            // Almacenar documentos requeridos como JSON
            // Estructura: { "dni_frontal": true, "dni_trasera": true, "licencia": false }
            $table->json('required_documents')->nullable()
                ->after('type')
                ->comment('Required documents for this document type in JSON format');

            // Almacenar documentos que ya fueron cargados correctamente
            $table->json('uploaded_documents')->nullable()
                ->after('required_documents')
                ->comment('Uploaded documents with their validation status in JSON format');

            // Agregar índice para búsquedas
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn(['required_documents', 'uploaded_documents']);
        });
    }
};
