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
        Schema::create('helpdesk_ticket_sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();

            // Time targets (in minutes)
            $table->integer('first_response_time')->nullable(); // Minutes to first agent response
            $table->integer('next_response_time')->nullable();  // Minutes between responses
            $table->integer('resolution_time')->nullable();     // Minutes to ticket closure

            // Business hours settings
            $table->boolean('business_hours_only')->default(true);
            $table->json('business_hours')->nullable(); // {'monday': {'start': '09:00', 'end': '17:00'}}
            $table->string('timezone')->default('America/Mexico_City');

            // Priority multipliers
            $table->json('priority_multipliers')->nullable(); // {'urgent': 0.5, 'high': 0.75, 'normal': 1.0, 'low': 1.5}

            // Escalation
            $table->boolean('enable_escalation')->default(false);
            $table->integer('escalation_threshold_percent')->default(80); // Escalate at 80% of SLA time
            $table->json('escalation_recipients')->nullable(); // User IDs or emails to notify

            $table->boolean('active')->default(true);
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->index('active');
            $table->index('is_default');
        });

        // Insert default SLA policy
        DB::table('helpdesk_ticket_sla_policies')->insert([
            'name' => 'Standard SLA',
            'description' => 'Standard service level agreement for all tickets',
            'first_response_time' => 60,  // 1 hour
            'next_response_time' => 240,  // 4 hours
            'resolution_time' => 1440,    // 24 hours
            'business_hours_only' => true,
            'timezone' => 'America/Mexico_City',
            'priority_multipliers' => json_encode([
                'urgent' => 0.25,
                'high' => 0.5,
                'normal' => 1.0,
                'low' => 2.0,
            ]),
            'enable_escalation' => false,
            'escalation_threshold_percent' => 80,
            'active' => true,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_sla_policies');
    }
};
