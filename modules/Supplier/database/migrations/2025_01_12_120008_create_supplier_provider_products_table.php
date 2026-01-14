<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_provider_products', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // Oracle reference
            $table->unsignedBigInteger('erp_artiprov_id')->unique()->comment('FK to ARTIPROV.IDARTIPROV');

            // Relationships
            $table->foreignId('provider_id')->constrained('supplier_erp_providers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('supplier_products')->onDelete('cascade');
            $table->unsignedBigInteger('erp_provider_id')->comment('Oracle IDPROVEEDOR');
            $table->unsignedBigInteger('erp_product_id')->comment('Oracle IDARTICULO');

            // Provider-specific data
            $table->string('provider_code', 100)->nullable()->comment('Provider product code (codigo)');
            $table->string('provider_code_alt', 100)->nullable()->comment('Alternate code (codigo2)');
            $table->string('provider_name', 500)->nullable()->comment('Provider product name (descripcion)');
            $table->string('ean13', 50)->nullable()->comment('EAN-13 barcode');
            $table->string('upc', 50)->nullable()->comment('UPC barcode');

            // Current pricing (from latest ARTIPROV_TARIFAPRO or ARTIPROV)
            $table->decimal('current_cost', 12, 4)->nullable()->comment('Current cost (pcosto)');
            $table->decimal('discount1', 5, 2)->nullable()->comment('Discount 1 (dto1)');
            $table->decimal('discount2', 5, 2)->nullable()->comment('Discount 2 (dto2)');
            $table->decimal('final_cost', 12, 4)->nullable()->comment('Calculated final cost after discounts');
            $table->timestamp('price_updated_at')->nullable()->comment('When price was last updated');

            // Status
            $table->boolean('is_active')->default(true)->comment('estado');
            $table->boolean('is_default')->default(false)->comment('pordefecto - Default supplier for this product');

            // Sync metadata
            $table->json('metadata')->nullable();
            $table->timestamp('erp_created_at')->nullable();
            $table->timestamp('erp_updated_at')->nullable();
            $table->timestamp('erp_deleted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('erp_artiprov_id');
            $table->index('provider_id');
            $table->index('product_id');
            $table->index(['provider_id', 'product_id'], 'provider_product_idx');
            $table->index('erp_provider_id');
            $table->index('erp_product_id');
            $table->index('provider_code');
            $table->index('ean13');
            $table->index('upc');
            $table->index('is_active');
            $table->index('is_default');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_provider_products');
    }
};
