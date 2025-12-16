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
        Schema::connection('mysql')->create('helpdesk_conversation_views', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name (My Open Tickets, High Priority, etc.)
            $table->text('description')->nullable(); // Optional description
            $table->json('filters')->nullable(); // JSON array of filter configuration
            $table->unsignedBigInteger('user_id')->nullable()->comment('References users.id in main database'); // Null = public/shared view
            $table->boolean('is_public')->default(false); // Public views visible to all agents
            $table->boolean('is_default')->default(false); // Default view for user
            $table->boolean('is_system')->default(false); // System view (cannot be deleted)
            $table->integer('order')->default(0); // Sort order for display
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_public');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_conversation_views');
    }
};
