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
        Schema::create('supplier_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('status_id')->nullable();
            $table->string('entity_type', 50); // 'product', 'category', 'price', 'provider'
            $table->unsignedBigInteger('entity_id')->nullable(); // ID of the synced entity
            $table->unsignedBigInteger('erp_id')->nullable(); // ID from ERP system
            $table->string('action', 50); // 'create', 'update', 'delete', 'skip'
            $table->string('result', 50); // 'success', 'failed', 'skipped', 'warning'
            $table->text('message')->nullable(); // Detailed log message
            $table->json('data_before')->nullable(); // State before sync
            $table->json('data_after')->nullable(); // State after sync
            $table->json('changes')->nullable(); // Specific field changes
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->integer('duration_ms')->nullable(); // Execution time in milliseconds
            $table->string('triggered_by', 100)->nullable(); // 'sync_job', 'manual_action', 'webhook', 'scheduled'
            $table->json('metadata')->nullable(); // Additional context data
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('supplier_sync_batches')->cascadeOnDelete();
            $table->foreign('status_id')->references('id')->on('supplier_sync_statuses')->nullOnDelete();
            $table->index(['batch_id', 'result']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('result');
            $table->index('error_code');
            $table->index('synced_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sync_logs');
    }
};
