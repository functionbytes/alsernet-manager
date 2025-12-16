<?php

namespace App\Permissions\V1;

use App\Models\User;

final class Abilities {
    // ===== TICKETS =====
    public const CreateTicket = 'ticket:create';
    public const UpdateTicket = 'ticket:update';
    public const ReplaceTicket = 'ticket:replace';
    public const DeleteTicket = 'ticket:delete';
    public const CreateOwnTicket = 'ticket:own:create';
    public const UpdateOwnTicket = 'ticket:own:update';
    public const DeleteOwnTicket = 'ticket:own:delete';
    public const AssignTicket = 'ticket:assign';
    public const CloseTicket = 'ticket:close';

    // ===== USERS =====
    public const CreateUser = 'user:create';
    public const UpdateUser = 'user:update';
    public const ReplaceUser = 'user:replace';
    public const DeleteUser = 'user:delete';
    public const ViewUsers = 'user:view';
    public const ExportUsers = 'user:export';

    // ===== ROLES & PERMISSIONS =====
    public const ViewRoles = 'role:view';
    public const CreateRoles = 'role:create';
    public const UpdateRoles = 'role:update';
    public const DeleteRoles = 'role:delete';
    public const AssignPermissions = 'role:assign-permissions';
    public const AssignUsers = 'role:assign-users';

    // ===== WAREHOUSE =====
    public const ViewWarehouse = 'warehouse:view';
    public const CreateWarehouse = 'warehouse:create';
    public const UpdateWarehouse = 'warehouse:update';
    public const DeleteWarehouse = 'warehouse:delete';

    // ===== RETURNS =====
    public const ViewReturns = 'return:view';
    public const CreateReturns = 'return:create';
    public const UpdateReturns = 'return:update';
    public const DeleteReturns = 'return:delete';
    public const ApproveReturns = 'return:approve';

    // ===== REPORTS =====
    public const ViewReports = 'report:view';
    public const ExportReports = 'report:export';
    public const CreateReports = 'report:create';

    // ===== SETTINGS =====
    public const ViewSettings = 'settings:view';
    public const EditSettings = 'settings:edit';
    public const SystemSettings = 'settings:system';

    /**
     * Get all available abilities grouped by category
     */
    public static function getAllAbilitiesGrouped(): array
    {
        return [
            'tickets' => [
                self::CreateTicket => 'Crear Tickets',
                self::UpdateTicket => 'Actualizar Tickets',
                self::ReplaceTicket => 'Reemplazar Tickets',
                self::DeleteTicket => 'Eliminar Tickets',
                self::AssignTicket => 'Asignar Tickets',
                self::CloseTicket => 'Cerrar Tickets',
                self::CreateOwnTicket => 'Crear Propios Tickets',
                self::UpdateOwnTicket => 'Actualizar Propios Tickets',
                self::DeleteOwnTicket => 'Eliminar Propios Tickets',
            ],
            'users' => [
                self::ViewUsers => 'Ver Usuarios',
                self::CreateUser => 'Crear Usuarios',
                self::UpdateUser => 'Actualizar Usuarios',
                self::ReplaceUser => 'Reemplazar Usuarios',
                self::DeleteUser => 'Eliminar Usuarios',
                self::ExportUsers => 'Exportar Usuarios',
            ],
            'roles' => [
                self::ViewRoles => 'Ver Roles',
                self::CreateRoles => 'Crear Roles',
                self::UpdateRoles => 'Actualizar Roles',
                self::DeleteRoles => 'Eliminar Roles',
                self::AssignPermissions => 'Asignar Permisos',
                self::AssignUsers => 'Asignar Usuarios',
            ],
            'warehouse' => [
                self::ViewWarehouse => 'Ver Almacén',
                self::CreateWarehouse => 'Crear en Almacén',
                self::UpdateWarehouse => 'Actualizar Almacén',
                self::DeleteWarehouse => 'Eliminar Almacén',
            ],
            'returns' => [
                self::ViewReturns => 'Ver Devoluciones',
                self::CreateReturns => 'Crear Devoluciones',
                self::UpdateReturns => 'Actualizar Devoluciones',
                self::DeleteReturns => 'Eliminar Devoluciones',
                self::ApproveReturns => 'Aprobar Devoluciones',
            ],
            'reports' => [
                self::ViewReports => 'Ver Reportes',
                self::ExportReports => 'Exportar Reportes',
                self::CreateReports => 'Crear Reportes',
            ],
            'settings' => [
                self::ViewSettings => 'Ver Configuración',
                self::EditSettings => 'Editar Configuración',
                self::SystemSettings => 'Configuración del Sistema',
            ],
        ];
    }

    /**
     * Get abilities for a specific user based on their role
     */
    public static function getAbilities(User $user): array
    {
        if ($user->is_manager) {
            return array_merge(
                self::getAllAdminAbilities(),
                self::getManagerAbilities()
            );
        } else {
            return self::getBasicUserAbilities();
        }
    }

    /**
     * Get all admin abilities
     */
    public static function getAllAdminAbilities(): array
    {
        return array_keys(array_merge(
            ...array_values(self::getAllAbilitiesGrouped())
        ));
    }

    /**
     * Get manager-level abilities
     */
    public static function getManagerAbilities(): array
    {
        return [
            self::ViewWarehouse,
            self::CreateWarehouse,
            self::UpdateWarehouse,
            self::ViewReturns,
            self::CreateReturns,
            self::UpdateReturns,
            self::ApproveReturns,
            self::ViewReports,
        ];
    }

    /**
     * Get basic user abilities
     */
    public static function getBasicUserAbilities(): array
    {
        return [
            self::CreateOwnTicket,
            self::UpdateOwnTicket,
            self::DeleteOwnTicket,
        ];
    }
}
