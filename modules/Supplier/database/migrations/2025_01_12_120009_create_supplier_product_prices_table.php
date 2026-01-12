<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_product_prices', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // Oracle reference
            $table->unsignedBigInteger('erp_price_id')->unique()->comment('FK to ARTIPROV_TARIFAPRO.IDARTIPROV_TARIFAPRO');

            // Relationships
            $table->foreignId('provider_product_id')->constrained('supplier_provider_products')->onDelete('cascade');
            $table->unsignedBigInteger('erp_artiprov_id')->comment('Oracle IDARTIPROV');

            // Price data
            $table->date('effective_date')->comment('Effective date (fecha)');
            $table->decimal('cost', 12, 4)->comment('Cost price (pcosto)');
            $table->decimal('discount1', 5, 2)->nullable()->comment('Discount 1 (dto1)');
            $table->decimal('discount2', 5, 2)->nullable()->comment('Discount 2 (dto2)');
            $table->decimal('final_cost', 12, 4)->comment('Final cost after discounts');

            // Status
            $table->boolean('is_active')->default(true)->comment('estado');
            $table->boolean('is_current')->default(false)->comment('Is this the current active price');

            // Sync metadata
            $table->json('metadata')->nullable();
            $table->timestamp('erp_created_at')->nullable();
            $table->timestamp('erp_updated_at')->nullable();
            $table->timestamp('erp_deleted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('erp_price_id');
            $table->index('provider_product_id');
            $table->index('erp_artiprov_id');
            $table->index('effective_date');
            $table->index('is_active');
            $table->index('is_current');
            $table->index(['provider_product_id', 'effective_date'], 'provider_product_date_idx');
            $table->index(['provider_product_id', 'is_current'], 'provider_product_current_idx');
            $table->index('last_synced_at');

            // Unique constraint: one active price per provider_product + date
            $table->unique(['provider_product_id', 'effective_date', 'is_active'], 'unique_active_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_product_prices');
    }
};
