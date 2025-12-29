<?php

use Illuminate\Support\Facades\Route;
use Modules\Prestashop\Http\Controllers\Managers\Settings\PrestashopSettingsController;

Route::prefix('prestashop')->name('prestashop.')->group(function () {
    // Dashboard
    Route::get('/', [PrestashopSettingsController::class, 'index'])->name('index');
    Route::get('/edit', [PrestashopSettingsController::class, 'edit'])->name('edit');
    Route::put('/update', [PrestashopSettingsController::class, 'update'])->name('update');

    // Connection & Status
    Route::post('/check-connection', [PrestashopSettingsController::class, 'checkConnection'])->name('check-connection');
    Route::post('/toggle-active', [PrestashopSettingsController::class, 'toggleActive'])->name('toggle-active');
    Route::post('/test-sync', [PrestashopSettingsController::class, 'testSync'])->name('test-sync');

    // Statistics
    Route::get('/stats', [PrestashopSettingsController::class, 'getStats'])->name('stats');
    Route::post('/reset-stats', [PrestashopSettingsController::class, 'resetStats'])->name('reset-stats');

    // Product Blockades
    Route::post('/sync-blockades', [PrestashopSettingsController::class, 'syncProductBlockades'])->name('sync-blockades');
    Route::get('/blockades-status', [PrestashopSettingsController::class, 'getBlockadesSyncStatus'])->name('blockades-status');
    Route::post('/blockades/create', [PrestashopSettingsController::class, 'createBlockade'])->name('blockades.create');
    Route::delete('/blockades/delete', [PrestashopSettingsController::class, 'deleteBlockade'])->name('blockades.delete');
    Route::post('/blockades/labels', [PrestashopSettingsController::class, 'saveBlockadeLabels'])->name('blockades.labels');
});
