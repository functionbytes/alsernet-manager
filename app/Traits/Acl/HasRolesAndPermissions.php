<?php

namespace App\Traits\Acl;

use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

/**
 * Trait HasRolesAndPermissions
 *
 * Combines Spatie Permission traits for role and permission management.
 * Provides methods for checking, assigning, and revoking roles and permissions.
 *
 * @package App\Traits\Acl
 */
trait HasRolesAndPermissions
{
    use HasRoles;
    use HasPermissions;
}
