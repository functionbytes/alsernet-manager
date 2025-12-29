<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Migrar datos de document_groups si existe
        if (Schema::hasTable('document_document_groups')) {
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
                if (Schema::hasTable('document_document_group_user')) {
                    $users = DB::table('document_document_group_user')
                        ->where('document_group_id', $group->id)
                        ->get();

                    foreach ($users as $user) {
                        DB::table('document_validator_group_user')->insert([
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

    public function down(): void
    {
        // Delete migrated data
        DB::table('document_validator_group_user')->delete();
        DB::table('document_validator_groups')->delete();
    }
};
