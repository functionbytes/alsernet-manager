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
        Schema::create('email_template_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('langs')->cascadeOnDelete();
            $table->string('subject')->nullable();
            $table->string('preheader')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();

            // Unique constraint to ensure one translation per template per language
            $table->unique(['email_template_id', 'lang_id']);

            // Index for faster lookups
            $table->index(['email_template_id', 'lang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_template_translations');
    }
};
