<?php

use App\Http\Controllers\Accountings\DashboardController;

Route::group(['prefix' => 'accounting', 'middleware' => []], function () {

    Route::get('/dashboard/v4', [DashboardController::class, 'dashboard'])->name('accounting.dashboard.v4');
    Route::get('/dashboard/v1', [DashboardController::class, 'dashboardV1'])->name('accounting.dashboard.v1');
    Route::get('/dashboard/v2', [DashboardController::class, 'dashboardV2'])->name('accounting.dashboard.v2');
    Route::get('/dashboard/v3', [DashboardController::class, 'dashboardV3'])->name('accounting.dashboard.v3');
    Route::get('/', [DashboardController::class, 'dashboardV4'])->name('accounting.dashboard');

});
