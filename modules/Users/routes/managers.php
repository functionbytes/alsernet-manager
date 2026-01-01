<?php

use Modules\Users\Http\Controllers\Managers\UsersController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/users'], function () {
    Route::get('/', [UsersController::class, 'index'])->name('manager.users');
    Route::get('/create', [UsersController::class, 'create'])->name('manager.users.create');
    Route::post('/store', [UsersController::class, 'store'])->name('manager.users.store');
    Route::get('/{uid}', [UsersController::class, 'view'])->name('manager.users.view');
    Route::get('/{uid}/edit', [UsersController::class, 'edit'])->name('manager.users.edit');
    Route::post('/update', [UsersController::class, 'update'])->name('manager.users.update');
    Route::get('/{uid}/destroy', [UsersController::class, 'destroy'])->name('manager.users.destroy');
});
