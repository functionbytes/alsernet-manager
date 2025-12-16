<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('helpdesk_ai_agent_session_messages', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->unsignedBigInteger('session_id')->index();

            // Message content
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->longText('content');

            // Metadata
            $table->json('metadata')->nullable(); // tokens used, model, temperature, etc.

            // Timestamp (creation only, immutable)
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_ai_agent_session_messages');
    }
};
