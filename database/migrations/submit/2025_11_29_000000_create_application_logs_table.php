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
        Schema::create('application_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level')->index(); // ERROR, WARNING, INFO, DEBUG, etc.
            $table->string('channel')->index(); // Name of the channel
            $table->text('message');
            $table->json('context')->nullable(); // Additional context as JSON
            $table->json('extra')->nullable(); // Extra data
            $table->longText('stack_trace')->nullable(); // For exceptions
            $table->string('user_id')->nullable()->index(); // Associated user
            $table->string('ip_address')->nullable(); // Request IP
            $table->string('url')->nullable(); // Request URL
            $table->string('method')->nullable()->default('GET'); // HTTP method
            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['created_at', 'level']);
            $table->index(['created_at', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_logs');
    }
};
