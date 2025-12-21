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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');
            $table->string('label', 255)->comment('Supplier name');
            $table->string('code', 50)->unique()->comment('Internal supplier code');
            $table->integer('erp_id')->nullable()->comment('Supplier ID in ERP/Management system');
            $table->integer('supplier_id')->nullable()->comment('FK to aalv_supplier.id_supplier (PrestaShop)');
            $table->string('website_url', 500)->nullable()->comment('Main supplier website URL');
            $table->boolean('is_active')->default(true)->comment('Active/inactive supplier');
            $table->timestamps();

            $table->index('erp_id');
            $table->index('supplier_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
