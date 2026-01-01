<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_shops', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique()->index();
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->string('reference')->nullable();
            $table->string('barcode')->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('available')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_shops');
    }
};
