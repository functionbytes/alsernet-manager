<?php

namespace Modules\Warehouse\Policies;

use App\Models\User;
use Modules\Warehouse\Entities\WarehouseLocationStyle;

class WarehouseLocationStylePolicy
{
    /**
     * Determine whether the user can view any location style.
     * Only theme and super-admin can view styles.
     */
    public function viewAny(User $user): bool
    {
        return $this->userHasStylePermission($user);
    }

    /**
     * Determine whether the user can view the location style.
     * Only theme and super-admin.
     */
    public function view(User $user, WarehouseLocationStyle $style): bool
    {
        return $this->userHasStylePermission($user);
    }

    /**
     * Determine whether the user can create location styles.
     * Only theme and super-admin.
     */
    public function create(User $user): bool
    {
        return $this->userHasStylePermission($user);
    }

    /**
     * Determine whether the user can update the location style.
     * Only theme and super-admin.
     */
    public function update(User $user, WarehouseLocationStyle $style): bool
    {
        return $this->userHasStylePermission($user);
    }

    /**
     * Determine whether the user can delete the location style.
     * Only theme and super-admin.
     */
    public function delete(User $user, WarehouseLocationStyle $style): bool
    {
        return $this->userHasStylePermission($user);
    }

    /**
     * Check if user has the required location style management permission or is super-admin.
     */
    private function userHasStylePermission(User $user): bool
    {
        return $user->hasPermissionTo('warehouse.style.manage') || $user->hasRole('super-admin');
    }
}
