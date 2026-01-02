<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\PermissionController;
use Modules\Role\Http\Controllers\RoleController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::group(['prefix' => 'roles', 'name' => 'roles.'], function () {

    // CRUD de roles básico
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/create', [RoleController::class, 'create'])->name('create');
    Route::post('/store', [RoleController::class, 'store'])->name('store');
    Route::get('/{role}/show', [RoleController::class, 'show'])->name('show');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
    Route::post('/{role}/update', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');

    // Gestión de permisos
    Route::get('/{role}/permissions', [RoleController::class, 'showPermissions'])->name('show.permissions');
    Route::post('/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('update.permissions');

    // Gestión de usuarios asignados a rol
    Route::get('/{role}/users', [RoleController::class, 'showUsers'])->name('show.users');
    Route::post('/{role}/users/assign', [RoleController::class, 'assignUsers'])->name('assign.users');
    Route::delete('/{role}/users/{user}', [RoleController::class, 'removeUser'])->name('remove.user');

    // Métodos avanzados
    Route::post('/{role}/duplicate', [RoleController::class, 'duplicate'])->name('duplicate');

});

Route::group(['prefix' => 'permissions', 'name' => 'permissions.'], function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::get('/create', [PermissionController::class, 'create'])->name('create');
    Route::post('/store', [PermissionController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [PermissionController::class, 'edit'])->name('edit');
    Route::post('/update', [PermissionController::class, 'update'])->name('update');
    Route::get('/destroy/{id}', [PermissionController::class, 'destroy'])->name('destroy');
    });
});
