<?php

use App\Http\Controllers\Weapons\DashboardController;

Route::group(['prefix' => 'weapons', 'middleware' => []], function () {

    Route::get('/dashboard/v4', [DashboardController::class, 'dashboard'])->name('weapons.dashboard.v4');
    Route::get('/dashboard/v1', [DashboardController::class, 'dashboardV1'])->name('weapons.dashboard.v1');
    Route::get('/dashboard/v2', [DashboardController::class, 'dashboardV2'])->name('weapons.dashboard.v2');
    Route::get('/dashboard/v3', [DashboardController::class, 'dashboardV3'])->name('weapons.dashboard.v3');
    Route::get('/', [DashboardController::class, 'dashboardV4'])->name('weapons.dashboard');

});
