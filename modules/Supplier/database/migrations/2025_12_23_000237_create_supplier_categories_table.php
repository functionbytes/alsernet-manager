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
            $table->char('uid', 26)->unique()->comment('ULID unique identifier');
            $table->unsignedBigInteger('supplier_id')->nullable()->constrained('suppliers')->onDelete('cascade')->comment('Foreign key to suppliers');
            $table->string('name', 255)->comment('Category name');
            $table->string('slug', 255)->unique()->comment('URL-friendly slug');
            $table->text('description')->nullable()->comment('Category description');
            $table->string('code', 100)->nullable()->unique()->comment('Category code for integrations');
            $table->integer('priority')->default(0)->comment('Display priority');
            $table->boolean('is_active')->default(true)->index()->comment('Active status');
            $table->json('metadata')->nullable()->comment('Additional metadata (JSON)');
            $table->timestamps();

            // Indexes for performance
            $table->index(['supplier_id', 'is_active'], 'supplier_active_idx');
            $table->index('slug');
            $table->index('code');
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
