<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // Oracle reference
            $table->unsignedBigInteger('erp_product_id')->unique()->comment('FK to ARTICULO.IDARTICULO');

            // Category hierarchy (all levels)
            $table->foreignId('group_id')->nullable()->constrained('supplier_groups')->onDelete('set null');
            $table->unsignedBigInteger('erp_group_id')->nullable()->comment('Oracle IDGRUPO_CL');

            // Supplier relationship (if product is directly linked to a supplier)
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');

            // Basic info
            $table->string('code', 100)->unique()->comment('Product code (codigo)');
            $table->string('barcode', 100)->nullable()->comment('Barcode (codbar)');
            $table->string('name', 500)->comment('Product name (descripcion)');
            $table->string('reference', 100)->nullable()->comment('Supplier reference (referencia)');

            // Brand & Model
            $table->unsignedBigInteger('erp_brand_id')->nullable()->comment('idmarca');
            $table->string('brand_name', 255)->nullable()->comment('Cached brand name');
            $table->unsignedBigInteger('erp_model_id')->nullable()->comment('idmodelo');
            $table->string('model_name', 255)->nullable()->comment('Cached model name');

            // Pricing
            $table->decimal('average_cost', 12, 4)->nullable()->comment('Average cost (preciomedio)');
            $table->decimal('last_purchase_cost', 12, 4)->nullable()->comment('Last purchase cost (precioultcompra)');
            $table->timestamp('last_purchase_date')->nullable()->comment('fprecioultcompra');
            $table->decimal('recommended_price', 12, 4)->nullable()->comment('preciorecomendadoprov');

            // Physical attributes
            $table->decimal('length', 10, 2)->nullable()->comment('largo');
            $table->decimal('width', 10, 2)->nullable()->comment('ancho');
            $table->decimal('height', 10, 2)->nullable()->comment('alto');
            $table->decimal('weight', 10, 3)->nullable()->comment('peso (kg)');
            $table->integer('units_per_box')->nullable()->comment('unidadesporcaja');

            // Stock & availability
            $table->integer('max_serve')->nullable()->comment('maxservir');
            $table->timestamp('available_from')->nullable()->comment('fdisponibilidad');
            $table->boolean('allow_reservation')->default(false)->comment('permitir_reservar');
            $table->boolean('auto_availability')->default(false)->comment('disponibilidad_automatica');

            // Status flags
            $table->boolean('is_active')->default(true)->comment('estado');
            $table->boolean('is_external')->default(false)->comment('externo');
            $table->boolean('is_second_hand')->default(false)->comment('segundamano');
            $table->boolean('requires_serial')->default(false)->comment('pedir_numero_serie');
            $table->boolean('centralized_purchase')->default(false)->comment('compra_centralizada');

            // Image
            $table->string('image_path', 500)->nullable()->comment('rutaimagen');

            // Notes
            $table->text('notes')->nullable()->comment('observaciones');
            $table->text('purchase_notes')->nullable()->comment('observaciones_compras');

            // Sync metadata
            $table->json('metadata')->nullable()->comment('Additional Oracle fields');
            $table->timestamp('erp_created_at')->nullable();
            $table->timestamp('erp_updated_at')->nullable();
            $table->timestamp('erp_deleted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('erp_product_id');
            $table->index('code');
            $table->index('barcode');
            $table->index('group_id');
            $table->index('erp_group_id');
            $table->index('supplier_id');
            $table->index('erp_brand_id');
            $table->index('erp_model_id');
            $table->index('is_active');
            $table->index('is_external');
            $table->index('centralized_purchase');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
