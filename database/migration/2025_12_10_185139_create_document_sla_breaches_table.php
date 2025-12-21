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
        Schema::create('document_sla_breaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('request_documents')->cascadeOnDelete();
            $table->foreignId('sla_policy_id')->constrained('document_sla_policies')->restrictOnDelete();
            $table->enum('breach_type', ['upload_request', 'review', 'approval']); // Which SLA was breached
            $table->integer('minutes_over')->default(0); // How many minutes over the target
            $table->boolean('escalated')->default(false);
            $table->dateTime('escalated_at')->nullable();
            $table->boolean('resolved')->default(false);
            $table->dateTime('resolved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('document_id');
            $table->index('sla_policy_id');
            $table->index('breach_type');
            $table->index('resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_sla_breaches');
    }
};
