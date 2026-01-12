<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_families', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // Oracle reference
            $table->unsignedBigInteger('erp_family_id')->unique()->comment('FK to FAMILIA_CL.IDFAMILIA_CL');

            // Parent relationships
            $table->foreignId('category_id')->constrained('supplier_erp_categories')->onDelete('cascade');
            $table->unsignedBigInteger('erp_category_id')->comment('Oracle IDCATEGORIA_CL');

            // Data fields
            $table->string('name', 255)->comment('Family name (descripcion)');
            $table->string('short_name', 100)->nullable()->comment('Short name (desc_corta)');
            $table->boolean('is_active')->default(true)->comment('Active status (estado)');
            $table->boolean('is_weapons')->default(false)->comment('sonarmas');
            $table->boolean('is_blank_weapons')->default(false)->comment('sonarmasfogueo');
            $table->boolean('is_cartridges')->default(false)->comment('soncartuchos');

            // Sync metadata
            $table->json('metadata')->nullable();
            $table->timestamp('erp_created_at')->nullable();
            $table->timestamp('erp_updated_at')->nullable();
            $table->timestamp('erp_deleted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('erp_family_id');
            $table->index('category_id');
            $table->index('erp_category_id');
            $table->index('is_active');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_families');
    }
};
