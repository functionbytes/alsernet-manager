<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_layout_langs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('layout_id')->constrained('mail_layouts')->onDelete('cascade');
            $table->foreignId('lang_id')->constrained('langs')->onDelete('cascade');
            $table->string('subject')->nullable();
            $table->longText('content');
            $table->timestamps();

            $table->unique(['layout_id', 'lang_id']);
            $table->index('lang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_layout_langs');
    }
};
