<?php

use Modules\User\Http\Controllers\Managers\UsersController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'users'], function () {
    Route::get('/', [UsersController::class, 'index'])->name('users');
    Route::get('/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/store', [UsersController::class, 'store'])->name('users.store');
    Route::get('/{uid}', [UsersController::class, 'view'])->name('users.view');
    Route::get('/{uid}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::post('/update', [UsersController::class, 'update'])->name('users.update');
    Route::get('/{uid}/destroy', [UsersController::class, 'destroy'])->name('users.destroy');
});
