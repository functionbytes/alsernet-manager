<?php

use Modules\Core\Http\Controllers\DashboardController;

// Dashboard route - will be named 'core.dashboard' due to name prefix in provider
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
