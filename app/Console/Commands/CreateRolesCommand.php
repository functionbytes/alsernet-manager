<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateRolesCommand extends Command
{
    protected $signature = 'roles:create';

    protected $description = 'Create all application roles from roleMapping configuration';

    public function handle()
    {
        $this->info('🔐 Creating application roles...\n');

        // Define all roles with detailed descriptions
        $allRoles = [
            'super-admin' => [
                'label' => 'Super Administrador',
                'description' => 'Acceso completo a todas las funciones y módulos del sistema. Puede gestionar usuarios, roles, permisos, configuraciones y ver todos los datos. Uso solo para administradores supremos del sistema.',
                'color' => '#FF0000', // Rojo
            ],
            'admin' => [
                'label' => 'Administrador',
                'description' => 'Acceso casi completo al sistema. Puede gestionar usuarios, roles, y la mayoría de funciones. No puede modificar la configuración del sistema ni roles de super-admin.',
                'color' => '#FF6600', // Naranja
            ],
            'manager' => [
                'label' => 'Gerente General',
                'description' => 'Gestiona usuarios y operaciones generales del perfil Manager. Puede crear, editar y eliminar usuarios; ver reportes; y administrar datos básicos del sistema.',
                'color' => '#0066FF', // Azul
            ],
            'callcenter-manager' => [
                'label' => 'Gerente de Call Center',
                'description' => 'Gestiona el call center. Puede asignar tareas a agentes, ver reportes de llamadas, gestionar colas, y monitorear el desempeño del equipo.',
                'color' => '#00AA00', // Verde
            ],
            'callcenter-agent' => [
                'label' => 'Agente de Call Center',
                'description' => 'Atiende llamadas de clientes. Puede ver información de clientes, registrar llamadas, crear tickets de soporte, y seguimiento de casos.',
                'color' => '#00DD00', // Verde claro
            ],
            'inventory-manager' => [
                'label' => 'Gerente de Inventario',
                'description' => 'Gestiona inventario y almacén. Puede crear/editar productos, gestionar stock, controlar entradas y salidas, y generar reportes de inventario.',
                'color' => '#9900FF', // Púrpura
            ],
            'inventory-staff' => [
                'label' => 'Personal de Inventario',
                'description' => 'Actualiza inventario. Puede registrar movimientos de stock, crear recuentos, y registrar entradas/salidas de productos. Acceso limitado a reportes.',
                'color' => '#CC99FF', // Púrpura claro
            ],
            'shop-manager' => [
                'label' => 'Gerente de Tienda',
                'description' => 'Gestiona operaciones de tienda. Puede vender productos, gestionar clientes, ver ventas, manejar caja, y generar reportes de tienda.',
                'color' => '#FF9900', // Naranja oscuro
            ],
            'shop-staff' => [
                'label' => 'Personal de Tienda',
                'description' => 'Asiste en operaciones de tienda. Puede registrar ventas, consultar inventario, procesar cobros y ayudar a clientes. Acceso limitado a reportes.',
                'color' => '#FFCC00', // Amarillo
            ],
            'administrative' => [
                'label' => 'Administrativo',
                'description' => 'Realiza tareas administrativas. Puede gestionar documentos, archivos, correspondencia, y realizar trámites administrativos del sistema.',
                'color' => '#666666', // Gris
            ],
        ];

        $createdRoles = [];
        $existingRoles = [];

        foreach ($allRoles as $roleName => $roleData) {
            try {
                $role = Role::firstOrCreate(
                    ['name' => $roleName],
                    [
                        'guard_name' => 'web',
                        'label' => $roleData['label'] ?? null,
                        'description' => $roleData['description'] ?? null,
                        'color' => $roleData['color'] ?? null,
                    ]
                );

                // Update if exists but missing data
                if (!$role->wasRecentlyCreated && (!$role->label || !$role->description)) {
                    $role->update([
                        'label' => $roleData['label'] ?? $role->label,
                        'description' => $roleData['description'] ?? $role->description,
                        'color' => $roleData['color'] ?? $role->color,
                    ]);
                }

                if ($role->wasRecentlyCreated) {
                    $createdRoles[] = $roleName;
                    $this->line("  ✓ Created: <fg=green>{$roleName}</fg=green>");
                    $this->line("     Label: {$roleData['label']}");
                } else {
                    $existingRoles[] = $roleName;
                    $this->line("  ℹ Exists: <fg=blue>{$roleName}</fg=blue>");
                }
            } catch (\Exception $e) {
                $this->line("  ✗ Error creating {$roleName}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('📊 Summary:');
        $this->line("  ✓ Created: " . count($createdRoles) . " new role(s)");
        $this->line("  ℹ Already existed: " . count($existingRoles) . " role(s)");
        $this->line("  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("  Total roles: " . (count($createdRoles) + count($existingRoles)));

        $this->newLine();
        $this->info('✅ Role creation completed!');

        // Optional: Show role hierarchy
        $this->showRoleHierarchy();
    }

    /**
     * Show which roles have access to which profiles
     */
    protected function showRoleHierarchy()
    {
        $this->newLine();
        $this->info('📋 Role Access Hierarchy:');
        $this->newLine();

        $hierarchy = [
            'Manager Profile' => ['super-admin', 'admin', 'manager'],
            'Call Center Profile' => ['super-admin', 'admin', 'callcenter-manager', 'callcenter-agent'],
            'Inventory Profile' => ['super-admin', 'admin', 'inventory-manager', 'inventory-staff'],
            'Warehouse Profile' => ['super-admin', 'admin', 'inventory-manager', 'inventory-staff'],
            'Shop Profile' => ['super-admin', 'admin', 'shop-manager', 'shop-staff'],
            'Administrative Profile' => ['super-admin', 'admin', 'administrative'],
        ];

        foreach ($hierarchy as $profile => $roles) {
            $this->line("  <fg=cyan>{$profile}:</fg=cyan>");
            foreach ($roles as $role) {
                $this->line("    • {$role}");
            }
            $this->newLine();
        }

        $this->comment('💡 Use: php artisan roles:assign <user_id> <role_name>');
    }
}
