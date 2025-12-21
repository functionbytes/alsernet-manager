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
        Schema::create('helpdesk_ticket_watchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id'); // FK to users (main DB)
            $table->timestamp('added_at')->useCurrent();

            $table->unique(['ticket_id', 'user_id']);
            $table->foreign('ticket_id')->references('id')->on('helpdesk_tickets')->onDelete('cascade');
            // user_id FK handled in model (cross-database)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_watchers');
    }
};
