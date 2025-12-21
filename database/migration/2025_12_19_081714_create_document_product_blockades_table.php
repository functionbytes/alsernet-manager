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
        Schema::create('document_product_blockades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id'); // Previously id_origen
            $table->unsignedBigInteger('article_id'); // Previously id_articulo
            $table->string('blockade_type', 50); // 'dni', 'escopeta', 'rifle', 'corta'
            $table->timestamps();

            // Unique index to prevent duplicates by source_id + article_id + blockade_type combination
            $table->unique(['source_id', 'article_id', 'blockade_type'], 'unique_product_blockade');

            // Indexes for fast lookups
            $table->index(['source_id', 'article_id'], 'idx_product_lookup');
            $table->index('blockade_type', 'idx_blockade_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_product_blockades');
    }
};
