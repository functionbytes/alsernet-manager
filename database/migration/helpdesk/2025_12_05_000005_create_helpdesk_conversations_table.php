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
        Schema::create('helpdesk_conversations', function (Blueprint $table) {
            $table->id();

            // FK to customers - will reference within same DB
            $table->unsignedBigInteger('customer_id');

            // FK to conversation_statuses - will reference within same DB
            $table->unsignedBigInteger('status_id');

            // FK to users table (main database) - will be added separately
            $table->unsignedBigInteger('assignee_id')->nullable();

            // Conversation details
            $table->string('subject')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            // State management
            $table->boolean('is_archived')->default(false);

            // Timestamps
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('customer_id');
            $table->index('status_id');
            $table->index('assignee_id');
            $table->index('is_archived');
            $table->index('created_at');
            $table->index(['customer_id', 'created_at']);
            $table->index(['status_id', 'created_at']);
            $table->index(['assignee_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_conversations');
    }
};
