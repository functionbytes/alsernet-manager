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
        Schema::create('helpdesk_ticket_items', function (Blueprint $table) {
            $table->id();

            // Parent ticket
            $table->unsignedBigInteger('ticket_id');

            // Author (customer or agent)
            $table->unsignedBigInteger('author_id')->nullable(); // FK to helpdesk_customers
            $table->unsignedBigInteger('user_id')->nullable(); // FK to users (main DB)

            // Content type
            $table->enum('type', [
                'message',           // Regular message
                'internal_note',     // Private note between agents
                'status_change',     // System event: status changed
                'assigned',          // System event: ticket assigned
                'unassigned',        // System event: unassigned
                'priority_changed',  // System event: priority updated
                'category_changed',  // System event: category changed
                'sla_warning',       // System event: SLA approaching breach
                'sla_breach',        // System event: SLA breached
                'attachment_added',  // System event: file attached
                'customer_replied',  // System event: customer responded
            ])->default('message');

            // Content
            $table->text('body')->nullable();
            $table->text('html_body')->nullable();
            $table->json('attachment_urls')->nullable();

            // Flags
            $table->boolean('is_internal')->default(false);

            // Metadata
            $table->json('metadata')->nullable(); // Event-specific data (old/new values, etc.)

            $table->timestamps();
            $table->softDeletes();

            $table->index('ticket_id');
            $table->index('author_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');

            $table->foreign('ticket_id')->references('id')->on('helpdesk_tickets')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('helpdesk_customers')->onDelete('set null');
            // user_id FK handled in model (cross-database)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_items');
    }
};
