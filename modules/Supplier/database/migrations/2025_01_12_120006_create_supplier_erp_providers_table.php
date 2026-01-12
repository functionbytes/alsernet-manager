<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_erp_providers', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // Oracle reference
            $table->unsignedBigInteger('erp_provider_id')->unique()->comment('FK to PROVEEDOR.IDPROVEEDOR');

            // Basic info
            $table->string('code', 50)->nullable()->comment('Provider code (codcliente)');
            $table->string('name', 255)->comment('Provider name (nombre)');
            $table->string('tax_id', 50)->nullable()->comment('Tax ID / CIF');
            $table->string('contact_person', 255)->nullable()->comment('Contact person (percontacto)');

            // Contact info
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable()->comment('telefono1');
            $table->string('phone_alt', 50)->nullable()->comment('telefono2');
            $table->string('fax', 50)->nullable();
            $table->string('website', 500)->nullable()->comment('web');

            // Address
            $table->string('street', 255)->nullable()->comment('calle');
            $table->string('street_number', 20)->nullable()->comment('num');
            $table->string('city', 100)->nullable()->comment('localidad');
            $table->string('postal_code', 20)->nullable()->comment('cp');
            $table->string('province', 100)->nullable()->comment('provincia');
            $table->string('country', 100)->nullable()->comment('pais');
            $table->unsignedBigInteger('erp_country_id')->nullable()->comment('idpais');

            // Business terms
            $table->decimal('shipping_cost', 10, 2)->nullable()->comment('portes');
            $table->decimal('discount_percent', 5, 2)->nullable()->comment('dto');
            $table->integer('partial_delivery_days')->nullable()->comment('dias_servir_parcial');
            $table->decimal('partial_delivery_percent', 5, 2)->nullable()->comment('porcentaje_servir_parcial');

            // Status
            $table->boolean('is_active')->default(true)->comment('estado');
            $table->boolean('is_visible')->default(true)->comment('visible');
            $table->boolean('is_creditor')->default(false)->comment('acreedor');

            // Notes
            $table->text('notes')->nullable()->comment('observaciones');
            $table->text('shipping_notes')->nullable()->comment('observacionportes');

            // Link to Supplier module (optional)
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null')->comment('FK to suppliers table');

            // Sync metadata
            $table->json('metadata')->nullable();
            $table->timestamp('erp_created_at')->nullable();
            $table->timestamp('erp_updated_at')->nullable();
            $table->timestamp('erp_deleted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('erp_provider_id');
            $table->index('code');
            $table->index('tax_id');
            $table->index('email');
            $table->index('is_active');
            $table->index('supplier_id');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_erp_providers');
    }
};
