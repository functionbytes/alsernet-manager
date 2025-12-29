<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_endpoint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_endpoint_id')->constrained('mail_endpoints')->onDelete('cascade');
            $table->json('payload')->nullable();
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('mail_subject')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('job_id')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('mail_endpoint_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_endpoint_logs');
    }
};
