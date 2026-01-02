<?php

use Illuminate\Support\Facades\Route;
use Modules\Role\Http\Controllers\PermissionController;
use Modules\Role\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| Roles Settings Web Routes
|--------------------------------------------------------------------------
|
| Rutas para la gestión de vistas de roles y permisos
| SOLO GET para renderizar vistas. POST, PUT, DELETE están en routes/api.php
| Middleware, prefix y name base ya están definidos en RoleServiceProvider
|
*/

// Roles routes
Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/create', [RoleController::class, 'create'])->name('create');
    Route::get('/{role}/show', [RoleController::class, 'show'])->name('show');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
    Route::get('/{role}/permissions', [RoleController::class, 'showPermissions'])->name('show.permissions');
    Route::get('/{role}/users', [RoleController::class, 'showUsers'])->name('show.users');
});

// Permissions routes
Route::prefix('permissions')->name('permissions.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::get('/create', [PermissionController::class, 'create'])->name('create');
    Route::get('/edit/{id}', [PermissionController::class, 'edit'])->name('edit');
});
