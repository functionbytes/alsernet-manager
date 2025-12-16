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
        Schema::create('helpdesk_page_visits', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('customer_id')
                  ->constrained('helpdesk_customers')
                  ->onDelete('cascade');

            $table->foreignId('session_id')
                  ->nullable()
                  ->constrained('helpdesk_customer_sessions')
                  ->onDelete('set null');

            // Page information
            $table->string('page_url');
            $table->string('page_title')->nullable();
            $table->string('referrer')->nullable();

            // Engagement metrics
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->unsignedTinyInteger('scroll_depth')->nullable(); // 0-100 percentage

            // Timestamps
            $table->timestamp('visited_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('customer_id');
            $table->index('session_id');
            $table->index('visited_at');
            $table->index('created_at');
            $table->fullText(['page_url', 'page_title']); // MySQL full-text search
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_page_visits');
    }
};
