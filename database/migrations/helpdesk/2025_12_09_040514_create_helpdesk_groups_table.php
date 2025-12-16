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
        Schema::connection('mysql')->create('helpdesk_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('assignment_mode', ['round_robin', 'load_balanced', 'manual'])->default('round_robin');
            $table->boolean('default')->default(false);
            $table->timestamps();

            $table->index('default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('helpdesk_groups');
    }
};
