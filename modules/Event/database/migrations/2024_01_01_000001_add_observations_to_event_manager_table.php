<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Since Event tables exist in the remote PrestaShop database,
     * we create observations and sync tracking in the local Laravel database
     * and link them via event_id (which maps to the remote PrestaShop event ID).
     */
    public function up(): void
    {
        // Create a local sync tracking table for the Event Manager
        // This bridges the local Laravel database with remote PrestaShop events
        Schema::create('aalv_event_metadata', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index()->comment('Reference to PrestaShop event ID');
            $table->text('observations')->nullable()->comment('Notes and observations about the event');
            $table->string('external_id')->nullable()->unique()->index()->comment('PrestaShop external reference ID');
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending')->comment('Current sync status');
            $table->timestamp('sync_timestamp')->nullable()->comment('Last synchronization timestamp');
            $table->timestamps();

            $table->index(['event_id', 'sync_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aalv_event_metadata');
    }
};
