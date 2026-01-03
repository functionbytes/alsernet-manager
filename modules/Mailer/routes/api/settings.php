<?php

use Illuminate\Support\Facades\Route;
use Modules\Mailer\Http\Controllers\MailerComponentController;
use Modules\Mailer\Http\Controllers\MailerEndpointController;
use Modules\Mailer\Http\Controllers\MailerTemplateController;
use Modules\Mailer\Http\Controllers\MailerVariableController;

/*
|--------------------------------------------------------------------------
| Mailer Settings API Routes
|--------------------------------------------------------------------------
|
| Rutas API para la gestión de configuraciones de email (POST, PUT, DELETE)
| Solo para acciones de datos. Las vistas GET están en web.php
| Middleware, prefix y name base ya están definidos en MailerServiceProvider
|
*/

// Templates API Routes
Route::prefix('templates')->name('templates.')->group(function () {
    Route::post('/', [MailerTemplateController::class, 'store'])->name('store');
    Route::patch('/{uid}', [MailerTemplateController::class, 'update'])->name('update');
    Route::delete('/{uid}', [MailerTemplateController::class, 'destroy'])->name('destroy');
    Route::post('/toggle-status/{uid}', [MailerTemplateController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/send-test/{uid}', [MailerTemplateController::class, 'sendTest'])->name('send-test');
    Route::post('/format-html', [MailerTemplateController::class, 'formatHtml'])->name('format-html');
});

// Components API Routes
Route::prefix('components')->name('components.')->group(function () {
    Route::post('/', [MailerComponentController::class, 'store'])->name('store');
    Route::patch('/{uid}', [MailerComponentController::class, 'update'])->name('update');
    Route::delete('/{uid}', [MailerComponentController::class, 'destroy'])->name('destroy');
    Route::post('/duplicate/{uid}', [MailerComponentController::class, 'duplicate'])->name('duplicate');
});

// Variables API Routes
Route::prefix('variables')->name('variables.')->group(function () {
    Route::post('/', [MailerVariableController::class, 'store'])->name('store');
    Route::patch('/{variable}', [MailerVariableController::class, 'update'])->name('update');
    Route::delete('/{variable}', [MailerVariableController::class, 'destroy'])->name('destroy');
    Route::post('/toggle-status/{variable}', [MailerVariableController::class, 'toggleStatus'])->name('toggle-status');
});

// Endpoints API Routes
Route::prefix('endpoints')->name('endpoints.')->group(function () {
    Route::post('/', [MailerEndpointController::class, 'store'])->name('store');
    Route::patch('/{emailEndpoint}', [MailerEndpointController::class, 'update'])->name('update');
    Route::delete('/{emailEndpoint}', [MailerEndpointController::class, 'destroy'])->name('destroy');
    Route::post('/regenerate-token/{emailEndpoint}', [MailerEndpointController::class, 'regenerateToken'])->name('regenerate-token');
});
