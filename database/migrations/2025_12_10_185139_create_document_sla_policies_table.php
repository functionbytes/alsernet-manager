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
        Schema::create('document_sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();

            // Time targets (in minutes)
            $table->integer('upload_request_time')->nullable(); // Minutes to request upload from customer
            $table->integer('review_time')->nullable(); // Minutes to review documents
            $table->integer('approval_time')->nullable(); // Minutes to approve/reject documents

            // Business hours settings
            $table->boolean('business_hours_only')->default(true);
            $table->json('business_hours')->nullable(); // {'monday': {'start': '09:00', 'end': '17:00'}}
            $table->string('timezone')->default('America/Mexico_City');

            // Document type targets
            $table->json('document_type_multipliers')->nullable(); // {'corta': 0.5, 'rifle': 1.0, etc}

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
        \Illuminate\Support\Facades\DB::table('document_sla_policies')->insert([
            'name' => 'Standard Document SLA',
            'description' => 'Standard service level agreement for all documents',
            'upload_request_time' => 120,  // 2 hours
            'review_time' => 480,          // 8 hours
            'approval_time' => 1440,       // 24 hours
            'business_hours_only' => true,
            'timezone' => 'America/Mexico_City',
            'document_type_multipliers' => json_encode([
                'corta' => 0.75,
                'rifle' => 1.0,
                'escopeta' => 1.0,
                'dni' => 0.5,
                'general' => 1.0,
                'order' => 1.5,
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
        Schema::dropIfExists('document_sla_policies');
    }
};
