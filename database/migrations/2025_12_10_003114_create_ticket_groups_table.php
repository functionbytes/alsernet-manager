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
        Schema::connection('helpdesk')->create('helpdesk_ticket_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('assignment_mode', ['manual', 'round_robin', 'load_balanced'])->default('manual');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('is_default');
            $table->index('is_active');
            $table->index('order');
        });

        // Pivot table for ticket_group_user (many-to-many with users)
        Schema::connection('helpdesk')->create('helpdesk_ticket_group_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_group_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('priority', ['primary', 'backup'])->default('primary');
            $table->timestamp('created_at')->nullable();

            $table->foreign('ticket_group_id')->references('id')->on('helpdesk_ticket_groups')->cascadeOnDelete();
            // Note: No FK for user_id (cross-database relationship)

            $table->unique(['ticket_group_id', 'user_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('helpdesk')->dropIfExists('helpdesk_ticket_group_user');
        Schema::connection('helpdesk')->dropIfExists('helpdesk_ticket_groups');
    }
};
