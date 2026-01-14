<?php

namespace Modules\Warehouse\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Modules\Warehouse\Entities\Warehouse;
use Modules\Warehouse\Entities\WarehouseInventorySlot;

class WarehouseInventorySlotPolicy
{
    /**
     * Determine whether the user can view any inventory slot.
     */
    public function viewAny(User $user): bool
    {
        return $this->userHasWarehousePermission($user, 'warehouse.manage');
    }

    /**
     * Determine whether the user can view the inventory slot.
     */
    public function view(User $user, WarehouseInventorySlot $slot): bool
    {
        if (!$this->userHasWarehousePermission($user, 'warehouse.manage')) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $slot->location->warehouse);
    }

    /**
     * Determine whether the user can create inventory slots.
     */
    public function create(User $user): bool
    {
        return $this->userHasWarehousePermission($user, 'warehouse.manage');
    }

    /**
     * Determine whether the user can update the inventory slot.
     */
    public function update(User $user, WarehouseInventorySlot $slot): bool
    {
        if (!$this->userHasWarehousePermission($user, 'warehouse.manage')) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $slot->location->warehouse);
    }

    /**
     * Determine whether the user can add quantity to an inventory slot.
     * User must have 'warehouse.slot.add' permission and be assigned to warehouse.
     */
    public function canAddQuantity(User $user, WarehouseInventorySlot $slot): bool
    {
        if (!$user->hasPermissionTo('warehouse.slot.add') && !$user->hasRole('super-admin')) {
            return false;
        }

        $warehouseUser = $this->getWarehouseUser($user, $slot->location->warehouse);

        if (!$warehouseUser || !$warehouseUser->can_inventory) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $slot->location->warehouse);
    }

    /**
     * Determine whether the user can subtract quantity from an inventory slot.
     * User must have 'warehouse.slot.add' permission, be assigned to warehouse,
     * and available quantity must be sufficient.
     */
    public function canSubtractQuantity(User $user, WarehouseInventorySlot $slot): bool
    {
        if (!$user->hasPermissionTo('warehouse.slot.add') && !$user->hasRole('super-admin')) {
            return false;
        }

        $warehouseUser = $this->getWarehouseUser($user, $slot->location->warehouse);

        if (!$warehouseUser || !$warehouseUser->can_inventory) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $slot->location->warehouse);
    }

    /**
     * Determine whether the user can transfer inventory between slots.
     * Both slots must be in the same warehouse and user must have transfer permission.
     */
    public function canTransfer(User $user, WarehouseInventorySlot $fromSlot, WarehouseInventorySlot $toSlot): bool
    {
        if (!$user->hasPermissionTo('warehouse.slot.transfer') && !$user->hasRole('super-admin')) {
            return false;
        }

        // Both slots must be in the same warehouse
        if ($fromSlot->location->warehouse_id !== $toSlot->location->warehouse_id) {
            return false;
        }

        $warehouseUser = $this->getWarehouseUser($user, $fromSlot->location->warehouse);

        if (!$warehouseUser || !$warehouseUser->can_transfer) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $fromSlot->location->warehouse);
    }

    /**
     * Determine whether the user can clear an inventory slot.
     * User must have 'warehouse.slot.add' permission and be assigned to warehouse.
     */
    public function canClear(User $user, WarehouseInventorySlot $slot): bool
    {
        if (!$user->hasPermissionTo('warehouse.slot.add') && !$user->hasRole('super-admin')) {
            return false;
        }

        $warehouseUser = $this->getWarehouseUser($user, $slot->location->warehouse);

        if (!$warehouseUser || !$warehouseUser->can_inventory) {
            return false;
        }

        return $this->isAssignedToWarehouse($user, $slot->location->warehouse);
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
     * Get the warehouse user assignment with pivot data.
     */
    private function getWarehouseUser(User $user, Warehouse $warehouse): ?object
    {
        return $user->warehouses()
            ->where('warehouse_id', $warehouse->id)
            ->first();
    }

    /**
     * Check if user has the required warehouse permission or is super-admin.
     */
    private function userHasWarehousePermission(User $user, string $permission): bool
    {
        return $user->hasPermissionTo($permission) || $user->hasRole('super-admin');
    }
}
