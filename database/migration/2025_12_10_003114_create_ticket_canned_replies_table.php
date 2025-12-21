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
        Schema::connection('helpdesk')->create('helpdesk_ticket_canned_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Creator/owner
            $table->string('title');
            $table->text('body'); // Plain text version
            $table->longText('html_body')->nullable(); // Rich text/HTML version
            $table->string('category')->nullable(); // Internal categorization
            $table->json('tags')->nullable(); // Tags for filtering
            $table->string('shortcut')->nullable()->unique(); // Quick shortcut like /bug, /feature
            $table->boolean('is_global')->default(false); // Available to all agents
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_count')->default(0); // Track usage
            $table->timestamps();
            $table->softDeletes();

            // Note: No FK for user_id (cross-database relationship)
            $table->index('user_id');
            $table->index('category');
            $table->index('is_global');
            $table->index('is_active');
            $table->index('shortcut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('helpdesk')->dropIfExists('helpdesk_ticket_canned_replies');
    }
};
