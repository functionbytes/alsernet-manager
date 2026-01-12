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
        Schema::create('supplier_sports', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');

            // Oracle reference
            $table->unsignedBigInteger('erp_sport_id')->unique()->comment('FK to DEPORTE_CL.IDDEPORTE_CL');

            // Data fields (mirroring Oracle)
            $table->string('code', 50)->nullable()->comment('Sport code (prefijo)');
            $table->string('name', 255)->comment('Sport name (descripcion)');
            $table->string('short_name', 100)->nullable()->comment('Short name (desc_corta)');
            $table->boolean('is_active')->default(true)->comment('Active status (estado)');

            // Sync metadata
            $table->json('metadata')->nullable()->comment('Additional metadata from Oracle');
            $table->timestamp('erp_created_at')->nullable()->comment('Oracle fcreacion');
            $table->timestamp('erp_updated_at')->nullable()->comment('Oracle fmodificacion');
            $table->timestamp('erp_deleted_at')->nullable()->comment('Oracle fbaja (soft delete)');
            $table->timestamp('last_synced_at')->nullable()->comment('Last sync timestamp');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('erp_sport_id');
            $table->index('code');
            $table->index('is_active');
            $table->index('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sports');
    }
};
