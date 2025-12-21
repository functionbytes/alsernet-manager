<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('helpdesk_ai_agent_tools', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->unsignedBigInteger('ai_agent_id')->index();

            // Basic info
            $table->string('name')->index(); // Function/tool name
            $table->text('description'); // What the tool does

            // Tool Configuration
            $table->enum('type', ['function', 'api', 'database', 'custom'])->default('function')->index();
            $table->json('parameters')->nullable(); // JSON schema for function parameters
            $table->text('implementation')->nullable(); // Code/endpoint for execution

            // Authentication & Security
            $table->json('auth_config')->nullable(); // API keys, tokens, etc.
            $table->boolean('requires_approval')->default(false); // User approval before execution

            // Usage tracking
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true)->index();

            // Metadata
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['ai_agent_id', 'is_active']);
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_ai_agent_tools');
    }
};
