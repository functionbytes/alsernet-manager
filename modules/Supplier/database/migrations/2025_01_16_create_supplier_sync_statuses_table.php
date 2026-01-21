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
        Schema::create('supplier_sync_statuses', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('sync_type', 50); // 'product', 'category', 'price', 'provider'
            $table->string('sync_scope', 50)->default('all'); // 'all', 'per_supplier', 'per_category'
            $table->string('status', 50)->default('pending'); // 'pending', 'running', 'completed', 'failed', 'paused'
            $table->integer('total_items')->default(0);
            $table->integer('synced_items')->default(0);
            $table->integer('failed_items')->default(0);
            $table->integer('skipped_items')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->float('elapsed_seconds')->nullable();
            $table->float('memory_used_mb')->nullable();
            $table->string('triggered_by', 100)->nullable(); // 'manual', 'scheduled', 'webhook', 'api'
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->foreign('batch_id')->references('id')->on('supplier_sync_batches')->nullOnDelete();
            $table->index(['status', 'sync_type']);
            $table->index('sync_scope');
            $table->index('started_at');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sync_statuses');
    }
};
