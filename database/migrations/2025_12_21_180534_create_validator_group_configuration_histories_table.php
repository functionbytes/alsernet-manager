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
        Schema::create('config_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_group_id')->constrained('validator_groups', 'id')->cascadeOnDelete();
            $table->foreignId('configuration_id')->constrained('validator_group_configurations', 'id')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->string('configuration_key')->comment('The config key that was changed');
            $table->string('configuration_label')->comment('The config label for display');
            $table->boolean('old_value')->comment('Previous value');
            $table->boolean('new_value')->comment('New value');
            $table->string('action')->default('update')->comment('update, create, delete');
            $table->text('description')->nullable()->comment('Description of the change');
            $table->timestamps();

            $table->index(['validator_group_id', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_histories');
    }
};
