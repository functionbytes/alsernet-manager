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
        Schema::connection('helpdesk')->create('helpdesk_ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->onDelete('cascade');

            // Dual author system (XOR: user_id OR author_id)
            $table->unsignedBigInteger('user_id')->nullable();      // Agent (mysql.users)
            $table->unsignedBigInteger('author_id')->nullable();    // Customer (helpdesk.customers)

            // Content
            $table->text('body')->nullable();
            $table->text('html_body')->nullable();
            $table->boolean('is_internal')->default(false);

            // Attachments
            $table->json('attachment_urls')->nullable();

            // Edit tracking
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->string('edit_reason')->nullable();

            // @mentions
            $table->json('mentioned_user_ids')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['ticket_id', 'created_at']);
            $table->index('user_id');
            $table->index('author_id');
            $table->index('is_internal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('helpdesk')->dropIfExists('helpdesk_ticket_comments');
    }
};
