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
        Schema::connection('helpdesk')->create('helpdesk_ticket_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->onDelete('cascade');
            $table->unsignedBigInteger('user_id'); // Agent only

            // Content
            $table->string('title')->nullable();
            $table->text('body');

            // Display
            $table->boolean('is_pinned')->default(false);
            $table->enum('color', ['yellow', 'blue', 'green', 'red', 'purple', 'orange'])->default('yellow');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['ticket_id', 'is_pinned', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('helpdesk')->dropIfExists('helpdesk_ticket_notes');
    }
};
