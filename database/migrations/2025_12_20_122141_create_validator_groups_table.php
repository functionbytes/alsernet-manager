<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Crea las tablas validator_groups y validator_group_user
     * que son versiones generalizadas de document_groups para
     * soportar validación polimórfica en múltiples entidades.
     */
    public function up(): void
    {
        // 1. Crear tabla validator_groups
        Schema::create('validator_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('name', 191);
            $table->string('key', 100)->unique();
            $table->text('description')->nullable();
            $table->enum('assignment_mode', ['manual', 'round_robin', 'load_balanced'])->default('manual');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('key');
            $table->index('is_active');
        });

        // 2. Crear tabla pivote validator_group_user
        Schema::create('validator_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_group_id')->constrained('validator_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('priority', ['primary', 'backup'])->default('primary');
            $table->timestamp('created_at')->nullable();

            $table->unique(['validator_group_id', 'user_id'], 'unique_validator_group_user');
        });

        // 3. Migrar datos de document_groups si existe
        if (Schema::hasTable('document_groups')) {
            $documentGroups = DB::table('document_groups')->get();

            foreach ($documentGroups as $group) {
                $validatorGroupId = DB::table('validator_groups')->insertGetId([
                    'uid' => Str::uuid()->toString(),
                    'name' => $group->name,
                    'key' => $group->key,
                    'description' => $group->description,
                    'assignment_mode' => $group->assignment_mode ?? 'manual',
                    'is_default' => $group->is_default ?? false,
                    'is_active' => $group->is_active ?? true,
                    'sort_order' => $group->order ?? 0,
                    'created_at' => $group->created_at ?? now(),
                    'updated_at' => $group->updated_at ?? now(),
                ]);

                // Migrar usuarios del grupo
                if (Schema::hasTable('document_group_user')) {
                    $users = DB::table('document_group_user')
                        ->where('document_group_id', $group->id)
                        ->get();

                    foreach ($users as $user) {
                        DB::table('validator_group_user')->insert([
                            'validator_group_id' => $validatorGroupId,
                            'user_id' => $user->user_id,
                            'priority' => $user->priority ?? 'primary',
                            'created_at' => $user->created_at ?? now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validator_group_user');
        Schema::dropIfExists('validator_groups');
    }
};
