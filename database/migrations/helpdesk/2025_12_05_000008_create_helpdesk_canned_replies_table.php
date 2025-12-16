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
        Schema::create('helpdesk_canned_replies', function (Blueprint $table) {
            $table->id();

            // Owner (FK to users table in main database)
            $table->unsignedBigInteger('user_id')->nullable();

            // Content
            $table->string('title');
            $table->longText('body');
            $table->longText('html_body')->nullable();

            // Organization
            $table->string('category')->nullable();
            $table->json('tags')->nullable();

            // Quick access
            $table->string('shortcut')->nullable()->unique(); // e.g., "thanks", "payment"

            // Visibility
            $table->boolean('is_global')->default(false); // Available to all agents

            // Analytics
            $table->unsignedInteger('usage_count')->default(0);

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('category');
            $table->index('is_global');
            $table->index('shortcut');
            $table->index('usage_count');
            $table->index('created_at');
            $table->fullText(['title', 'body']); // MySQL full-text search
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_canned_replies');
    }
};
