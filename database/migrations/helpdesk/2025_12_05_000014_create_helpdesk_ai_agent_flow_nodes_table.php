<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('helpdesk_ai_agent_flow_nodes', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->unsignedBigInteger('flow_id')->index();

            // Node identification
            $table->string('node_id'); // React Flow node ID (e.g., "node-1")
            $table->enum('type', ['input', 'prompt', 'condition', 'action', 'output'])->index();

            // Display
            $table->string('label');

            // Node-specific data
            $table->json('data')->nullable(); // Node configuration (prompt text, conditions, actions, etc.)
            $table->json('position')->nullable(); // x, y coordinates for visual placement

            // Timestamp
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_ai_agent_flow_nodes');
    }
};
