<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('helpdesk_ai_agents', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->enum('provider', ['openai', 'anthropic', 'gemini', 'local'])->default('openai')->index();
            $table->string('model')->default('gpt-4o'); // gpt-4o, claude-3-opus, gemini-pro, etc.

            // Personality & behavior
            $table->longText('personality')->nullable(); // System prompt

            // Status
            $table->enum('status', ['inactive', 'active', 'paused'])->default('inactive')->index();
            $table->timestamp('enabled_at')->nullable();

            // Configuration (JSON)
            $table->json('settings')->nullable(); // API keys, temperature, max_tokens, etc.
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_ai_agents');
    }
};
