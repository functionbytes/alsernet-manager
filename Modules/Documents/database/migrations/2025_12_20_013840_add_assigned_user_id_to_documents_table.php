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
            // Usuario específico asignado para validar el documento en la etapa actual
            // NULL = asignado a todo el grupo
            $table->foreignId('assigned_user_id')
                ->nullable()
                ->after('current_validator_group')
                ->constrained('users')
                ->nullOnDelete();

            // Índice para búsquedas de documentos asignados a un usuario
            $table->index(['assigned_user_id', 'validation_status'], 'idx_assigned_validation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_assigned_validation');
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn('assigned_user_id');
        });
    }
};
