<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_variable_langs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_variable_id')->constrained('mail_variables')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('langs')->cascadeOnDelete();
            $table->string('label');
            $table->text('example_value')->nullable();
            $table->timestamps();
            $table->unique(['mail_variable_id', 'lang_id']);
            $table->index('lang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_variable_langs');
    }
};
