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
        Schema::create('supplier_sync_failures', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique();
            $table->string('sync_type', 50); // 'price', 'provider', 'product'
            $table->unsignedBigInteger('supplier_id'); // ID en tabla Supplier (SupplierProductPrice, SupplierErpProvider, SupplierProduct)
            $table->unsignedBigInteger('erp_id')->nullable(); // ID en Oracle (ArtiprovTarifapro, Proveedor, Articulo)
            $table->json('changed_data'); // Campos que intentaron sincronizarse
            $table->text('error_message');
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamps();

            $table->index(['sync_type', 'supplier_id']);
            $table->index('retry_count');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sync_failures');
    }
};
