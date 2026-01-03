<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UsersController;

Route::group(['prefix' => 'users'], function () {
    Route::post('/', [UsersController::class, 'store'])->name('users.store');
    Route::post('/update', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/{uid}', [UsersController::class, 'destroy'])->name('users.destroy');
});
