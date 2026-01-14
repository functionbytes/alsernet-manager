<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_erp_categories', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // Oracle reference
            $table->unsignedBigInteger('erp_category_id')->unique()->comment('FK to CATEGORIA_CL.IDCATEGORIA_CL');

            // Parent relationship
            $table->foreignId('sport_id')->constrained('supplier_sports')->onDelete('cascade')->comment('FK to supplier_sports');
            $table->unsignedBigInteger('erp_sport_id')->comment('Oracle IDDEPORTE_CL');

            // Data fields
            $table->string('name', 255)->comment('Category name (descripcion)');
            $table->string('short_name', 100)->nullable()->comment('Short name (desc_corta)');
            $table->boolean('is_active')->default(true)->comment('Active status (estado)');
            $table->boolean('show_in_stock_report')->default(false)->comment('aparece_inf_stock');

            // Sync metadata
            $table->json('metadata')->nullable()->comment('Additional metadata from Oracle');
            $table->timestamp('erp_created_at')->nullable();
            $table->timestamp('erp_updated_at')->nullable();
            $table->timestamp('erp_deleted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('erp_category_id');
            $table->index('sport_id');
            $table->index('erp_sport_id');
            $table->index('is_active');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_erp_categories');
    }
};
