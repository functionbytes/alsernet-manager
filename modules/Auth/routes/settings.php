<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Settings\DeviceController;
use Modules\Auth\Http\Controllers\Settings\PasswordController;
use Modules\Auth\Http\Controllers\Settings\SessionController;
use Modules\Auth\Http\Controllers\Settings\TwoFactorAuthenticationController;

/*
|--------------------------------------------------------------------------
| Auth Settings Routes
|--------------------------------------------------------------------------
|
| Rutas de configuración de seguridad del usuario
| Prefix: /settings/auth (aplicado por ServiceProvider)
| Name: settings.auth.* (aplicado por ServiceProvider)
| Middleware: web, auth
|
*/

// Active sessions management
Route::prefix('sessions')->name('sessions.')->group(function () {
    Route::get('/', [SessionController::class, 'index'])->name('index');
    Route::delete('/{session}', [SessionController::class, 'destroy'])->name('destroy');
});

// Two-Factor Authentication
Route::prefix('two-factor')->name('two-factor.')->group(function () {
    Route::get('/', [TwoFactorAuthenticationController::class, 'index'])->name('index');
    Route::post('/enable', [TwoFactorAuthenticationController::class, 'enable'])->name('enable');
    Route::delete('/disable', [TwoFactorAuthenticationController::class, 'disable'])->name('disable');
});

// Password management
Route::prefix('password')->name('password.')->group(function () {
    Route::get('/edit', [PasswordController::class, 'edit'])->name('edit');
    Route::put('/update', [PasswordController::class, 'update'])->name('update');
});

// Authorized devices
Route::prefix('devices')->name('devices.')->group(function () {
    Route::get('/', [DeviceController::class, 'index'])->name('index');
    Route::delete('/{device}', [DeviceController::class, 'destroy'])->name('destroy');
});
