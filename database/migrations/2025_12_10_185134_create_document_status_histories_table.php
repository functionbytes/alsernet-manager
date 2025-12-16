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
        Schema::create('document_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('request_documents')->cascadeOnDelete();
            $table->foreignId('from_status_id')->nullable()->constrained('document_statuses')->nullOnDelete();
            $table->foreignId('to_status_id')->constrained('document_statuses')->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable(); // Store additional info about the change
            $table->timestamps();

            $table->index('document_id');
            $table->index('to_status_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_status_histories');
    }
};
