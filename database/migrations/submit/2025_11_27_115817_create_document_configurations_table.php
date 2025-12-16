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
        Schema::create('request_document_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('document_type')->unique(); // corta, rifle, escopeta, dni, general
            $table->json('required_documents'); // Array de documentos requeridos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_document_configurations');
    }
};
