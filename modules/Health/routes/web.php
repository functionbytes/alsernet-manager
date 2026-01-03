<?php

use Illuminate\Support\Facades\Route;
use Modules\Health\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| Health Routes
|--------------------------------------------------------------------------
|
| Health monitoring and system diagnostics routes
| Prefix: /backups/health (applied by ServiceProvider)
| Name: backups.health.* (applied by ServiceProvider)
| Middleware: web, auth, role:manager|super-admin
|
*/

Route::middleware(['web', 'auth', 'role:manager|super-admin'])
    ->prefix('settings/health')
    ->name('settings.health.')
    ->group(function () {
        Route::get('/', [HealthController::class, 'index'])->name('index');
        Route::get('/check', [HealthController::class, 'check'])->name('check');
        Route::get('/history', [HealthController::class, 'history'])->name('history');
    });

// Health Check Routes (no authentication, no rate limiting - for external monitoring)
Route::prefix('health')->group(function () {
    Route::get('ping', [HealthController::class, 'ping']);           // Ping simple
    Route::get('/', [HealthController::class, 'health']);            // Health check completo
    Route::get('documents', [HealthController::class, 'documentsHealth']); // Health específico documentos
    Route::get('detailed', [HealthController::class, 'detailed']);   // Detallado (solo debug)
});
