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
            $table->string('name', 255)->comment('Supplier name');
            $table->string('code', 50)->unique()->comment('Internal supplier code');
            $table->integer('erp_id')->nullable()->comment('Supplier ID in ERP/Management system');
            $table->integer('supplier_id')->nullable()->comment('FK to aalv_supplier.id_supplier (PrestaShop)');
            $table->string('website', 500)->nullable()->comment('Main supplier website URL');
            $table->text('description')->nullable()->comment('Supplier description');
            $table->string('contact_email', 255)->nullable()->comment('Contact email address');
            $table->string('contact_phone', 50)->nullable()->comment('Contact phone number');
            $table->integer('priority')->default(10)->comment('Supplier priority');
            $table->string('content_type', 50)->default('inventaries')->comment('Content type for this supplier');
            $table->integer('api_rate_limit')->default(60)->comment('API rate limit');
            $table->string('api_rate_period', 20)->default('minute')->comment('API rate limit period');
            $table->json('metadata')->nullable()->comment('Metadata storage');
            $table->json('config')->nullable()->comment('Configuration storage');
            $table->timestamp('last_sync_at')->nullable()->comment('Last sync timestamp');
            $table->boolean('is_active')->default(true)->comment('Active/inactive supplier');
            $table->timestamps();

            $table->index('erp_id');
            $table->index('supplier_id');
            $table->index('is_active');
            $table->index('priority');
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
