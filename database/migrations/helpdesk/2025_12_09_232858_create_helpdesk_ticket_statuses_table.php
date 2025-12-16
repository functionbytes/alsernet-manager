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
        Schema::create('helpdesk_ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color', 7); // Hex color code
            $table->text('description')->nullable();
            $table->integer('order')->default(0);

            // Status behavior flags
            $table->boolean('is_default')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_open')->default(true)->index(); // Open vs Closed
            $table->boolean('stops_sla_timer')->default(false); // Pause SLA when in this status
            $table->boolean('active')->default(true)->index();

            $table->timestamps();
        });

        // Insert default ticket statuses
        DB::table('helpdesk_ticket_statuses')->insert([
            [
                'name' => 'New',
                'slug' => 'new',
                'color' => '#5D87FF',
                'description' => 'Newly created ticket',
                'is_default' => true,
                'is_system' => true,
                'is_open' => true,
                'stops_sla_timer' => false,
                'active' => true,
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Open',
                'slug' => 'open',
                'color' => '#5D87FF',
                'description' => 'Ticket is open and being worked on',
                'is_default' => false,
                'is_system' => true,
                'is_open' => true,
                'stops_sla_timer' => false,
                'active' => true,
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'In Progress',
                'slug' => 'in-progress',
                'color' => '#FFAE1F',
                'description' => 'Ticket is actively being worked on',
                'is_default' => false,
                'is_system' => true,
                'is_open' => true,
                'stops_sla_timer' => false,
                'active' => true,
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Waiting on Customer',
                'slug' => 'waiting-customer',
                'color' => '#FEC90F',
                'description' => 'Waiting for customer response',
                'is_default' => false,
                'is_system' => true,
                'is_open' => true,
                'stops_sla_timer' => true, // Pause SLA when waiting on customer
                'active' => true,
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'On Hold',
                'slug' => 'on-hold',
                'color' => '#6C757D',
                'description' => 'Ticket is temporarily on hold',
                'is_default' => false,
                'is_system' => false,
                'is_open' => true,
                'stops_sla_timer' => true, // Pause SLA when on hold
                'active' => true,
                'order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Resolved',
                'slug' => 'resolved',
                'color' => '#13DEB9',
                'description' => 'Issue has been resolved',
                'is_default' => false,
                'is_system' => true,
                'is_open' => false,
                'stops_sla_timer' => true, // SLA stops when resolved
                'active' => true,
                'order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Closed',
                'slug' => 'closed',
                'color' => '#FA896B',
                'description' => 'Ticket is closed',
                'is_default' => false,
                'is_system' => true,
                'is_open' => false,
                'stops_sla_timer' => true, // SLA stops when closed
                'active' => true,
                'order' => 7,
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
        Schema::dropIfExists('helpdesk_ticket_statuses');
    }
};
