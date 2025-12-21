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
        Schema::connection('mysql')->create('helpdesk_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('helpdesk_groups')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->comment('References users.id in main database');
            $table->enum('conversation_priority', ['primary', 'backup'])->default('backup');
            $table->timestamp('created_at')->nullable();

            $table->unique(['group_id', 'user_id']);
            $table->index('user_id');
            $table->index('group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_group_user');
    }
};
