<?php

namespace App\Helpers;

use App\Http\Controllers\Managers\Users\UsersController;
use Illuminate\Support\Facades\Route;

/**
 * Route Helper - Provides 3 approaches to define user management routes
 *
 * This helper simplifies route definition across different profiles
 * by providing 3 tested approaches to user management route registration.
 */
class RouteHelper
{
    /**
     * APPROACH 1: Middleware-Based Access Control (RECOMMENDED)
     *
     * Uses CheckRolesAndPermissions middleware for centralized permission checking.
     * Best for consistency across all profiles.
     */
    public static function registerUserRoutesMiddlewareBased(string $profile = 'manager')
    {
        Route::prefix('users')
            ->name('users.')
            ->middleware(['auth', "check.roles.permissions:{$profile}"])
            ->group(function () {
                Route::get('/', [UsersController::class, 'index'])->name('index');
                Route::get('/create', [UsersController::class, 'create'])->name('create');
                Route::post('/store', [UsersController::class, 'store'])->name('store');
                Route::get('/{uid}', [UsersController::class, 'view'])->name('view');
                Route::get('/{uid}/edit', [UsersController::class, 'edit'])->name('edit');
                Route::post('/update', [UsersController::class, 'update'])->name('update');
                Route::get('/{uid}/destroy', [UsersController::class, 'destroy'])->name('destroy');
            });
    }

    /**
     * APPROACH 2: Separate Routing Per Profile
     *
     * Allows each profile to have custom user management routes
     * with profile-specific middleware and permissions.
     */
    public static function registerUserRoutesSeparate(string $profile = 'manager', array $allowedRoles = [])
    {
        $defaultRoles = [
            'manager' => ['super-admin', 'admin', 'manager'],
            'callcenter' => ['super-admin', 'admin', 'callcenter-manager'],
            'shop' => ['super-admin', 'admin', 'shop-manager'],
            'warehouse' => ['super-admin', 'admin', 'inventory-manager'],
            'administrative' => ['super-admin', 'admin', 'administrative'],
        ];

        $roles = !empty($allowedRoles) ? $allowedRoles : ($defaultRoles[$profile] ?? []);

        Route::prefix('users')
            ->name('users.')
            ->middleware([
                'auth',
                function ($request, $next) use ($roles) {
                    if (!auth()->user()?->hasAnyRole($roles)) {
                        abort(403, 'Unauthorized to access user management');
                    }
                    return $next($request);
                }
            ])
            ->group(function () {
                Route::get('/', [UsersController::class, 'index'])->name('index');
                Route::get('/create', [UsersController::class, 'create'])->name('create');
                Route::post('/store', [UsersController::class, 'store'])->name('store');
                Route::get('/{uid}', [UsersController::class, 'view'])->name('view');
                Route::get('/{uid}/edit', [UsersController::class, 'edit'])->name('edit');
                Route::post('/update', [UsersController::class, 'update'])->name('update');
                Route::get('/{uid}/destroy', [UsersController::class, 'destroy'])->name('destroy');
            });
    }

    /**
     * APPROACH 3: Permission-Based Access Control
     *
     * Uses Spatie permissions for fine-grained access control.
     * Requires permissions to be properly configured in the database.
     */
    public static function registerUserRoutesPermissionBased()
    {
        Route::middleware(['auth'])
            ->prefix('users')
            ->name('users.')
            ->group(function () {
                // View all users
                Route::get('/', [UsersController::class, 'index'])
                    ->middleware('can:users.view')
                    ->name('index');

                // Create new user
                Route::get('/create', [UsersController::class, 'create'])
                    ->middleware('can:users.create')
                    ->name('create');

                Route::post('/store', [UsersController::class, 'store'])
                    ->middleware('can:users.create')
                    ->name('store');

                // View single user
                Route::get('/{uid}', [UsersController::class, 'view'])
                    ->middleware('can:users.view')
                    ->name('view');

                // Edit user
                Route::get('/{uid}/edit', [UsersController::class, 'edit'])
                    ->middleware('can:users.update')
                    ->name('edit');

                Route::post('/update', [UsersController::class, 'update'])
                    ->middleware('can:users.update')
                    ->name('update');

                // Delete user
                Route::get('/{uid}/destroy', [UsersController::class, 'destroy'])
                    ->middleware('can:users.delete')
                    ->name('destroy');
            });
    }

    /**
     * Get explanation of each approach
     */
    public static function getApproachDescriptions(): array
    {
        return [
            1 => [
                'name' => 'Middleware-Based Access Control',
                'description' => 'Uses CheckRolesAndPermissions middleware for centralized permission checking',
                'pros' => [
                    'Centralized permission checking',
                    'Consistent behavior across profiles',
                    'Easy to audit and modify',
                    'Best for role-based access',
                ],
                'cons' => [
                    'Depends on middleware being properly configured',
                ],
                'method' => 'registerUserRoutesMiddlewareBased',
            ],
            2 => [
                'name' => 'Separate Routing Per Profile',
                'description' => 'Each profile has custom user management routes',
                'pros' => [
                    'Maximum flexibility per profile',
                    'Can customize routes for specific profiles',
                    'Better for complex permission scenarios',
                ],
                'cons' => [
                    'Code duplication across profiles',
                    'Harder to maintain consistency',
                ],
                'method' => 'registerUserRoutesSeparate',
            ],
            3 => [
                'name' => 'Permission-Based Access Control',
                'description' => 'Uses Spatie permissions for fine-grained access control',
                'pros' => [
                    'Fine-grained control per action',
                    'Can check specific permissions',
                    'Works well with Spatie system',
                ],
                'cons' => [
                    'Requires permission setup in database',
                    'More overhead per request',
                    'Need to manage permission relationships',
                ],
                'method' => 'registerUserRoutesPermissionBased',
            ],
        ];
    }
}
