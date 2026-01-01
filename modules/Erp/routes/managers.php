<?php

use Illuminate\Support\Facades\Route;
use Modules\Erp\Http\Controllers\Managers\ErpSettingsController;

/*
|--------------------------------------------------------------------------
| ERP Module Manager Routes
|--------------------------------------------------------------------------
|
| All routes for ERP management interface
|
*/

Route::middleware(['web', 'auth', 'verified'])->prefix('manager/settings')->group(function () {
    Route::prefix('erp')->group(function () {
        Route::get('/', [ErpSettingsController::class, 'index'])->name('manager.settings.erp.index');
        Route::get('/edit', [ErpSettingsController::class, 'edit'])->name('manager.settings.erp.edit');
        Route::put('/update', [ErpSettingsController::class, 'update'])->name('manager.settings.erp.update');

        // Connection management
        Route::post('/check-connection', [ErpSettingsController::class, 'checkConnection'])->name('manager.settings.erp.check-connection');
        Route::post('/toggle-active', [ErpSettingsController::class, 'toggleActive'])->name('manager.settings.erp.toggle-active');

        // Cache & Stats
        Route::post('/clear-cache', [ErpSettingsController::class, 'clearCache'])->name('manager.settings.erp.clear-cache');
        Route::post('/reset-stats', [ErpSettingsController::class, 'resetStats'])->name('manager.settings.erp.reset-stats');
        Route::get('/get-stats', [ErpSettingsController::class, 'getStats'])->name('manager.settings.erp.get-stats');

        // Testing
        Route::post('/test-sync', [ErpSettingsController::class, 'testSync'])->name('manager.settings.erp.test-sync');
    });
});
