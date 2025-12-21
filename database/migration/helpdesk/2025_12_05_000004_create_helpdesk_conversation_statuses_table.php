<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('helpdesk_conversation_statuses', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->string('slug')->unique(); // URL-friendly identifier
            $table->string('color', 7); // Hex color code (#RRGGBB)
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_default')->default(false); // Is this the default status?
            $table->boolean('is_system')->default(false); // System status (cannot be deleted)
            $table->boolean('is_open')->default(false)->index(); // Is this an open status?
            $table->boolean('active')->default(true); // Is this status active?

            $table->timestamps();
        });

        // Insert default statuses
        DB::table('helpdesk_conversation_statuses')->insert([
            [
                'name' => 'Open',
                'slug' => 'open',
                'color' => '#5D87FF',
                'is_default' => true,
                'is_system' => true,
                'is_open' => true,
                'active' => true,
                'order' => 1,
                'description' => 'Conversation is open and waiting for response',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Waiting for Customer',
                'slug' => 'waiting_customer',
                'color' => '#FFAE1F',
                'is_default' => false,
                'is_system' => true,
                'is_open' => true,
                'active' => true,
                'order' => 2,
                'description' => 'Waiting for customer reply',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'On Hold',
                'slug' => 'on_hold',
                'color' => '#6C757D',
                'is_default' => false,
                'is_system' => false,
                'is_open' => true,
                'active' => true,
                'order' => 3,
                'description' => 'Conversation is on hold',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Resolved',
                'slug' => 'resolved',
                'color' => '#13DEB9',
                'is_default' => false,
                'is_system' => true,
                'is_open' => false,
                'active' => true,
                'order' => 4,
                'description' => 'Issue has been resolved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Closed',
                'slug' => 'closed',
                'color' => '#FA896B',
                'is_default' => false,
                'is_system' => true,
                'is_open' => false,
                'active' => true,
                'order' => 5,
                'description' => 'Conversation is closed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_conversation_statuses');
    }
};
