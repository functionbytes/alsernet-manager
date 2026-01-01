<?php

use Illuminate\Support\Facades\Route;
use Modules\Mailer\Http\Controllers\Settings\MailerComponentController;
use Modules\Mailer\Http\Controllers\Settings\MailerEndpointController;
use Modules\Mailer\Http\Controllers\Settings\MailerTemplateController;
use Modules\Mailer\Http\Controllers\Settings\MailerVariableController;

Route::group(['prefix' => 'templates'], function () {
        Route::get('/', [MailerTemplateController::class, 'index'])->name('manager.settings.mailers.templates.index');
        Route::get('/create', [MailerTemplateController::class, 'create'])->name('manager.settings.mailers.templates.create');
        Route::post('/', [MailerTemplateController::class, 'store'])->name('manager.settings.mailers.templates.store');
        Route::get('/edit/{uid}/{translation_uid?}', [MailerTemplateController::class, 'edit'])->name('manager.settings.mailers.templates.edit');
        Route::patch('/{uid}', [MailerTemplateController::class, 'update'])->name('manager.settings.mailers.templates.update');
        Route::get('/preview/{uid}', [MailerTemplateController::class, 'preview'])->name('manager.settings.mailers.templates.preview');
        Route::get('/preview-ajax/{uid}', [MailerTemplateController::class, 'previewAjax'])->name('manager.settings.mailers.templates.preview-ajax');
        Route::get('/variables/{uid}', [MailerTemplateController::class, 'getVariables'])->name('manager.settings.mailers.templates.variables');
        Route::get('/variables-by-module', [MailerTemplateController::class, 'variablesByModule'])->name('manager.settings.mailers.variables-by-module');
        Route::delete('/{uid}', [MailerTemplateController::class, 'destroy'])->name('manager.settings.mailers.templates.destroy');
        Route::post('/toggle-status/{uid}', [MailerTemplateController::class, 'toggleStatus'])->name('manager.settings.mailers.templates.toggle-status');
        Route::post('/send-test/{uid}', [MailerTemplateController::class, 'sendTest'])->name('manager.settings.mailers.templates.send-test');
        Route::post('/format-html', [MailerTemplateController::class, 'formatHtml'])->name('manager.settings.mailers.templates.format-html');
});

Route::group(['prefix' => 'components'], function () {
    Route::get('/', [MailerComponentController::class, 'index'])->name('manager.settings.mailers.components.index');
    Route::get('/create', [MailerComponentController::class, 'create'])->name('manager.settings.mailers.components.create');
    Route::post('/', [MailerComponentController::class, 'store'])->name('manager.settings.mailers.components.store');
    Route::get('/edit/{uid}/{translation_uid?}', [MailerComponentController::class, 'edit'])->name('manager.settings.mailers.components.edit');
    Route::patch('/{uid}', [MailerComponentController::class, 'update'])->name('manager.settings.mailers.components.update');
    Route::get('/preview/{uid}', [MailerComponentController::class, 'preview'])->name('manager.settings.mailers.components.preview');
    Route::get('/preview-ajax/{uid}', [MailerComponentController::class, 'previewAjax'])->name('manager.settings.mailers.components.preview-ajax');
    Route::get('/variables', [MailerComponentController::class, 'variables'])->name('manager.settings.mailers.components.variables');
    Route::delete('/{uid}', [MailerComponentController::class, 'destroy'])->name('manager.settings.mailers.components.destroy');
    Route::post('/duplicate/{uid}', [MailerComponentController::class, 'duplicate'])->name('manager.settings.mailers.components.duplicate');
});

Route::group(['prefix' => 'variables'], function () {
    Route::get('/', [MailerVariableController::class, 'index'])->name('manager.settings.mailers.variables.index');
    Route::get('/create', [MailerVariableController::class, 'create'])->name('manager.settings.mailers.variables.create');
    Route::post('/', [MailerVariableController::class, 'store'])->name('manager.settings.mailers.variables.store');
    Route::get('/edit/{variable}', [MailerVariableController::class, 'edit'])->name('manager.settings.mailers.variables.edit');
    Route::patch('/{variable}', [MailerVariableController::class, 'update'])->name('manager.settings.mailers.variables.update');
    Route::delete('/{variable}', [MailerVariableController::class, 'destroy'])->name('manager.settings.mailers.variables.destroy');
    Route::post('/toggle-status/{variable}', [MailerVariableController::class, 'toggleStatus'])->name('manager.settings.mailers.variables.toggle-status');
    Route::get('/by-module', [MailerVariableController::class, 'getByModule'])->name('manager.settings.mailers.variables.by-module');
});

Route::group(['prefix' => 'endpoints'], function () {
    Route::get('/documentation', [MailerEndpointController::class, 'documentation'])->name('manager.settings.mailers.endpoints.documentation');
    Route::get('/', [MailerEndpointController::class, 'index'])->name('manager.settings.mailers.endpoints.index');
    Route::get('/create', [MailerEndpointController::class, 'create'])->name('manager.settings.mailers.endpoints.create');
    Route::post('/', [MailerEndpointController::class, 'store'])->name('manager.settings.mailers.endpoints.store');
    Route::get('/edit/{emailEndpoint}', [MailerEndpointController::class, 'edit'])->name('manager.settings.mailers.endpoints.edit');
    Route::patch('/{emailEndpoint}', [MailerEndpointController::class, 'update'])->name('manager.settings.mailers.endpoints.update');
    Route::delete('/{emailEndpoint}', [MailerEndpointController::class, 'destroy'])->name('manager.settings.mailers.endpoints.destroy');
    Route::post('/regenerate-token/{emailEndpoint}', [MailerEndpointController::class, 'regenerateToken'])->name('manager.settings.mailers.endpoints.regenerate-token');
    Route::get('/logs/{emailEndpoint}', [MailerEndpointController::class, 'logs'])->name('manager.settings.mailers.endpoints.logs');
});
