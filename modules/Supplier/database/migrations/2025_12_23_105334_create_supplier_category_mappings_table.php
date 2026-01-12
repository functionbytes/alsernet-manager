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
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // IDs de ambos sistemas
            $table->unsignedBigInteger('supplier_category_id')->comment('FK to supplier_categories');
            $table->string('external_category_id', 100)->comment('External category ID (PrestaShop, etc)');

            // Control de sincronización
            $table->timestamp('last_synced_at')->nullable()->comment('Last synchronization timestamp');
            $table->enum('sync_direction', ['laravel_to_external', 'external_to_laravel', 'manual'])->default('manual')->comment('Sync direction');
            $table->enum('sync_status', ['pending', 'in_progress', 'success', 'failed', 'conflict'])->default('pending')->index()->comment('Current sync status');

            // Detección y resolución de conflictos
            $table->json('conflict_data')->nullable()->comment('Data in conflict when sync_status = conflict');
            $table->enum('conflict_resolution', ['laravel_wins', 'supplier_wins', 'merge', 'manual', 'skip'])->nullable()->comment('How to resolve conflicts');
            $table->timestamp('conflict_detected_at')->nullable();
            $table->timestamp('conflict_resolved_at')->nullable();

            // Metadata y auditoría
            $table->json('metadata')->nullable()->comment('Additional synchronization data, logs, etc.');
            $table->string('error_message')->nullable()->comment('Error message if sync failed');
            $table->integer('sync_attempts')->default(0)->comment('Number of sync attempts');
            $table->timestamp('last_sync_attempt_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Constraints
            $table->unique(['supplier_category_id', 'external_category_id'], 'category_mapping_unique');

            // Índices
            $table->index('supplier_category_id');
            $table->index(['sync_status', 'last_synced_at'], 'sync_status_idx');
            $table->index(['conflict_resolution'], 'conflict_idx');
            $table->index(['external_category_id'], 'external_category_idx');
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
