<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aalv_event_sync_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index()->comment('Reference to event');
            $table->string('external_id')->nullable()->index()->comment('External event ID for sync tracking');
            $table->enum('source', ['laravel', 'prestashop'])->comment('Origin of the sync action');
            $table->string('action')->comment('Sync action: create, update, delete');
            $table->enum('status', ['pending', 'synced', 'failed'])->default('pending')->comment('Current sync status');
            $table->json('payload')->nullable()->comment('Data payload synchronized');
            $table->text('error_message')->nullable()->comment('Error details if sync failed');
            $table->timestamp('synced_at')->nullable()->comment('When the sync completed');
            $table->timestamps();

            // Indexes for performance
            $table->index(['event_id', 'created_at']);
            $table->index(['status', 'source']);
            $table->index(['synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aalv_event_sync_log');
    }
};
