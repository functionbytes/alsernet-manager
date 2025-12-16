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
        Schema::connection('helpdesk')->create('helpdesk_ticket_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->onDelete('cascade');
            $table->foreignId('ticket_comment_id')->nullable()->constrained('helpdesk_ticket_comments')->onDelete('set null');

            // Direction
            $table->enum('direction', ['inbound', 'outbound']);

            // Email headers
            $table->string('message_id')->unique();  // For threading
            $table->string('in_reply_to')->nullable();
            $table->text('references')->nullable();  // Comma-separated message IDs

            // Addresses
            $table->string('from');
            $table->string('to');
            $table->text('cc')->nullable();
            $table->text('bcc')->nullable();
            $table->string('subject');

            // Content
            $table->text('body_html')->nullable();
            $table->text('body_text')->nullable();

            // Attachments
            $table->json('attachments')->nullable(); // [{filename, url, size, mime}]

            // Headers (full raw headers)
            $table->json('headers')->nullable();

            // Delivery tracking
            $table->enum('status', ['pending', 'sent', 'delivered', 'bounced', 'failed'])->default('pending');
            $table->text('delivery_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Raw email (for debugging)
            $table->longText('raw_email')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['ticket_id', 'created_at']);
            $table->index('message_id');
            $table->index('in_reply_to');
            $table->index('direction');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('helpdesk')->dropIfExists('helpdesk_ticket_mails');
    }
};
