<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_groups', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // Oracle reference
            $table->unsignedBigInteger('erp_group_id')->unique()->comment('FK to GRUPO_CL.IDGRUPO_CL');

            // Parent relationships
            $table->foreignId('subfamily_id')->constrained('supplier_subfamilies')->onDelete('cascade');
            $table->unsignedBigInteger('erp_subfamily_id')->comment('Oracle IDSUBFAMILIA_CL');

            // Data fields
            $table->string('name', 255)->comment('Group name (descripcion)');
            $table->string('short_name', 100)->nullable()->comment('Short name (desc_corta)');
            $table->string('prefix', 20)->nullable()->comment('Code prefix (prefijo)');
            $table->integer('next_number')->nullable()->comment('Next sequential number (prox_num)');
            $table->boolean('is_active')->default(true)->comment('Active status (estado)');
            $table->boolean('exclude_store_request')->default(false)->comment('excluir_pedir_a_tienda');
            $table->boolean('require_serial_number')->default(false)->comment('pedir_numero_serie');
            $table->string('intrastat', 20)->nullable()->comment('Intrastat code');

            // Sync metadata
            $table->json('metadata')->nullable();
            $table->timestamp('erp_created_at')->nullable();
            $table->timestamp('erp_updated_at')->nullable();
            $table->timestamp('erp_deleted_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('erp_group_id');
            $table->index('subfamily_id');
            $table->index('erp_subfamily_id');
            $table->index('prefix');
            $table->index('is_active');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_groups');
    }
};
