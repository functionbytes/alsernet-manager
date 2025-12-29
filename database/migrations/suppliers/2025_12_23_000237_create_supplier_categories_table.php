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
        Schema::create('supplier_categories', function (Blueprint $table) {
            $table->id();
            $table->char('uid', 26)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Composite unique index to prevent duplicate assignments
            $table->unique(['supplier_id', 'category_id'], 'supplier_category_unique');

            // Index for active filtering
            $table->index(['supplier_id', 'is_active'], 'supplier_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_categories');
    }
};
