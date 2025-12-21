<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Logs when status transitions are performed on documents.
     * This tracks the usage history of document_status_transitions.
     */
    public function up(): void
    {
        Schema::create('document_status_transition_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->comment('Document being transitioned');
            $table->unsignedBigInteger('transition_id')->nullable()->comment('Reference to document_status_transitions');
            $table->unsignedBigInteger('from_status_id')->comment('Status before transition');
            $table->unsignedBigInteger('to_status_id')->comment('Status after transition');
            $table->unsignedBigInteger('performed_by')->nullable()->comment('User who performed transition');
            $table->string('reason')->nullable()->comment('Reason for transition');
            $table->text('metadata')->nullable()->comment('Additional data (JSON)');
            $table->timestamps();

            // Indexes for performance
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('transition_id')->references('id')->on('document_status_transitions')->onDelete('set null');
            $table->foreign('from_status_id')->references('id')->on('document_statuses')->onDelete('restrict');
            $table->foreign('to_status_id')->references('id')->on('document_statuses')->onDelete('restrict');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');

            $table->index('document_id');
            $table->index('transition_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_status_transition_logs');
    }
};
