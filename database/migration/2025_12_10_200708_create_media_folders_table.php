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
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('color')->default('#5a67d8'); // Color para identificar carpeta
            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->index('parent_id');
            $table->index('user_id');
            $table->index('slug');

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('media_folders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_folders');
    }
};
