<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add key column as nullable first
        Schema::table('document_groups', function (Blueprint $table) {
            $table->string('key', 100)->nullable()->after('name');
        });

        // Update existing groups with unique keys based on their names
        $existingGroups = DB::table('document_groups')->get();
        foreach ($existingGroups as $group) {
            $key = \Illuminate\Support\Str::slug($group->name);
            DB::table('document_groups')
                ->where('id', $group->id)
                ->update(['key' => $key]);
        }

        // Now make the key unique
        Schema::table('document_groups', function (Blueprint $table) {
            $table->unique('key');
        });

        // Create validation workflow groups if they don't exist
        $workflowGroups = [
            [
                'name' => 'Administrativos',
                'key' => 'administrative',
                'description' => 'Grupo de validación administrativa - Primera etapa de validación para todos los documentos',
                'assignment_mode' => 'manual',
                'is_default' => false,
                'is_active' => true,
                'order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gerencia',
                'key' => 'manager',
                'description' => 'Grupo de validación de gerencia - Segunda etapa para documentos de armas (ESCOPETA, RIFLE, CORTA)',
                'assignment_mode' => 'manual',
                'is_default' => false,
                'is_active' => true,
                'order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Contabilidad Workflow',
                'key' => 'accounting',
                'description' => 'Grupo de validación contable - Etapa final para documentos que requieren financiación',
                'assignment_mode' => 'manual',
                'is_default' => false,
                'is_active' => true,
                'order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($workflowGroups as $group) {
            // Only insert if key doesn't exist
            $exists = DB::table('document_groups')->where('key', $group['key'])->exists();
            if (! $exists) {
                DB::table('document_groups')->insert($group);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove workflow groups
        DB::table('document_groups')->whereIn('key', ['administrative', 'manager', 'accounting'])->delete();

        Schema::table('document_groups', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->dropColumn('key');
        });
    }
};
