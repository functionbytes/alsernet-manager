<?php

namespace Modules\Document\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DocumentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds document-related permissions for Spatie Laravel Permission.
     * Creates granular permissions for document management features and assigns
     * them to predefined roles (super-admin, manager, administrative).
     *
     * Permissions are organized by feature area for easy maintenance and scalability.
     * Use this seeder incrementally - add new permissions to definePermissions() as needed.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all document permissions
        $permissions = $this->definePermissions();

        // Create permissions in database
        $this->createPermissionsFor($permissions);

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        $this->command->info('✅ Document permissions seeded successfully');
    }

    /**
     * Define all document-related permissions.
     *
     * Structure: 'permission.name' => 'Human readable description'
     * Organized by feature area for easy management.
     *
     * @return array
     */
    private function definePermissions(): array
    {
        return [
            // Documents - Core
            'documents.view' => 'Ver documentos',
            'documents.view_all' => 'Ver todos los documentos',
            'documents.create' => 'Crear documentos',
            'documents.update' => 'Actualizar documentos',
            'documents.delete' => 'Eliminar documentos',
            'documents.manage' => 'Gestionar documentos',
            'documents.approve_stage' => 'Aprobar etapas de documentos',
            'documents.reject_stage' => 'Rechazar etapas de documentos',
            'documents.assign' => 'Asignar documentos',
            'documents.export' => 'Exportar documentos',
            'documents.import' => 'Importar documentos',
            'documents.bulk_update' => 'Actualización masiva de documentos',

            // Documents - Files
            'documents.files.create' => 'Cargar archivos en documentos',
            'documents.files.update' => 'Actualizar archivos de documentos',
            'documents.files.delete' => 'Eliminar archivos de documentos',
            'documents.files.download' => 'Descargar archivos de documentos',

            // Documents - Notes
            'documents.notes.create' => 'Crear notas en documentos',
            'documents.notes.update' => 'Actualizar notas de documentos',
            'documents.notes.delete' => 'Eliminar notas de documentos',

            // Document Types
            'document_types.view' => 'Ver tipos de documento',
            'document_types.create' => 'Crear tipos de documento',
            'document_types.update' => 'Actualizar tipos de documento',
            'document_types.delete' => 'Eliminar tipos de documento',

            // Document Groups
            'document_groups.view' => 'Ver grupos de documentos',
            'document_groups.create' => 'Crear grupos de documentos',
            'document_groups.update' => 'Actualizar grupos de documentos',
            'document_groups.delete' => 'Eliminar grupos de documentos',
            'document_groups.configure' => 'Configurar grupos de documentos',

            // Document Conditions
            'document_conditions.view' => 'Ver condiciones de validación',
            'document_conditions.create' => 'Crear condiciones de validación',
            'document_conditions.update' => 'Actualizar condiciones de validación',
            'document_conditions.delete' => 'Eliminar condiciones de validación',

            // Document SLA Policies
            'document_sla_policies.view' => 'Ver políticas SLA de documentos',
            'document_sla_policies.create' => 'Crear políticas SLA de documentos',
            'document_sla_policies.update' => 'Actualizar políticas SLA de documentos',
            'document_sla_policies.delete' => 'Eliminar políticas SLA de documentos',

            // Document Storage
            'document_storage.view' => 'Ver almacenamiento de documentos',
            'document_storage.update' => 'Actualizar configuración de almacenamiento',
            'document_storage.test' => 'Probar conexión de almacenamiento',

            // Document Settings
            'document_settings.view' => 'Ver configuración de documentos',
            'document_settings.update' => 'Actualizar configuración de documentos',
            'document_settings.reset' => 'Restablecer configuración de documentos',

            // Document Blockades
            'document_blockades.view' => 'Ver bloqueos de documentos',
            'document_blockades.create' => 'Crear bloqueos de documentos',
            'document_blockades.update' => 'Actualizar bloqueos de documentos',
            'document_blockades.delete' => 'Eliminar bloqueos de documentos',
            'document_blockades.sync' => 'Sincronizar bloqueos de documentos',
        ];
    }

    /**
     * Create or find permissions in the database.
     *
     * Uses findOrCreate to be idempotent - safe to run multiple times.
     *
     * @param array $permissions Array of permission_name => description
     * @return void
     */
    private function createPermissionsFor(array $permissions): void
    {
        foreach ($permissions as $permissionName => $description) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $this->command->line("Created " . count($permissions) . " permissions");
    }

    /**
     * Assign permissions to document-related roles.
     *
     * Defines three role levels:
     * - super-admin: All permissions (via wildcard)
     * - manager: Configuration and view permissions
     * - administrative: CRUD operations and validation
     *
     * @return void
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Admin - All permissions
        $this->assignToRole('super-admin', [
            'documents.*',
            'document_types.*',
            'document_groups.*',
            'document_conditions.*',
            'document_sla_policies.*',
            'document_storage.*',
            'document_settings.*',
            'document_blockades.*',
        ]);

        // Manager - Configuration and view permissions
        $this->assignToRole('manager', [
            'documents.view',
            'documents.view_all',
            'documents.export',
            'document_types.view',
            'document_groups.view',
            'document_groups.configure',
            'document_conditions.view',
            'document_sla_policies.view',
            'document_storage.view',
            'document_settings.view',
            'document_blockades.view',
        ]);

        // Administrative - CRUD and validation permissions
        $this->assignToRole('administrative', [
            'documents.view',
            'documents.view_all',
            'documents.create',
            'documents.update',
            'documents.assign',
            'documents.approve_stage',
            'documents.reject_stage',
            'documents.export',
            'documents.files.create',
            'documents.files.update',
            'documents.files.delete',
            'documents.files.download',
            'documents.notes.create',
            'documents.notes.update',
            'documents.notes.delete',
            'document_types.view',
            'document_groups.view',
            'document_conditions.view',
            'document_sla_policies.view',
            'document_storage.view',
            'document_settings.view',
            'document_blockades.view',
        ]);
    }

    /**
     * Helper method to assign permissions to a role.
     *
     * Supports wildcard permissions (e.g., 'documents.*').
     * Safely handles cases where role doesn't exist yet.
     *
     * @param string $roleName The role name
     * @param array $permissions Array of permission names (supports wildcards)
     * @return void
     */
    private function assignToRole(string $roleName, array $permissions): void
    {
        $role = Role::findOrCreate($roleName, 'web');

        foreach ($permissions as $permission) {
            // Handle wildcard permissions (e.g., 'documents.*')
            if (str_ends_with($permission, '.*')) {
                $prefix = substr($permission, 0, -2);
                $matchingPermissions = Permission::query()
                    ->where('name', 'like', $prefix . '.%')
                    ->get();

                $role->givePermissionTo($matchingPermissions);
            } else {
                // Regular permission
                $role->givePermissionTo($permission);
            }
        }

        $this->command->line("Assigned permissions to role: {$roleName}");
    }
}
