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
        Schema::create('helpdesk_conversation_items', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('author_id')->nullable();

            // FK to users table (main database) - will be added separately
            $table->unsignedBigInteger('user_id')->nullable();

            // Message type: message, status_change, assigned, closed, reopened, archived, etc.
            $table->string('type')->default('message');

            // Content
            $table->longText('body')->nullable();
            $table->longText('html_body')->nullable();

            // Attachments
            $table->json('attachment_urls')->nullable();

            // Flags
            $table->boolean('is_internal')->default(false);

            // Additional metadata
            $table->json('metadata')->nullable(); // For system events: old_status, new_status, assigned_to_id, etc.

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('conversation_id');
            $table->index('author_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('is_internal');
            $table->index('created_at');
            $table->index(['conversation_id', 'created_at']);
            $table->index(['conversation_id', 'type']);
            $table->index(['conversation_id', 'is_internal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_conversation_items');
    }
};
