<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds performance indexes to frequently queried columns in supplier tables.
     * These indexes significantly improve query performance for:
     * - Product lookups by ERP ID, code, and barcode
     * - Active product filtering
     * - Price queries by provider and current status
     * - Sync timestamp checks
     * - Automation execution queries
     */
    public function up(): void
    {
        // Helper closure to check if index exists
        $indexExists = function (string $table, string $indexName): bool {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

            return count($indexes) > 0;
        };
        // Indexes for supplier_products table
        if (Schema::hasTable('supplier_products')) {
            Schema::table('supplier_products', function (Blueprint $table) use ($indexExists) {
                // Composite index for ERP product ID + active status (very common filter)
                if (! $indexExists('supplier_products', 'idx_products_erp_active')) {
                    $table->index(['erp_product_id', 'is_active'], 'idx_products_erp_active');
                }

                // Individual indexes for code and barcode lookups
                if (! $indexExists('supplier_products', 'idx_products_code')) {
                    $table->index('code', 'idx_products_code');
                }
                if (! $indexExists('supplier_products', 'idx_products_barcode')) {
                    $table->index('barcode', 'idx_products_barcode');
                }

                // Index for group relationships
                if (! $indexExists('supplier_products', 'idx_products_group')) {
                    $table->index('group_id', 'idx_products_group');
                }

                // Index for sync monitoring
                if (! $indexExists('supplier_products', 'idx_products_last_sync')) {
                    $table->index('last_synced_at', 'idx_products_last_sync');
                }
            });
        }

        // Indexes for supplier_product_prices table
        if (Schema::hasTable('supplier_product_prices')) {
            Schema::table('supplier_product_prices', function (Blueprint $table) use ($indexExists) {
                // Composite index for provider + current price filtering
                if (! $indexExists('supplier_product_prices', 'idx_prices_provider_current')) {
                    $table->index(['supplier_provider_product_id', 'is_current'], 'idx_prices_provider_current');
                }

                // Index for effective date queries (date-based filtering)
                if (! $indexExists('supplier_product_prices', 'idx_prices_effective_date')) {
                    $table->index('effective_date', 'idx_prices_effective_date');
                }

                // Index for sync monitoring
                if (! $indexExists('supplier_product_prices', 'idx_prices_last_sync')) {
                    $table->index('last_synced_at', 'idx_prices_last_sync');
                }

                // Index for active price filtering
                if (! $indexExists('supplier_product_prices', 'idx_prices_active')) {
                    $table->index('is_active', 'idx_prices_active');
                }
            });
        }

        // Indexes for supplier_erp_providers table
        if (Schema::hasTable('supplier_erp_providers')) {
            Schema::table('supplier_erp_providers', function (Blueprint $table) use ($indexExists) {
                // Index for ERP provider ID lookups
                if (! $indexExists('supplier_erp_providers', 'idx_erp_providers_erp_id')) {
                    $table->index('erp_provider_id', 'idx_erp_providers_erp_id');
                }

                // Index for code lookups
                if (! $indexExists('supplier_erp_providers', 'idx_erp_providers_code')) {
                    $table->index('code', 'idx_erp_providers_code');
                }

                // Index for active status
                if (! $indexExists('supplier_erp_providers', 'idx_erp_providers_active')) {
                    $table->index('is_active', 'idx_erp_providers_active');
                }

                // Index for sync monitoring
                if (! $indexExists('supplier_erp_providers', 'idx_erp_providers_last_sync')) {
                    $table->index('last_synced_at', 'idx_erp_providers_last_sync');
                }
            });
        }

        // Indexes for supplier_automation_executions table
        if (Schema::hasTable('supplier_automation_executions')) {
            Schema::table('supplier_automation_executions', function (Blueprint $table) use ($indexExists) {
                // Composite index for workflow + status + creation date (very common query pattern)
                if (! $indexExists('supplier_automation_executions', 'idx_executions_workflow_status_date')) {
                    $table->index(['workflow_id', 'status', 'created_at'], 'idx_executions_workflow_status_date');
                }

                // Index for completion date filtering
                if (! $indexExists('supplier_automation_executions', 'idx_executions_completed')) {
                    $table->index('completed_at', 'idx_executions_completed');
                }

                // Index for status filtering
                if (! $indexExists('supplier_automation_executions', 'idx_executions_status')) {
                    $table->index('status', 'idx_executions_status');
                }
            });
        }

        // Indexes for supplier_sync_failures table
        if (Schema::hasTable('supplier_sync_failures')) {
            Schema::table('supplier_sync_failures', function (Blueprint $table) use ($indexExists) {
                // Composite index for sync type + retry count (dashboard filtering)
                if (! $indexExists('supplier_sync_failures', 'idx_sync_failures_type_retry')) {
                    $table->index(['sync_type', 'retry_count'], 'idx_sync_failures_type_retry');
                }

                // Index for last retry timestamp
                if (! $indexExists('supplier_sync_failures', 'idx_sync_failures_last_retry')) {
                    $table->index('last_retry_at', 'idx_sync_failures_last_retry');
                }
            });
        }

        // Indexes for supplier_sources table
        if (Schema::hasTable('supplier_sources')) {
            Schema::table('supplier_sources', function (Blueprint $table) use ($indexExists) {
                // Index for supplier relationship
                if (! $indexExists('supplier_sources', 'idx_sources_supplier')) {
                    $table->index('supplier_id', 'idx_sources_supplier');
                }

                // Composite index for active sources by type
                if (! $indexExists('supplier_sources', 'idx_sources_type_active')) {
                    $table->index(['source_type', 'is_active'], 'idx_sources_type_active');
                }

                // Index for last accessed monitoring
                if (! $indexExists('supplier_sources', 'idx_sources_last_accessed')) {
                    $table->index('last_accessed_at', 'idx_sources_last_accessed');
                }
            });
        }

        // Indexes for supplier_ai_contents table
        if (Schema::hasTable('supplier_ai_contents')) {
            Schema::table('supplier_ai_contents', function (Blueprint $table) use ($indexExists) {
                // Index for supplier relationship
                if (Schema::hasColumn('supplier_ai_contents', 'supplier_id') && ! $indexExists('supplier_ai_contents', 'idx_ai_contents_supplier')) {
                    $table->index('supplier_id', 'idx_ai_contents_supplier');
                }

                // Index for status filtering
                if (Schema::hasColumn('supplier_ai_contents', 'status') && ! $indexExists('supplier_ai_contents', 'idx_ai_contents_status')) {
                    $table->index('status', 'idx_ai_contents_status');
                }

                // Index for generation timestamp
                if (Schema::hasColumn('supplier_ai_contents', 'generated_at') && ! $indexExists('supplier_ai_contents', 'idx_ai_contents_generated')) {
                    $table->index('generated_at', 'idx_ai_contents_generated');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from supplier_products
        if (Schema::hasTable('supplier_products')) {
            Schema::table('supplier_products', function (Blueprint $table) {
                $table->dropIndex('idx_products_erp_active');
                $table->dropIndex('idx_products_code');
                $table->dropIndex('idx_products_barcode');
                $table->dropIndex('idx_products_group');
                $table->dropIndex('idx_products_last_sync');
            });
        }

        // Drop indexes from supplier_product_prices
        if (Schema::hasTable('supplier_product_prices')) {
            Schema::table('supplier_product_prices', function (Blueprint $table) {
                $table->dropIndex('idx_prices_provider_current');
                $table->dropIndex('idx_prices_effective_date');
                $table->dropIndex('idx_prices_last_sync');
                $table->dropIndex('idx_prices_active');
            });
        }

        // Drop indexes from supplier_erp_providers
        if (Schema::hasTable('supplier_erp_providers')) {
            Schema::table('supplier_erp_providers', function (Blueprint $table) {
                $table->dropIndex('idx_erp_providers_erp_id');
                $table->dropIndex('idx_erp_providers_code');
                $table->dropIndex('idx_erp_providers_active');
                $table->dropIndex('idx_erp_providers_last_sync');
            });
        }

        // Drop indexes from supplier_automation_executions
        if (Schema::hasTable('supplier_automation_executions')) {
            Schema::table('supplier_automation_executions', function (Blueprint $table) {
                $table->dropIndex('idx_executions_workflow_status_date');
                $table->dropIndex('idx_executions_completed');
                $table->dropIndex('idx_executions_status');
            });
        }

        // Drop indexes from supplier_sync_failures
        if (Schema::hasTable('supplier_sync_failures')) {
            Schema::table('supplier_sync_failures', function (Blueprint $table) {
                $table->dropIndex('idx_sync_failures_type_retry');
                $table->dropIndex('idx_sync_failures_last_retry');
            });
        }

        // Drop indexes from supplier_sources
        if (Schema::hasTable('supplier_sources')) {
            Schema::table('supplier_sources', function (Blueprint $table) {
                $table->dropIndex('idx_sources_supplier');
                $table->dropIndex('idx_sources_type_active');
                $table->dropIndex('idx_sources_last_accessed');
            });
        }

        // Drop indexes from supplier_ai_contents
        if (Schema::hasTable('supplier_ai_contents')) {
            Schema::table('supplier_ai_contents', function (Blueprint $table) {
                $table->dropIndex('idx_ai_contents_supplier');
                $table->dropIndex('idx_ai_contents_status');
                $table->dropIndex('idx_ai_contents_generated');
            });
        }
    }
};
