<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 36)->unique();
            $table->string('ticket_number', 20)->unique();
            $table->string('subject', 255);
            $table->text('description');
            $table->string('customer_name', 255);
            $table->string('customer_email', 255)->index();
            $table->string('customer_phone', 50)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('priority_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('due_at')->nullable()->index();
            $table->unsignedBigInteger('sla_policy_id')->nullable();
            $table->boolean('sla_breached')->default(false)->index();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('helpdesk_categories')->onDelete('set null');
            $table->foreign('priority_id')->references('id')->on('helpdesk_priorities')->onDelete('restrict');
            $table->foreign('status_id')->references('id')->on('helpdesk_statuses')->onDelete('restrict');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('sla_policy_id')->references('id')->on('helpdesk_sla_policies')->onDelete('set null');

            // Composite indexes
            $table->index(['assigned_to', 'status_id']);
            $table->index(['customer_email', 'status_id']);
            $table->index(['last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_tickets');
    }
};
