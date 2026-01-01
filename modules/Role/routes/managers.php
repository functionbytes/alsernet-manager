<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\Managers\Settings\Roles\PermissionController;
use Modules\Role\Http\Controllers\Managers\Settings\Roles\RoleController;

Route::group(['prefix' => 'manager/settings/roles'], function () {

    // CRUD de roles básico
    Route::get('/', [RoleController::class, 'index'])->name('manager.roles');
    Route::get('/create', [RoleController::class, 'create'])->name('manager.roles.create');
    Route::post('/store', [RoleController::class, 'store'])->name('manager.roles.store');
    Route::get('/{role}/show', [RoleController::class, 'show'])->name('manager.roles.show');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('manager.roles.edit');
    Route::post('/{role}/update', [RoleController::class, 'update'])->name('manager.roles.update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('manager.roles.destroy');

    // Gestión de permisos
    Route::get('/{role}/permissions', [RoleController::class, 'showPermissions'])->name('manager.roles.show.permissions');
    Route::post('/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('manager.roles.update.permissions');

    // Gestión de usuarios asignados a rol
    Route::get('/{role}/users', [RoleController::class, 'showUsers'])->name('manager.roles.show.users');
    Route::post('/{role}/users/assign', [RoleController::class, 'assignUsers'])->name('manager.roles.assign.users');
    Route::delete('/{role}/users/{user}', [RoleController::class, 'removeUser'])->name('manager.roles.remove.user');

    // Métodos avanzados
    Route::post('/{role}/duplicate', [RoleController::class, 'duplicate'])->name('manager.roles.duplicate');

});

Route::group(['prefix' => 'manager/settings/permissions'], function () {
    Route::get('/', [PermissionController::class, 'index'])->name('manager.permissions');
    Route::get('/create', [PermissionController::class, 'create'])->name('manager.permissions.create');
    Route::post('/store', [PermissionController::class, 'store'])->name('manager.permissions.store');
    Route::get('/edit/{id}', [PermissionController::class, 'edit'])->name('manager.permissions.edit');
    Route::post('/update', [PermissionController::class, 'update'])->name('manager.permissions.update');
    Route::get('/destroy/{id}', [PermissionController::class, 'destroy'])->name('manager.permissions.destroy');
});
