<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('helpdesk_ai_agent_flows', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->unsignedBigInteger('ai_agent_id')->index();

            // Basic info
            $table->string('name')->index();
            $table->text('description')->nullable();

            // Flow configuration
            $table->enum('trigger', ['message', 'intent', 'keyword', 'conversation_start'])->index();
            $table->json('nodes')->nullable(); // React Flow nodes
            $table->json('edges')->nullable(); // React Flow edges

            // Status & Publishing
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();

            // Metadata
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_ai_agent_flows');
    }
};
