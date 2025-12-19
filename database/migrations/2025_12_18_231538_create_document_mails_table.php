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
        Schema::create('document_mails', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('email_type', 50); // request, reminder, upload, approval, rejection, missing, custom
            $table->string('recipient_email');
            $table->string('subject');
            $table->longText('body_html');
            $table->text('body_text')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('mail_templates')->nullOnDelete();
            $table->unsignedBigInteger('sent_by')->nullable(); // admin ID who triggered the email
            $table->json('metadata')->nullable(); // extra data like missing_docs, reason, etc.
            $table->enum('status', ['queued', 'sent', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'email_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_mails');
    }
};
