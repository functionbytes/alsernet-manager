<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Document\Entities\DocumentPermission;
use Modules\Document\Entities\DocumentValidatorGroup;

class DocumentGroupPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Asigns all document permissions to a validator group
     */
    public function run(): void
    {
        // Get all available permissions
        $allPermissions = DocumentPermission::active()->pluck('id')->toArray();

        if (empty($allPermissions)) {
            $this->command->error('No permissions found in database. Run document permission migrations first.');
            return;
        }

        // Find or create test/default validator group
        $group = DocumentValidatorGroup::firstOrCreate(
            ['name' => 'Grupo de Prueba'],
            [
                'key' => 'test-group',
                'description' => 'Grupo con acceso completo a todos los permisos del documento',
                'is_active' => true,
            ]
        );

        // Assign all permissions to the group
        $group->permissions()->sync($allPermissions);

        $this->command->info("✓ Asignados ".count($allPermissions)." permisos al grupo: {$group->name}");

        // Display permission breakdown
        $byCategory = DocumentPermission::active()
            ->get()
            ->groupBy('category');

        $this->command->line("\nPermisos por categoría:");
        foreach ($byCategory as $category => $perms) {
            $this->command->info("  ✓ " . strtoupper($category) . ": " . count($perms) . " permisos");
        }

        $this->command->newLine();
        $this->command->info("Para asignar permisos selectivos, ve a:");
        $this->command->line("/settings/documents/groups/permissions/{$group->id}/edit");
    }
}
