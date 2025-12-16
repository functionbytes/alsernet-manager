<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_endpoint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_endpoint_id')->constrained('email_endpoints')->onDelete('cascade');

            // Request data
            $table->json('payload'); // Raw JSON received
            $table->string('status'); // pending, processing, success, failed
            $table->text('error_message')->nullable();

            // Email details
            $table->string('recipient_email')->nullable();
            $table->string('email_subject')->nullable();

            // Tracking
            $table->timestamp('sent_at')->nullable();
            $table->string('job_id')->nullable(); // Queue job ID

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_endpoint_logs');
    }
};
