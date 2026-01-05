<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\PermissionController;
use Modules\Role\Http\Controllers\RoleController;

// Roles management routes
Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/create', [RoleController::class, 'create'])->name('create');
    Route::get('{role}/show', [RoleController::class, 'show'])->name('show');
    Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
    Route::get('{role}/modules', [RoleController::class, 'showModules'])->name('show.modules');
    Route::get('{role}/permissions', [RoleController::class, 'showPermissions'])->name('show.permissions');
    Route::get('{role}/users', [RoleController::class, 'showUsers'])->name('show.users');
});

// Permissions management routes
Route::prefix('permissions')->name('permissions.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::get('/create', [PermissionController::class, 'create'])->name('create');
    Route::get('{id}/edit', [PermissionController::class, 'edit'])->name('edit');
});
