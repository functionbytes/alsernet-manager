<?php

namespace Modules\Warehouse\Policies;

use App\Models\User;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseInventoryOperation;

class WarehouseInventoryOperationPolicy
{
    /**
     * Determine whether the user can view any inventory operation.
     */
    public function viewAny(User $user): bool
    {
        return $this->userHasWarehousePermission($user, 'warehouse.manage');
    }

    /**
     * Determine whether the user can view the inventory operation.
     * User must be assigned to the operation's warehouse or be the creator.
     */
    public function view(User $user, WarehouseInventoryOperation $operation): bool
    {
        if ($this->userHasWarehousePermission($user, 'warehouse.manage')) {
            return $this->isAssignedToWarehouse($user, $operation->warehouse);
        }

        return $operation->created_by === $user->id;
    }

    /**
     * Determine whether the user can create inventory operations.
     * Only managers with warehouse.manage permission.
     */
    public function create(User $user): bool
    {
        return $this->userHasWarehousePermission($user, 'warehouse.manage');
    }

    /**
     * Determine whether the user can start an operation in a warehouse.
     * Only managers with warehouse.manage permission.
     */
    public function canStartOperation(User $user, Warehouse $warehouse): bool
    {
        if (!$this->userHasWarehousePermission($user, 'warehouse.manage')) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $warehouse);
    }

    /**
     * Determine whether the user can validate an inventory operation.
     * User must be assigned to the operation's warehouse.
     */
    public function canValidate(User $user, WarehouseInventoryOperation $operation): bool
    {
        return $this->isAssignedToWarehouse($user, $operation->warehouse);
    }

    /**
     * Determine whether the user can close an inventory operation.
     * Either the operation creator or a manager with warehouse.manage permission.
     */
    public function canClose(User $user, WarehouseInventoryOperation $operation): bool
    {
        if ($operation->created_by === $user->id) {
            return true;
        }

        return $this->userHasWarehousePermission($user, 'warehouse.manage');
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
