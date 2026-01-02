<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\PermissionController;
use Modules\Role\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| Roles Settings API Routes
|--------------------------------------------------------------------------
|
| Rutas API para la gestión de roles y permisos en el panel de administración
| Operaciones: POST (crear), PUT (actualizar), DELETE (eliminar)
|
*/

// Roles management operations (API)
Route::prefix('roles')->name('roles.')->group(function () {
    Route::post('/store', [RoleController::class, 'store'])->name('store');
    Route::put('/{role}/update', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    Route::post('/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('update.permissions');
    Route::post('/{role}/users/assign', [RoleController::class, 'assignUsers'])->name('assign.users');
    Route::delete('/{role}/users/{user}', [RoleController::class, 'removeUser'])->name('remove.user');
    Route::post('/{role}/duplicate', [RoleController::class, 'duplicate'])->name('duplicate');
});

// Permissions management operations (API)
Route::prefix('permissions')->name('permissions.')->group(function () {
    Route::post('/store', [PermissionController::class, 'store'])->name('store');
    Route::put('/update', [PermissionController::class, 'update'])->name('update');
    Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
});
