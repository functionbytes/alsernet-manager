<?php

/**
 * Shared User Management Routes
 *
 * These routes are included in each profile route file (managers, callcenters, shops, etc.)
 * to provide user management functionality with appropriate middleware and permissions.
 *
 * This file provides 3 approaches to access control:
 * 1. Middleware-based: Uses CheckRolesAndPermissions middleware
 * 2. Separate routing: Different route groups per profile
 * 3. Permission-based: Uses Spatie permissions directly
 */

use App\Http\Controllers\Managers\Users\UsersController;
use Illuminate\Support\Facades\Route;

/**
 * ============================================================
 * APPROACH 1: MIDDLEWARE-BASED ACCESS CONTROL (RECOMMENDED)
 * ============================================================
 *
 * Pros:
 * - Centralized permission checking
 * - Consistent behavior across profiles
 * - Easy to audit and modify
 *
 * Cons:
 * - Depends on middleware being properly configured
 *
 * Usage: Include this route group in each profile file
 */

// Route::group([
//     'prefix' => 'users',
//     'name' => 'users.',
//     'middleware' => ['auth', 'check.roles.permissions:manager']
// ], function () {
//     Route::get('/', [UsersController::class, 'index'])->name('index');
//     Route::get('/create', [UsersController::class, 'create'])->name('create');
//     Route::post('/store', [UsersController::class, 'store'])->name('store');
//     Route::get('/{uid}', [UsersController::class, 'view'])->name('view');
//     Route::get('/{uid}/edit', [UsersController::class, 'edit'])->name('edit');
//     Route::post('/update', [UsersController::class, 'update'])->name('update');
//     Route::get('/{uid}/destroy', [UsersController::class, 'destroy'])->name('destroy');
// });

/**
 * ============================================================
 * APPROACH 2: SEPARATE ROUTING PER PROFILE
 * ============================================================
 *
 * Pros:
 * - Maximum flexibility per profile
 * - Can customize routes for specific profiles
 * - Better for complex permission scenarios
 *
 * Cons:
 * - Code duplication across profiles
 * - Harder to maintain consistency
 *
 * Implementation: Create separate route groups in managers.php, callcenters.php, etc.
 * Example:
 *
 * Route::group(['prefix' => 'users', 'name' => 'users.'], function () {
 *     Route::middleware(['check.roles.permissions:manager'])->group(function () {
 *         Route::get('/', [UsersController::class, 'index'])->name('index');
 *         // ... other routes
 *     });
 * });
 */

/**
 * ============================================================
 * APPROACH 3: PERMISSION-BASED ACCESS CONTROL
 * ============================================================
 *
 * Pros:
 * - Fine-grained control
 * - Can check specific permissions per action
 * - Works well with Spatie permission system
 *
 * Cons:
 * - Requires permission setup in database
 * - More overhead per request
 * - Need to manage permission relationships
 *
 * Implementation: Use can:permission in route middleware
 * Example:
 *
 * Route::middleware(['auth'])->group(function () {
 *     Route::get('/users', [UsersController::class, 'index'])
 *         ->middleware('can:users.view')
 *         ->name('users.index');
 *
 *     Route::get('/users/create', [UsersController::class, 'create'])
 *         ->middleware('can:users.create')
 *         ->name('users.create');
 *     // ... etc
 * });
 */

/**
 * ============================================================
 * RECOMMENDED CONFIGURATION
 * ============================================================
 *
 * Use APPROACH 1 (Middleware-based) as it provides:
 * - Best balance of flexibility and maintainability
 * - Centralized control through CheckRolesAndPermissions
 * - Easy to add new profiles or modify existing ones
 * - Clear separation of concerns
 *
 * How to include this file in your profile route files:
 *
 * // In routes/managers.php:
 * Route::group(['prefix' => 'manager', 'middleware' => 'auth'], function () {
 *     require __DIR__ . '/shared/user-routes.php';
 *     // ... other routes
 * });
 *
 * // In routes/callcenters.php:
 * Route::group(['prefix' => 'callcenter', 'middleware' => 'auth'], function () {
 *     require __DIR__ . '/shared/user-routes.php';
 *     // ... other routes
 * });
 */
