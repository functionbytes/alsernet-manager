<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_status_transition_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('transition_id')->constrained('document_status_transitions')->cascadeOnDelete();
            $table->foreignId('from_status_id')->constrained('document_statuses')->cascadeOnDelete();
            $table->foreignId('to_status_id')->constrained('document_statuses')->cascadeOnDelete();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('document_id');
            $table->index('transition_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_status_transition_logs');
    }
};
