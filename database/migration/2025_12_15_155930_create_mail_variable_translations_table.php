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
        Schema::create('mail_variable_translations', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('mail_variable_id')->constrained('mail_variables')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('langs')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            // Unique constraint to ensure one translation per variable per language
            $table->unique(['mail_variable_id', 'lang_id']);

            // Index for faster lookups
            $table->index(['mail_variable_id', 'lang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_variable_translations');
    }
};
