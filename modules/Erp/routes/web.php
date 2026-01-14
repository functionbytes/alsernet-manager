<?php

use Illuminate\Support\Facades\Route;
use Modules\Erp\Http\Controllers\ErpSettingsController;

/*
|--------------------------------------------------------------------------
| ERP Module Routes
|--------------------------------------------------------------------------
|
| All routes for ERP management interface
|
*/

Route::middleware(['web', 'auth', 'verified'])->prefix('settings/erp')->name('settings.erp.')->group(function () {
    Route::get('/', [ErpSettingsController::class, 'index'])->name('index');
    Route::get('/edit', [ErpSettingsController::class, 'edit'])->name('edit');
    Route::put('/update', [ErpSettingsController::class, 'update'])->name('update');

    // Connection management
    Route::post('/check-connection', [ErpSettingsController::class, 'checkConnection'])->name('check-connection');
    Route::post('/toggle-active', [ErpSettingsController::class, 'toggleActive'])->name('toggle-active');

    // Cache & Stats
    Route::post('/clear-cache', [ErpSettingsController::class, 'clearCache'])->name('clear-cache');
    Route::post('/reset-stats', [ErpSettingsController::class, 'resetStats'])->name('reset-stats');
    Route::get('/get-stats', [ErpSettingsController::class, 'getStats'])->name('get-stats');

    // Testing
    Route::post('/test-sync', [ErpSettingsController::class, 'testSync'])->name('test-sync');
});
