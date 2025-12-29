<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->string('product_reference')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();

            $table->index('document_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_products');
    }
};
