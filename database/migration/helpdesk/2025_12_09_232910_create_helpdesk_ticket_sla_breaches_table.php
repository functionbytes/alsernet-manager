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
        Schema::create('helpdesk_ticket_sla_breaches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('sla_policy_id');

            $table->enum('breach_type', ['first_response', 'next_response', 'resolution']);
            $table->timestamp('due_at');
            $table->timestamp('breached_at');
            $table->integer('breach_duration_minutes'); // How long past due

            // Resolution tracking
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();

            $table->timestamps();

            $table->index('ticket_id');
            $table->index('breach_type');
            $table->index('resolved');

            $table->foreign('ticket_id')->references('id')->on('helpdesk_tickets')->onDelete('cascade');
            $table->foreign('sla_policy_id')->references('id')->on('helpdesk_ticket_sla_policies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_sla_breaches');
    }
};
