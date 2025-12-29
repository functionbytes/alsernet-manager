<?php

namespace Modules\Warehouse\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseFloor;

class WarehouseFloorPolicy
{
    /**
     * Determine whether the user can view any floor.
     */
    public function viewAny(User $user): bool
    {
        return $this->userHasWarehousePermission($user, 'warehouse.manage');
    }

    /**
     * Determine whether the user can view the floor.
     * User must be assigned to the warehouse containing this floor.
     */
    public function view(User $user, WarehouseFloor $floor): bool
    {
        if (!$this->userHasWarehousePermission($user, 'warehouse.manage')) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $floor->warehouse);
    }

    /**
     * Determine whether the user can create floors.
     */
    public function create(User $user): bool
    {
        return $this->userHasWarehousePermission($user, 'warehouse.manage');
    }

    /**
     * Determine whether the user can update the floor.
     */
    public function update(User $user, WarehouseFloor $floor): bool
    {
        if (!$this->userHasWarehousePermission($user, 'warehouse.manage')) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $floor->warehouse);
    }

    /**
     * Determine whether the user can delete the floor.
     */
    public function delete(User $user, WarehouseFloor $floor): bool
    {
        if (!$this->userHasWarehousePermission($user, 'warehouse.manage')) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $floor->warehouse);
    }

    /**
     * Check if user is assigned to the warehouse.
     */
    private function isAssignedToWarehouse(User $user, Warehouse $warehouse): bool
    {
        return $user->warehouses()
            ->where('warehouse_id', $warehouse->id)
            ->exists();
    }

    /**
     * Check if user has the required warehouse permission or is super-admin.
     */
    private function userHasWarehousePermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission) || $user->hasRole('super-admin');
    }
}
