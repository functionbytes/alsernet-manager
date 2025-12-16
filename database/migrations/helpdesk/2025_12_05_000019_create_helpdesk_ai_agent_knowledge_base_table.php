<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->create('helpdesk_ai_agent_knowledge_base', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->unsignedBigInteger('ai_agent_id')->index();

            // Content
            $table->string('title')->index();
            $table->longText('content');
            $table->enum('type', ['document', 'faq', 'article', 'manual', 'url'])->default('document')->index();

            // Source information
            $table->string('source_url')->nullable();
            $table->string('source_type')->nullable(); // 'manual', 'import', 'scrape', 'help_center'
            $table->unsignedBigInteger('source_id')->nullable(); // Reference to help center article, etc.

            // Vector embeddings for RAG
            $table->text('embedding')->nullable(); // JSON array of embedding vector
            $table->string('embedding_model')->nullable(); // Model used for embedding

            // Metadata & Search
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable(); // Categorization tags
            $table->text('summary')->nullable(); // Auto-generated summary

            // Usage tracking
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true)->index();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['ai_agent_id', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index(['source_type', 'source_id']);
            $table->fullText(['title', 'content']); // Full-text search
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_ai_agent_knowledge_base');
    }
};
