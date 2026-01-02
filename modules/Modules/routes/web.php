<?php

use Illuminate\Support\Facades\Route;
use Modules\Modules\Http\Controllers\ModulesController;

Route::middleware(['auth', 'verified'])->prefix('modules')->group(function () {
    // Modules management routes
    Route::get('/', [ModulesController::class, 'index'])->name('modules.index');
    Route::get('/{moduleAlias}', [ModulesController::class, 'show'])->name('modules.show');

    // Enable/Disable operations
    Route::post('/{moduleAlias}/enable', [ModulesController::class, 'enable'])->name('modules.enable');
    Route::post('/{moduleAlias}/disable', [ModulesController::class, 'disable'])->name('modules.disable');

    // Install/Uninstall operations
    Route::get('/upload/form', [ModulesController::class, 'uploadForm'])->name('modules.uploadForm');
    Route::post('/install', [ModulesController::class, 'install'])->name('modules.install');
    Route::post('/{moduleAlias}/uninstall', [ModulesController::class, 'uninstall'])->name('modules.uninstall');
});
