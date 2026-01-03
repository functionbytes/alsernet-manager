<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DocumentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app([\Spatie\Permission\PermissionRegistrar::class])->forgetCachedPermissions();

        $permissions = $this->definePermissions();
        $this->createPermissionsFor($permissions);
        $this->assignPermissionsToRoles();

        $this->command->info('✅ Document permissions seeded successfully');
    }

    /**
     * Define all document-related permissions.
     */
    private function definePermissions(): array
    {
        return [
            'documents.view' => 'Ver documentos',
            'documents.view_all' => 'Ver todos los documentos',
            'documents.create' => 'Crear documentos',
            'documents.update' => 'Actualizar documentos',
            'documents.delete' => 'Eliminar documentos',
            'documents.manage' => 'Gestionar documentos',
            'documents.approve' => 'Aprobar etapas de documentos',
            'documents.reject' => 'Rechazar etapas de documentos',
            'documents.export' => 'Exportar documentos',
            'documents.files.upload' => 'Cargar archivos en documentos',
            'documents.files.download' => 'Descargar archivos de documentos',
            'document_types.view' => 'Ver tipos de documento',
            'document_types.manage' => 'Gestionar tipos de documento',
            'document_settings.view' => 'Ver configuración de documentos',
            'document_settings.manage' => 'Gestionar configuración de documentos',
        ];
    }

    /**
     * Create or find permissions in the database.
     */
    private function createPermissionsFor(array $permissions): void
    {
        foreach ($permissions as $permissionName => $description) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $this->command->line('Created '.count($permissions).' permissions');
    }

    /**
     * Assign permissions to document-related roles.
     */
    private function assignPermissionsToRoles(): void
    {
        $this->assignToRole('super-admin', ['documents.*', 'document_types.*', 'document_settings.*']);
        $this->assignToRole('manager', [
            'documents.view',
            'documents.view_all',
            'documents.export',
            'document_types.view',
            'document_settings.view',
        ]);
        $this->assignToRole('administrative', [
            'documents.view',
            'documents.view_all',
            'documents.create',
            'documents.update',
            'documents.approve',
            'documents.reject',
            'documents.export',
            'documents.files.upload',
            'documents.files.download',
            'document_types.view',
            'document_settings.view',
        ]);
    }

    /**
     * Helper method to assign permissions to a role.
     */
    private function assignToRole(string $roleName, array $permissions): void
    {
        $role = Role::findOrCreate($roleName, 'web');

        foreach ($permissions as $permission) {
            if (str_ends_with($permission, '.*')) {
                $prefix = substr($permission, 0, -2);
                $matchingPermissions = Permission::query()
                    ->where('name', 'like', $prefix.'.%')
                    ->get();

                $role->givePermissionTo($matchingPermissions);
            } else {
                $role->givePermissionTo($permission);
            }
        }

        $this->command->line("Assigned permissions to role: {$roleName}");
    }
}
