<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UsersController;

Route::group(['prefix' => 'users'], function () {
    Route::get('/', [UsersController::class, 'index'])->name('users.index');
    Route::get('/create', [UsersController::class, 'create'])->name('users.create');
    Route::get('/{uid}', [UsersController::class, 'view'])->name('users.show');
    Route::get('/{uid}/edit', [UsersController::class, 'edit'])->name('users.edit');
});
