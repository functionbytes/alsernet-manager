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
        Schema::create('supplier_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique();

            // IDs de ambos sistemas
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->unsignedBigInteger('supplier_category_id')->comment('id_category en PrestaShop');

            // Control de sincronización
            $table->timestamp('last_synced_at')->nullable();
            $table->enum('sync_direction', ['laravel_to_ps', 'ps_to_laravel', 'manual'])->default('manual');
            $table->enum('sync_status', ['pending', 'in_progress', 'success', 'failed', 'conflict'])->default('pending')->index();

            // Detección y resolución de conflictos
            $table->json('conflict_data')->nullable()->comment('Datos en conflicto cuando sync_status = conflict');
            $table->enum('conflict_resolution', ['laravel_wins', 'supplier_wins', 'merge', 'manual', 'skip'])->nullable();
            $table->timestamp('conflict_detected_at')->nullable();
            $table->timestamp('conflict_resolved_at')->nullable();

            // Metadata y auditoría
            $table->json('metadata')->nullable()->comment('Datos adicionales de sincronización, logs, etc.');
            $table->string('error_message')->nullable();
            $table->integer('sync_attempts')->default(0);
            $table->timestamp('last_sync_attempt_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Constraints
            $table->unique(['category_id', 'supplier_category_id'], 'category_supplier_unique');

            // Índices
            $table->index(['sync_status', 'last_synced_at'], 'sync_status_idx');
            $table->index(['conflict_resolution'], 'conflict_idx');
            $table->index(['supplier_category_id'], 'supplier_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_category_mappings');
    }
};
