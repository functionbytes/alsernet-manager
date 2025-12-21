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
        Schema::table('document_product_blockades', function (Blueprint $table) {
            // Drop existing indexes first
            $table->dropUnique('unique_product_blockade');
            $table->dropIndex('idx_product_lookup');

            // Rename source_id to source_id
            $table->renameColumn('source_id', 'source_id');

            // Drop article_id
            $table->dropColumn('source_id');

            // Add new columns (nullable because simple products use product_id, combinations use product_attribute_id)
            $table->unsignedBigInteger('product_id')->nullable()->after('source_id');
            $table->unsignedBigInteger('product_attribute_id')->nullable()->after('product_id');

            // Add new indexes
            $table->unique(['source_id', 'product_id', 'blockade_type'], 'unique_product_blockade');
            $table->unique(['source_id', 'product_attribute_id', 'blockade_type'], 'unique_combination_blockade');
            $table->index(['product_id'], 'idx_product');
            $table->index(['product_attribute_id'], 'idx_product_attribute');
            $table->index(['source_id'], 'idx_origen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_product_blockades', function (Blueprint $table) {
            // Drop new indexes
            $table->dropUnique('unique_product_blockade');
            $table->dropUnique('unique_combination_blockade');
            $table->dropIndex('idx_product');
            $table->dropIndex('idx_product_attribute');
            $table->dropIndex('idx_origen');

            // Drop new columns
            $table->dropColumn(['product_id', 'product_attribute_id']);

            // Add back article_id
            $table->unsignedBigInteger('article_id')->after('source_id');

            // Rename source_id back to source_id
            $table->renameColumn('source_id', 'source_id');

            // Restore old indexes
            $table->unique(['source_id', 'article_id', 'blockade_type'], 'unique_product_blockade');
            $table->index(['source_id', 'article_id'], 'idx_product_lookup');
        });
    }
};
