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
        Schema::connection('mysql')->create('helpdesk_conversation_tag_pivot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('id')
                ->on('helpdesk_conversations')
                ->onDelete('cascade');

            $table->foreign('tag_id')
                ->references('id')
                ->on('helpdesk_conversation_tags')
                ->onDelete('cascade');

            $table->unique(['conversation_id', 'tag_id'], 'conversation_tag_unique');
            $table->index('conversation_id');
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_conversation_tag_pivot');
    }
};
