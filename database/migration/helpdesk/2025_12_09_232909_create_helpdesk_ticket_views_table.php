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
        Schema::create('helpdesk_ticket_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // FK to users (main DB), nullable for system views
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 7)->nullable();
            $table->json('filters'); // {'status_id': 1, 'priority': 'high', 'category_id': 2}
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_system')->default(false); // System views cannot be deleted
            $table->boolean('is_shared')->default(false); // Shared with team

            $table->timestamps();

            $table->index('user_id');
            $table->index('is_default');
            $table->index('is_system');
            // user_id FK handled in model (cross-database)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_views');
    }
};
