<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_mails', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('email_type');
            $table->string('recipient_email');
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('mail_templates')->nullOnDelete();
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status')->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('document_id');
            $table->index('email_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_mails');
    }
};
