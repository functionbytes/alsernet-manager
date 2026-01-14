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
        Schema::create('supplier_sync_audit', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique();

            // Sync metadata
            $table->string('entity_type', 50)->index(); // 'sports', 'categories', 'providers', 'products', etc.
            $table->string('sync_direction', 20)->index(); // 'erp_to_supplier' or 'supplier_to_erp'

            // Statistics
            $table->integer('records_synced')->default(0);
            $table->integer('records_failed')->default(0);
            $table->integer('records_skipped')->default(0);

            // Performance metrics
            $table->float('elapsed_seconds', 8, 2)->default(0);
            $table->float('memory_mb', 8, 2)->nullable();
            $table->float('peak_memory_mb', 8, 2)->nullable();

            // Additional context
            $table->json('metadata')->nullable(); // Extra info (errors, warnings, etc.)
            $table->unsignedBigInteger('cycle_number')->nullable(); // Monitor cycle number
            $table->string('triggered_by', 50)->nullable(); // 'monitor_job', 'manual_command', 'event_listener'

            // Timestamps
            $table->timestamp('synced_at')->index();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['entity_type', 'synced_at'], 'entity_synced_at_idx');
            $table->index(['sync_direction', 'synced_at'], 'direction_synced_at_idx');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sync_audit');
    }
};
