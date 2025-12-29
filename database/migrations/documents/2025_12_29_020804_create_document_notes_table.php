<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->longText('content');
            $table->boolean('is_internal')->default(true);
            $table->timestamps();

            $table->index('document_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_notes');
    }
};
