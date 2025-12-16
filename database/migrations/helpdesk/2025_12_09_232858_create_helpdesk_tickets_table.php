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
        Schema::create('helpdesk_tickets', function (Blueprint $table) {
            $table->id();

            // Relationships (cross-database aware)
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('sla_policy_id')->nullable();
            $table->unsignedBigInteger('assignee_id')->nullable(); // FK to users table (main DB)
            $table->unsignedBigInteger('group_id')->nullable();

            // Ticket identifiers
            $table->string('ticket_number')->unique(); // e.g., "TCK-2025-00123"
            $table->string('subject');

            // Priority & urgency
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            // Content
            $table->text('description')->nullable(); // Initial ticket description
            $table->json('custom_fields')->nullable(); // Category-specific fields

            // State management
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_spam')->default(false);

            // SLA tracking
            $table->timestamp('sla_first_response_due_at')->nullable();
            $table->timestamp('sla_next_response_due_at')->nullable();
            $table->timestamp('sla_resolution_due_at')->nullable();
            $table->boolean('sla_first_response_breached')->default(false);
            $table->boolean('sla_resolution_breached')->default(false);
            $table->integer('sla_paused_duration_minutes')->default(0); // Time spent in paused statuses

            // Source tracking
            $table->enum('source', ['manager', 'widget', 'portal', 'api', 'email'])->default('manager');
            $table->string('source_identifier')->nullable(); // API key name, widget domain, etc.

            // Activity timestamps
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('last_customer_message_at')->nullable();
            $table->timestamp('last_agent_message_at')->nullable();

            // Tags & metadata
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable(); // Additional data (IP, browser, etc.)

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('customer_id');
            $table->index('category_id');
            $table->index('status_id');
            $table->index('assignee_id');
            $table->index('group_id');
            $table->index('priority');
            $table->index('is_archived');
            $table->index('created_at');
            $table->index('sla_resolution_due_at');
            $table->index(['status_id', 'created_at']);
            $table->index(['assignee_id', 'created_at']);
            $table->index(['category_id', 'status_id']);

            // Foreign keys (within helpdesk DB)
            $table->foreign('customer_id')->references('id')->on('helpdesk_customers')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('helpdesk_ticket_categories')->onDelete('restrict');
            $table->foreign('status_id')->references('id')->on('helpdesk_ticket_statuses')->onDelete('restrict');
            $table->foreign('sla_policy_id')->references('id')->on('helpdesk_ticket_sla_policies')->onDelete('set null');
            $table->foreign('group_id')->references('id')->on('helpdesk_groups')->onDelete('set null');
            // Note: assignee_id references users in main DB - handled in model, not FK
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_tickets');
    }
};
