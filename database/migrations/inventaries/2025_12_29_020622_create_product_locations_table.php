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
        Schema::create('product_locations', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('product_id')->constrained('inventaries')->onDelete('cascade');
            $table->foreignId('location_id')->nullable();
            $table->foreignId('shop_id')->nullable();
            $table->integer('kardex')->default(0);
            $table->integer('management')->default(0);
            $table->integer('count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_locations');
    }
};
