<?php

use Illuminate\Support\Facades\Route;
use Modules\Mailer\Http\Controllers\MailerComponentController;
use Modules\Mailer\Http\Controllers\MailerEndpointController;
use Modules\Mailer\Http\Controllers\MailerTemplateController;
use Modules\Mailer\Http\Controllers\MailerVariableController;


// Templates Routes (Views)
Route::prefix('templates')->name('templates.')->group(function () {
    Route::get('/', [MailerTemplateController::class, 'index'])->name('index');
    Route::get('/create', [MailerTemplateController::class, 'create'])->name('create');
    Route::get('/edit/{uid}/{translation_uid?}', [MailerTemplateController::class, 'edit'])->name('edit');
    Route::get('/preview/{uid}', [MailerTemplateController::class, 'preview'])->name('preview');
    Route::get('/preview-ajax/{uid}', [MailerTemplateController::class, 'previewAjax'])->name('preview-ajax');
    Route::get('/variables/{uid}', [MailerTemplateController::class, 'getVariables'])->name('variables');
    Route::get('/variables-by-module', [MailerTemplateController::class, 'variablesByModule'])->name('variables-by-module');
});

// Components Routes (Views)
Route::prefix('components')->name('components.')->group(function () {
    Route::get('/', [MailerComponentController::class, 'index'])->name('index');
    Route::get('/create', [MailerComponentController::class, 'create'])->name('create');
    Route::get('/edit/{uid}/{translation_uid?}', [MailerComponentController::class, 'edit'])->name('edit');
    Route::get('/preview/{uid}', [MailerComponentController::class, 'preview'])->name('preview');
    Route::get('/preview-ajax/{uid}', [MailerComponentController::class, 'previewAjax'])->name('preview-ajax');
    Route::get('/variables', [MailerComponentController::class, 'variables'])->name('variables');
});

// Variables Routes (Views)
Route::prefix('variables')->name('variables.')->group(function () {
    Route::get('/', [MailerVariableController::class, 'index'])->name('index');
    Route::get('/create', [MailerVariableController::class, 'create'])->name('create');
    Route::get('/edit/{variable}', [MailerVariableController::class, 'edit'])->name('edit');
    Route::get('/by-module', [MailerVariableController::class, 'getByModule'])->name('by-module');
});

// Endpoints Routes (Views)
Route::prefix('endpoints')->name('endpoints.')->group(function () {
    Route::get('/documentation', [MailerEndpointController::class, 'documentation'])->name('documentation');
    Route::get('/', [MailerEndpointController::class, 'index'])->name('index');
    Route::get('/create', [MailerEndpointController::class, 'create'])->name('create');
    Route::get('/edit/{emailEndpoint}', [MailerEndpointController::class, 'edit'])->name('edit');
    Route::get('/logs/{emailEndpoint}', [MailerEndpointController::class, 'logs'])->name('logs');
});
