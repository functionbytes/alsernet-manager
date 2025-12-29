<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('helpdesk')->create('helpdesk_group_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('user_id');
            $table->string('conversation_priority')->default('primary');
            $table->createdAt();

            $table->foreign('group_id')->references('id')->on('helpdesk_groups')->onDelete('cascade');

            $table->unique(['group_id', 'user_id']);
            $table->index('group_id');
        });
    }

    public function down(): void
    {
        Schema::connection('helpdesk')->dropIfExists('helpdesk_group_user');
    }
};
