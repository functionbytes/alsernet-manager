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
        Schema::connection('mysql')->create('helpdesk_agent_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->comment('References users.id in main database');
            $table->integer('assignment_limit')->default(0)->comment('0 = unlimited');
            $table->enum('accepts_conversations', ['yes', 'no', 'working_hours'])->default('yes');
            $table->json('working_hours')->nullable()->comment('Working hours schedule');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_agent_settings');
    }
};
