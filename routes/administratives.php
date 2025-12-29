<?php

use App\Http\Controllers\Administratives\DashboardController;

Route::group(['prefix' => 'administrative', 'middleware' => ['auth']], function () {

    Route::get('/dashboard/v4', [DashboardController::class, 'dashboard'])->name('administrative.dashboard.v4');
    Route::get('/dashboard/v1', [DashboardController::class, 'dashboardV1'])->name('administrative.dashboard.v1');
    Route::get('/dashboard/v2', [DashboardController::class, 'dashboardV2'])->name('administrative.dashboard.v2');
    Route::get('/dashboard/v3', [DashboardController::class, 'dashboardV3'])->name('administrative.dashboard.v3');
    Route::get('/', [DashboardController::class, 'dashboardV4'])->name('administrative.dashboard');

});
