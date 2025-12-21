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
        Schema::connection('mysql')->create('helpdesk_campaign_templates', function (Blueprint $table) {
            $table->id();

            // Foreign key
            $table->unsignedBigInteger('campaign_id')->nullable();

            // Template info
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['newsletter', 'promotion', 'announcement', 'survey', 'feedback', 'custom'])->default('custom')->index();

            // Template content
            $table->json('content')->nullable(); // Reusable content structure
            $table->string('thumbnail_url')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamps();

            // Index for searching
            $table->fulltext(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_campaign_templates');
    }
};
