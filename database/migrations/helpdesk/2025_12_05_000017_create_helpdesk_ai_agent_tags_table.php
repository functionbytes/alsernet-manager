<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('helpdesk_ai_agent_tags', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#90bb13'); // Hex color for UI display
            $table->string('icon')->nullable(); // Tabler icon class

            // Usage & Functionality
            $table->text('system_prompt_addition')->nullable(); // Additional instructions when tag is active
            $table->integer('priority')->default(0)->index(); // Higher priority tags take precedence

            // Status
            $table->boolean('is_active')->default(true)->index();

            // Metadata
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'priority']);
        });

        // Pivot table for many-to-many relationship between conversations and tags
        Schema::connection('mysql')->create('helpdesk_conversation_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->unsignedBigInteger('tag_id')->index();
            $table->timestamp('tagged_at')->useCurrent();

            $table->unique(['conversation_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_conversation_tag');
        Schema::connection('mysql')->dropIfExists('helpdesk_ai_agent_tags');
    }
};
