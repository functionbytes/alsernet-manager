<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration enhances the existing supplier_sync_failures table
     * with batch tracking and additional retry logic support.
     */
    public function up(): void
    {
        Schema::table('supplier_sync_failures', function (Blueprint $table) {
            // Add batch relationship if not already present
            if (! Schema::hasColumn('supplier_sync_failures', 'batch_id')) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('uid');
                $table->foreign('batch_id')->references('id')->on('supplier_sync_batches')->nullOnDelete();
                $table->index('batch_id');
            }

            // Add status tracking if not already present
            if (! Schema::hasColumn('supplier_sync_failures', 'failure_status')) {
                $table->string('failure_status', 50)->default('pending')->after('retry_count'); // 'pending', 'resolved', 'acknowledged', 'archived'
                $table->index('failure_status');
            }

            // Add maximum retry attempts tracking if not already present
            if (! Schema::hasColumn('supplier_sync_failures', 'max_retries')) {
                $table->integer('max_retries')->default(5)->after('retry_count');
            }

            // Add entity relationship if not already present
            if (! Schema::hasColumn('supplier_sync_failures', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable()->after('supplier_id');
                $table->index(['sync_type', 'entity_id']);
            }

            // Add context information if not already present
            if (! Schema::hasColumn('supplier_sync_failures', 'context')) {
                $table->json('context')->nullable()->after('changed_data');
            }

            // Add resolution tracking if not already present
            if (! Schema::hasColumn('supplier_sync_failures', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('last_retry_at');
                $table->unsignedBigInteger('resolved_by_user_id')->nullable()->after('resolved_at');
                $table->text('resolution_notes')->nullable()->after('resolved_by_user_id');
                $table->index('resolved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_sync_failures', function (Blueprint $table) {
            // Remove foreign key and columns added in this migration
            if (Schema::hasColumn('supplier_sync_failures', 'batch_id')) {
                $table->dropForeignKeyIfExists(['batch_id']);
                $table->dropIndexIfExists(['batch_id']);
                $table->dropColumn('batch_id');
            }

            if (Schema::hasColumn('supplier_sync_failures', 'failure_status')) {
                $table->dropIndexIfExists(['failure_status']);
                $table->dropColumn('failure_status');
            }

            if (Schema::hasColumn('supplier_sync_failures', 'max_retries')) {
                $table->dropColumn('max_retries');
            }

            if (Schema::hasColumn('supplier_sync_failures', 'entity_id')) {
                $table->dropIndexIfExists(['sync_type', 'entity_id']);
                $table->dropColumn('entity_id');
            }

            if (Schema::hasColumn('supplier_sync_failures', 'context')) {
                $table->dropColumn('context');
            }

            if (Schema::hasColumn('supplier_sync_failures', 'resolved_at')) {
                $table->dropIndexIfExists(['resolved_at']);
                $table->dropColumn(['resolved_at', 'resolved_by_user_id', 'resolution_notes']);
            }
        });
    }
};
