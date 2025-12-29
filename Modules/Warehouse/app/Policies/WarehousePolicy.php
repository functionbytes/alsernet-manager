<?php

namespace Modules\Warehouse\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Modules\Warehouse\Entities\Warehouse;

class WarehousePolicy
{
    /**
     * Determine whether the user can view any model.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Warehouse $warehouse): Response
    {
        if ($user->hasRole('super-admin')) {
            return Response::allow();
        }

        if (! $user->hasPermissionTo('warehouse.manage')) {
            return Response::deny('No tienes permiso para ver almacenes.');
        }

        if (! $this->isAssignedToWarehouse($user, $warehouse)) {
            return Response::deny('No estás asignado a este almacén.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can manage users for the warehouse.
     */
    public function canManageUsers(User $user, Warehouse $warehouse): Response
    {
        if ($user->hasRole('super-admin')) {
            return Response::allow();
        }

        if (! $user->hasPermissionTo('warehouse.manage')) {
            return Response::deny('No tienes permiso para gestionar usuarios de almacenes.');
        }

        if (! $this->isAssignedToWarehouse($user, $warehouse)) {
            return Response::deny('No estás asignado a este almacén.');
        }

        return Response::allow();
    }

    /**
     * Check if the user is assigned to the warehouse.
     */
    private function isAssignedToWarehouse(User $user, Warehouse $warehouse): bool
    {
        return $warehouse->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if the user has a specific warehouse permission.
     */
    private function userHasWarehousePermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission) || $user->hasRole('super-admin');
    }
}
