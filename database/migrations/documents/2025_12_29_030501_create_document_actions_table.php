<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('action_type');
            $table->string('action_name');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->string('performed_by_type')->default('system');
            $table->timestamps();

            $table->index('document_id');
            $table->index('action_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_actions');
    }
};
