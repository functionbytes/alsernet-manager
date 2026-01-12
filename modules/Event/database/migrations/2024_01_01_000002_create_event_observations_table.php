<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aalv_event_observations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index()->comment('Reference to event');
            $table->unsignedBigInteger('user_id')->nullable()->index()->comment('User who created observation');
            $table->text('content')->comment('Observation content/notes');
            $table->timestamps();

            // Index for efficient queries
            $table->index(['event_id', 'created_at']);

            // Foreign key constraint to users table if it exists
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aalv_event_observations');
    }
};
