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
        Schema::create('document_requirement_langs', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 36)->unique();
            $table->unsignedBigInteger('document_requirement_id');
            $table->unsignedBigInteger('lang_id');
            $table->string('name');
            $table->text('help_text')->nullable();
            $table->timestamps();

            // Foreign keys with custom names to avoid length issues
            $table->foreign('document_requirement_id', 'doc_req_trans_req_fk')
                ->references('id')->on('document_requirements')->cascadeOnDelete();
            $table->foreign('lang_id', 'doc_req_trans_lang_fk')
                ->references('id')->on('langs')->cascadeOnDelete();

            // Unique constraint: one translation per requirement per language
            $table->unique(['document_requirement_id', 'lang_id'], 'doc_req_trans_unique');

            // Index for faster lookups
            $table->index(['document_requirement_id', 'lang_id'], 'doc_req_trans_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requirement_langs');
    }
};
