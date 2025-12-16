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
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('name');
            $table->string('mime_type');
            $table->string('type'); // image, video, document, audio, etc
            $table->bigInteger('size'); // en bytes
            $table->string('url'); // ruta relativa o completa del archivo
            $table->text('alt')->nullable(); // texto alternativo para imágenes
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('metadata')->nullable(); // dimensiones de imagen, duración de video, etc
            $table->string('visibility')->default('private'); // private, public
            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->index('folder_id');
            $table->index('user_id');
            $table->index('mime_type');
            $table->index('type');
            $table->fullText('name'); // búsqueda full text

            // Foreign keys
            $table->foreign('folder_id')->references('id')->on('media_folders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
