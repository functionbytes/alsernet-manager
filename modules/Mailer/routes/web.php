<?php

use Illuminate\Support\Facades\Route;
use Modules\Mailer\Http\Controllers\Settings\MailerComponentController;
use Modules\Mailer\Http\Controllers\Settings\MailerEndpointController;
use Modules\Mailer\Http\Controllers\Settings\MailerTemplateController;
use Modules\Mailer\Http\Controllers\Settings\MailerVariableController;

Route::group(['prefix' => 'templates'], function () {
        Route::get('/', [MailerTemplateController::class, 'index'])->name('templates.index');
        Route::get('/create', [MailerTemplateController::class, 'create'])->name('templates.create');
        Route::post('/', [MailerTemplateController::class, 'store'])->name('templates.store');
        Route::get('/edit/{uid}/{translation_uid?}', [MailerTemplateController::class, 'edit'])->name('templates.edit');
        Route::patch('/{uid}', [MailerTemplateController::class, 'update'])->name('templates.update');
        Route::get('/preview/{uid}', [MailerTemplateController::class, 'preview'])->name('templates.preview');
        Route::get('/preview-ajax/{uid}', [MailerTemplateController::class, 'previewAjax'])->name('templates.preview-ajax');
        Route::get('/variables/{uid}', [MailerTemplateController::class, 'getVariables'])->name('templates.variables');
        Route::get('/variables-by-module', [MailerTemplateController::class, 'variablesByModule'])->name('variables-by-module');
        Route::delete('/{uid}', [MailerTemplateController::class, 'destroy'])->name('templates.destroy');
        Route::post('/toggle-status/{uid}', [MailerTemplateController::class, 'toggleStatus'])->name('templates.toggle-status');
        Route::post('/send-test/{uid}', [MailerTemplateController::class, 'sendTest'])->name('templates.send-test');
        Route::post('/format-html', [MailerTemplateController::class, 'formatHtml'])->name('templates.format-html');
});

Route::group(['prefix' => 'components'], function () {
    Route::get('/', [MailerComponentController::class, 'index'])->name('components.index');
    Route::get('/create', [MailerComponentController::class, 'create'])->name('components.create');
    Route::post('/', [MailerComponentController::class, 'store'])->name('components.store');
    Route::get('/edit/{uid}/{translation_uid?}', [MailerComponentController::class, 'edit'])->name('components.edit');
    Route::patch('/{uid}', [MailerComponentController::class, 'update'])->name('components.update');
    Route::get('/preview/{uid}', [MailerComponentController::class, 'preview'])->name('components.preview');
    Route::get('/preview-ajax/{uid}', [MailerComponentController::class, 'previewAjax'])->name('components.preview-ajax');
    Route::get('/variables', [MailerComponentController::class, 'variables'])->name('components.variables');
    Route::delete('/{uid}', [MailerComponentController::class, 'destroy'])->name('components.destroy');
    Route::post('/duplicate/{uid}', [MailerComponentController::class, 'duplicate'])->name('components.duplicate');
});

Route::group(['prefix' => 'variables'], function () {
    Route::get('/', [MailerVariableController::class, 'index'])->name('variables.index');
    Route::get('/create', [MailerVariableController::class, 'create'])->name('variables.create');
    Route::post('/', [MailerVariableController::class, 'store'])->name('variables.store');
    Route::get('/edit/{variable}', [MailerVariableController::class, 'edit'])->name('variables.edit');
    Route::patch('/{variable}', [MailerVariableController::class, 'update'])->name('variables.update');
    Route::delete('/{variable}', [MailerVariableController::class, 'destroy'])->name('variables.destroy');
    Route::post('/toggle-status/{variable}', [MailerVariableController::class, 'toggleStatus'])->name('variables.toggle-status');
    Route::get('/by-module', [MailerVariableController::class, 'getByModule'])->name('variables.by-module');
});

Route::group(['prefix' => 'endpoints'], function () {
    Route::get('/documentation', [MailerEndpointController::class, 'documentation'])->name('endpoints.documentation');
    Route::get('/', [MailerEndpointController::class, 'index'])->name('endpoints.index');
    Route::get('/create', [MailerEndpointController::class, 'create'])->name('endpoints.create');
    Route::post('/', [MailerEndpointController::class, 'store'])->name('endpoints.store');
    Route::get('/edit/{emailEndpoint}', [MailerEndpointController::class, 'edit'])->name('endpoints.edit');
    Route::patch('/{emailEndpoint}', [MailerEndpointController::class, 'update'])->name('endpoints.update');
    Route::delete('/{emailEndpoint}', [MailerEndpointController::class, 'destroy'])->name('endpoints.destroy');
    Route::post('/regenerate-token/{emailEndpoint}', [MailerEndpointController::class, 'regenerateToken'])->name('endpoints.regenerate-token');
    Route::get('/logs/{emailEndpoint}', [MailerEndpointController::class, 'logs'])->name('endpoints.logs');
});
