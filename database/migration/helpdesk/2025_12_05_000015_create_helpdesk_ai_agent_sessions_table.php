<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('helpdesk_ai_agent_sessions', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->unsignedBigInteger('ai_agent_id')->index();
            $table->unsignedBigInteger('conversation_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();

            // Status & lifecycle
            $table->enum('status', ['active', 'completed', 'failed', 'paused'])->default('active')->index();

            // Session data
            $table->json('context')->nullable(); // Session variables/state
            $table->json('metadata')->nullable(); // Additional data (tokens used, cost, etc.)

            // Timing
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_ai_agent_sessions');
    }
};
