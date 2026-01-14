<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_subfamilies', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // Oracle reference
            $table->unsignedBigInteger('erp_subfamily_id')->unique()->comment('FK to SUBFAMILIA_CL.IDSUBFAMILIA_CL');

            // Parent relationships
            $table->foreignId('family_id')->constrained('supplier_families')->onDelete('cascade');
            $table->unsignedBigInteger('erp_family_id')->comment('Oracle IDFAMILIA_CL');

            // Data fields
            $table->string('name', 255)->comment('Subfamily name (descripcion)');
            $table->string('short_name', 100)->nullable()->comment('Short name (desc_corta)');
            $table->boolean('is_active')->default(true)->comment('Active status (estado)');
            $table->boolean('is_ammunition')->default(false)->comment('escartucheria');
            $table->boolean('is_metal_ammunition')->default(false)->comment('esmunicionmetalica');
            $table->boolean('show_lots')->default(false)->comment('mostrarlotes');

            // Sync metadata
            $table->json('metadata')->nullable();
            $table->timestamp('erp_created_at')->nullable();
            $table->timestamp('erp_updated_at')->nullable();
            $table->timestamp('erp_deleted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('erp_subfamily_id');
            $table->index('family_id');
            $table->index('erp_family_id');
            $table->index('is_active');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_subfamilies');
    }
};
