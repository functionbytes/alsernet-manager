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
        Schema::connection('mysql')->create('helpdesk_campaigns', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->enum('type', ['popup', 'banner', 'slide-in', 'full-screen'])->default('popup');
            $table->enum('status', ['draft', 'scheduled', 'active', 'ended', 'paused'])->default('draft')->index();

            // Campaign content and configuration (JSON)
            $table->json('content')->nullable(); // Array of content blocks
            $table->json('appearance')->nullable(); // Colors, fonts, positioning
            $table->json('conditions')->nullable(); // Targeting rules
            $table->json('metadata')->nullable(); // Additional data

            // Publishing dates
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_campaigns');
    }
};
