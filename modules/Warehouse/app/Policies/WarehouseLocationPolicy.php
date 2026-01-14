<?php

namespace Modules\Warehouse\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Modules\Warehouse\Entities\WarehouseLocation;

class WarehouseLocationPolicy
{
    /**
     * Determine whether the user can view any model.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('warehouse.manage') ||
               $user->hasPermissionTo('warehouse.inventory') ||
               $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WarehouseLocation $location): Response
    {
        if ($user->hasRole('super-admin')) {
            return Response::allow();
        }

        if (! $user->hasPermissionTo('warehouse.manage') && ! $user->hasPermissionTo('warehouse.inventory')) {
            return Response::deny('No tienes permiso para ver ubicaciones de almacén.');
        }

        if (! $this->isAssignedToWarehouse($user, $location)) {
            return Response::deny('No estás asignado al almacén de esta ubicación.');
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
    public function update(User $user, WarehouseLocation $location): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WarehouseLocation $location): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WarehouseLocation $location): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WarehouseLocation $location): bool
    {
        return $user->hasPermissionTo('warehouse.manage') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can add quantity to the location.
     */
    public function canAddQuantity(User $user, WarehouseLocation $location): Response
    {
        if ($user->hasRole('super-admin')) {
            return Response::allow();
        }

        if (! $user->hasPermissionTo('warehouse.inventory')) {
            return Response::deny('No tienes permiso para realizar operaciones de inventario.');
        }

        if (! $this->isAssignedToWarehouse($user, $location)) {
            return Response::deny('No estás asignado al almacén de esta ubicación.');
        }

        if (! $this->hasInventoryPermission($user, $location)) {
            return Response::deny('No tienes permiso de inventario para este almacén.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can subtract quantity from the location.
     */
    public function canSubtractQuantity(User $user, WarehouseLocation $location): Response
    {
        if ($user->hasRole('super-admin')) {
            return Response::allow();
        }

        if (! $user->hasPermissionTo('warehouse.inventory')) {
            return Response::deny('No tienes permiso para realizar operaciones de inventario.');
        }

        if (! $this->isAssignedToWarehouse($user, $location)) {
            return Response::deny('No estás asignado al almacén de esta ubicación.');
        }

        if (! $this->hasInventoryPermission($user, $location)) {
            return Response::deny('No tienes permiso de inventario para este almacén.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can transfer from the location.
     */
    public function canTransferFrom(User $user, WarehouseLocation $location): Response
    {
        if ($user->hasRole('super-admin')) {
            return Response::allow();
        }

        if (! $user->hasPermissionTo('warehouse.inventory')) {
            return Response::deny('No tienes permiso para realizar transferencias.');
        }

        if (! $this->isAssignedToWarehouse($user, $location)) {
            return Response::deny('No estás asignado al almacén de esta ubicación.');
        }

        if (! $this->hasTransferPermission($user, $location)) {
            return Response::deny('No tienes permiso de transferencia para este almacén.');
        }

        return Response::allow();
    }

    /**
     * Check if the user is assigned to the warehouse of the location.
     */
    private function isAssignedToWarehouse(User $user, WarehouseLocation $location): bool
    {
        if (! $location->warehouse) {
            return false;
        }

        return $location->warehouse->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if the user has inventory permission for the warehouse.
     */
    private function hasInventoryPermission(User $user, WarehouseLocation $location): bool
    {
        if (! $location->warehouse) {
            return false;
        }

        return $location->warehouse->users()
            ->where('user_id', $user->id)
            ->wherePivot('can_inventory', true)
            ->exists();
    }

    /**
     * Check if the user has transfer permission for the warehouse.
     */
    private function hasTransferPermission(User $user, WarehouseLocation $location): bool
    {
        if (! $location->warehouse) {
            return false;
        }

        return $location->warehouse->users()
            ->where('user_id', $user->id)
            ->wherePivot('can_transfer', true)
            ->exists();
    }
}
