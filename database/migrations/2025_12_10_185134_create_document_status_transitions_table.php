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
        Schema::create('document_status_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_status_id')->constrained('document_statuses')->cascadeOnDelete();
            $table->foreignId('to_status_id')->constrained('document_statuses')->cascadeOnDelete();
            $table->string('permission')->nullable(); // Required permission to perform transition
            $table->boolean('requires_all_documents_uploaded')->default(false);
            $table->integer('auto_transition_after_days')->nullable(); // Auto-transition after N days
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['from_status_id', 'to_status_id']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_status_transitions');
    }
};
