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
        // Pivot table: TicketCategory <-> TicketGroup
        Schema::connection('mysql')->create('helpdesk_ticket_category_ticket_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_category_id');
            $table->unsignedBigInteger('ticket_group_id');
            $table->boolean('is_default')->default(false); // Primary group for this category
            $table->integer('priority')->default(0); // Assignment order
            $table->timestamps();

            $table->foreign('ticket_category_id', 'tkt_cat_group_cat_fk')
                ->references('id')
                ->on('helpdesk_ticket_categories')
                ->cascadeOnDelete();

            $table->foreign('ticket_group_id', 'tkt_cat_group_group_fk')
                ->references('id')
                ->on('helpdesk_ticket_groups')
                ->cascadeOnDelete();

            $table->unique(['ticket_category_id', 'ticket_group_id'], 'category_group_unique');
            $table->index('is_default');
        });

        // Pivot table: TicketCategory <-> TicketCannedReply
        Schema::connection('mysql')->create('helpdesk_ticket_category_ticket_canned_reply', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_category_id');
            $table->unsignedBigInteger('ticket_canned_reply_id');
            $table->integer('order')->default(0); // Display order
            $table->timestamps();

            $table->foreign('ticket_category_id', 'tkt_cat_canned_cat_fk')
                ->references('id')
                ->on('helpdesk_ticket_categories')
                ->cascadeOnDelete();

            $table->foreign('ticket_canned_reply_id', 'tkt_cat_canned_reply_fk')
                ->references('id')
                ->on('helpdesk_ticket_canned_replies')
                ->cascadeOnDelete();

            $table->unique(['ticket_category_id', 'ticket_canned_reply_id'], 'category_canned_reply_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_ticket_category_ticket_canned_reply');
        Schema::connection('mysql')->dropIfExists('helpdesk_ticket_category_ticket_group');
    }
};
