<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_product_blockades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->nullable()->constrained('document_sources')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_attribute_id')->nullable();
            $table->foreignId('document_type_id')->nullable()->constrained('document_types')->nullOnDelete();
            $table->timestamps();

            $table->index('product_id');
            $table->index('product_attribute_id');
            $table->index('document_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_product_blockades');
    }
};
