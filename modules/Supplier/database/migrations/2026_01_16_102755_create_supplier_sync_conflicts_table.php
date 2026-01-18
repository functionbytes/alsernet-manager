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
        Schema::create('supplier_sync_conflicts', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // 'price', 'product', 'provider'
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('erp_id')->nullable();
            $table->string('resolution_strategy')->default('erp_wins');
            $table->json('local_data')->nullable(); // Datos del sistema local
            $table->json('erp_data')->nullable(); // Datos del ERP
            $table->json('resolved_data')->nullable(); // Datos finales aplicados
            $table->json('changed_fields')->nullable(); // Campos que cambiaron
            $table->timestamp('conflict_detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            $table->string('resolved_by_ip')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index(['entity_type', 'entity_id']);
            $table->index('conflict_detected_at');
            $table->index('resolved_at');
            $table->index('resolution_strategy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sync_conflicts');
    }
};
