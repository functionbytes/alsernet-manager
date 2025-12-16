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
        Schema::connection('helpdesk')->create('helpdesk_ticket_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable(); // Who made the change

            // Change tracking
            $table->string('field_name');           // 'status', 'priority', 'assignee', etc.
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            // Action type
            $table->enum('action_type', [
                'created', 'updated', 'deleted', 'assigned', 'unassigned',
                'status_change', 'priority_change', 'category_change',
                'custom_field_change', 'note_added', 'comment_added',
                'mail_sent', 'mail_received',
            ]);

            // Metadata
            $table->json('metadata')->nullable();

            // Immutable - no updated_at or soft deletes
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->index(['ticket_id', 'created_at']);
            $table->index('user_id');
            $table->index('action_type');
            $table->index('field_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('helpdesk')->dropIfExists('helpdesk_ticket_histories');
    }
};
