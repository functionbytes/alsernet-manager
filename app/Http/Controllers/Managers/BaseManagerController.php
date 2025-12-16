<?php

namespace App\Http\Controllers\Managers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseManagerController extends Controller
{
    /**
     * Return a successful JSON response
     */
    protected function success(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            ...$data
        ]);
    }

    /**
     * Return an error JSON response
     */
    protected function error(string $message, array $data = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            ...$data
        ], $status);
    }

    /**
     * Get configured pagination number
     */
    protected function getPaginationPerPage(): int
    {
        return config('app.pagination.per_page', 20);
    }

    /**
     * Get available permissions grouped by category
     */
    protected function getAvailablePermissions(): array
    {
        return [
            'roles' => [
                'roles.view' => 'Ver Roles',
                'roles.create' => 'Crear Roles',
                'roles.edit' => 'Editar Roles',
                'roles.delete' => 'Eliminar Roles',
                'roles.assign.permissions' => 'Asignar Permisos',
                'roles.assign.users' => 'Asignar Usuarios',
            ],
            'users' => [
                'users.view' => 'Ver Usuarios',
                'users.create' => 'Crear Usuarios',
                'users.edit' => 'Editar Usuarios',
                'users.delete' => 'Eliminar Usuarios',
                'users.export' => 'Exportar Usuarios',
            ],
            'tickets' => [
                'tickets.view' => 'Ver Tickets',
                'tickets.create' => 'Crear Tickets',
                'tickets.edit' => 'Editar Tickets',
                'tickets.delete' => 'Eliminar Tickets',
                'tickets.assign' => 'Asignar Tickets',
                'tickets.close' => 'Cerrar Tickets',
            ],
            'reports' => [
                'reports.view' => 'Ver Reportes',
                'reports.export' => 'Exportar Reportes',
                'reports.create' => 'Crear Reportes',
            ],
            'settings' => [
                'settings.view' => 'Ver Configuración',
                'settings.edit' => 'Editar Configuración',
                'settings.system' => 'Configuración del Sistema',
            ],
            'warehouse' => [
                'warehouse.view' => 'Ver Almacén',
                'warehouse.create' => 'Crear en Almacén',
                'warehouse.edit' => 'Editar en Almacén',
                'warehouse.delete' => 'Eliminar en Almacén',
            ],
            'returns' => [
                'returns.view' => 'Ver Devoluciones',
                'returns.create' => 'Crear Devoluciones',
                'returns.edit' => 'Editar Devoluciones',
                'returns.delete' => 'Eliminar Devoluciones',
                'returns.approve' => 'Aprobar Devoluciones',
            ],
        ];
    }
}
