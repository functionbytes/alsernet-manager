<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates a centralized table to define validation conditions that can be
     * reused across multiple document types. Each condition maps a key (like
     * 'is_weapon' or 'is_dni_only') to an array of sale_types from document_product_blockades.
     */
    public function up(): void
    {
        Schema::create('document_validation_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 36)->unique();
            $table->string('key', 50)->unique()->comment('Condition key used in validation_stages (e.g., is_weapon, is_dni_only)');
            $table->string('name')->comment('Display name for this condition');
            $table->text('description')->nullable()->comment('Description of what this condition represents');
            $table->json('sale_types')->comment('Array of sale_types from document_product_blockades that match this condition');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_validation_conditions');
    }
};
