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
        Schema::create('supplier_sync_batches', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('batch_name', 255);
            $table->string('sync_type', 50); // 'product', 'category', 'price', 'provider'
            $table->string('status', 50)->default('pending'); // 'pending', 'running', 'completed', 'failed', 'cancelled'
            $table->string('priority', 50)->default('normal'); // 'low', 'normal', 'high', 'urgent'
            $table->integer('batch_size')->default(100); // Number of items per batch iteration
            $table->integer('total_batches')->default(0); // Total number of sub-batches
            $table->integer('processed_batches')->default(0); // Completed sub-batches
            $table->integer('total_items')->default(0); // Total items in this batch
            $table->integer('processed_items')->default(0); // Items successfully processed
            $table->integer('failed_items')->default(0); // Items that failed
            $table->integer('retry_attempt')->default(0);
            $table->integer('max_retries')->default(3);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_retry_at')->nullable();
            $table->float('duration_seconds')->nullable();
            $table->string('triggered_by', 100)->nullable(); // 'manual', 'scheduled', 'webhook'
            $table->text('trigger_details')->nullable();
            $table->json('filter_criteria')->nullable(); // Filters applied to this batch (e.g., category_id, date_range)
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->index(['status', 'sync_type']);
            $table->index('supplier_id');
            $table->index(['priority', 'status']);
            $table->index('started_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sync_batches');
    }
};
