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
        Schema::dropIfExists('document_requirement_translations');
        Schema::dropIfExists('document_type_translations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate document_type_translations table
        Schema::create('document_type_translations', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('document_type_id');
            $table->foreignId('lang_id')->constrained('langs')->onDelete('cascade');
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->unique(['document_type_id', 'lang_id'], 'doc_type_trans_unique');
            $table->index('lang_id');

            $table->foreign('document_type_id', 'doc_type_trans_type_fk')
                ->references('id')
                ->on('document_types')
                ->onDelete('cascade');
        });

        // Recreate document_requirement_translations table
        Schema::create('document_requirement_translations', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('document_requirement_id');
            $table->foreignId('lang_id')->constrained('langs')->onDelete('cascade');
            $table->string('name');
            $table->text('help_text')->nullable();
            $table->timestamps();

            $table->unique(['document_requirement_id', 'lang_id'], 'doc_req_trans_unique');
            $table->index('lang_id');

            $table->foreign('document_requirement_id', 'doc_req_trans_req_fk')
                ->references('id')
                ->on('document_requirements')
                ->onDelete('cascade');
        });
    }
};
