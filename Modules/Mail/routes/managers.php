<?php

use Illuminate\Support\Facades\Route;
use Modules\Mail\Http\Controllers\Managers\Settings\Mails\MailComponentController;
use Modules\Mail\Http\Controllers\Managers\Settings\Mails\MailEndpointController;
use Modules\Mail\Http\Controllers\Managers\Settings\Mails\MailTemplateController;
use Modules\Mail\Http\Controllers\Managers\Settings\Mails\MailVariableController;

Route::group(['prefix' => 'mailers'], function () {

    Route::group(['prefix' => 'templates'], function () {
        Route::get('/', [MailTemplateController::class, 'index'])->name('manager.settings.mailers.templates.index');
        Route::get('/create', [MailTemplateController::class, 'create'])->name('manager.settings.mailers.templates.create');
        Route::post('/', [MailTemplateController::class, 'store'])->name('manager.settings.mailers.templates.store');
        Route::get('/edit/{uid}/{translation_uid?}', [MailTemplateController::class, 'edit'])->name('manager.settings.mailers.templates.edit');
        Route::patch('/{uid}', [MailTemplateController::class, 'update'])->name('manager.settings.mailers.templates.update');
        Route::get('/preview/{uid}', [MailTemplateController::class, 'preview'])->name('manager.settings.mailers.templates.preview');
        Route::get('/preview-ajax/{uid}', [MailTemplateController::class, 'previewAjax'])->name('manager.settings.mailers.templates.preview-ajax');
        Route::get('/variables/{uid}', [MailTemplateController::class, 'getVariables'])->name('manager.settings.mailers.templates.variables');
        Route::get('/variables-by-module', [MailTemplateController::class, 'variablesByModule'])->name('manager.settings.mailers.variables-by-module');
        Route::delete('/{uid}', [MailTemplateController::class, 'destroy'])->name('manager.settings.mailers.templates.destroy');
        Route::post('/toggle-status/{uid}', [MailTemplateController::class, 'toggleStatus'])->name('manager.settings.mailers.templates.toggle-status');
        Route::post('/send-test/{uid}', [MailTemplateController::class, 'sendTest'])->name('manager.settings.mailers.templates.send-test');
        Route::post('/format-html', [MailTemplateController::class, 'formatHtml'])->name('manager.settings.mailers.templates.format-html');
    });

    Route::group(['prefix' => 'components'], function () {
        Route::get('/', [MailComponentController::class, 'index'])->name('manager.settings.mailers.components.index');
        Route::get('/create', [MailComponentController::class, 'create'])->name('manager.settings.mailers.components.create');
        Route::post('/', [MailComponentController::class, 'store'])->name('manager.settings.mailers.components.store');
        Route::get('/edit/{uid}/{translation_uid?}', [MailComponentController::class, 'edit'])->name('manager.settings.mailers.components.edit');
        Route::patch('/{uid}', [MailComponentController::class, 'update'])->name('manager.settings.mailers.components.update');
        Route::get('/preview/{uid}', [MailComponentController::class, 'preview'])->name('manager.settings.mailers.components.preview');
        Route::get('/preview-ajax/{uid}', [MailComponentController::class, 'previewAjax'])->name('manager.settings.mailers.components.preview-ajax');
        Route::get('/variables', [MailComponentController::class, 'variables'])->name('manager.settings.mailers.components.variables');
        Route::delete('/{uid}', [MailComponentController::class, 'destroy'])->name('manager.settings.mailers.components.destroy');
        Route::post('/duplicate/{uid}', [MailComponentController::class, 'duplicate'])->name('manager.settings.mailers.components.duplicate');
    });

    Route::group(['prefix' => 'variables'], function () {
        Route::get('/', [MailVariableController::class, 'index'])->name('manager.settings.mailers.variables.index');
        Route::get('/create', [MailVariableController::class, 'create'])->name('manager.settings.mailers.variables.create');
        Route::post('/', [MailVariableController::class, 'store'])->name('manager.settings.mailers.variables.store');
        Route::get('/edit/{variable}', [MailVariableController::class, 'edit'])->name('manager.settings.mailers.variables.edit');
        Route::patch('/{variable}', [MailVariableController::class, 'update'])->name('manager.settings.mailers.variables.update');
        Route::delete('/{variable}', [MailVariableController::class, 'destroy'])->name('manager.settings.mailers.variables.destroy');
        Route::post('/toggle-status/{variable}', [MailVariableController::class, 'toggleStatus'])->name('manager.settings.mailers.variables.toggle-status');
        Route::get('/by-module', [MailVariableController::class, 'getByModule'])->name('manager.settings.mailers.variables.by-module');
    });

    Route::group(['prefix' => 'endpoints'], function () {
        Route::get('/documentation', [MailEndpointController::class, 'documentation'])->name('manager.settings.mailers.endpoints.documentation');
        Route::get('/', [MailEndpointController::class, 'index'])->name('manager.settings.mailers.endpoints.index');
        Route::get('/create', [MailEndpointController::class, 'create'])->name('manager.settings.mailers.endpoints.create');
        Route::post('/', [MailEndpointController::class, 'store'])->name('manager.settings.mailers.endpoints.store');
        Route::get('/edit/{emailEndpoint}', [MailEndpointController::class, 'edit'])->name('manager.settings.mailers.endpoints.edit');
        Route::patch('/{emailEndpoint}', [MailEndpointController::class, 'update'])->name('manager.settings.mailers.endpoints.update');
        Route::delete('/{emailEndpoint}', [MailEndpointController::class, 'destroy'])->name('manager.settings.mailers.endpoints.destroy');
        Route::post('/regenerate-token/{emailEndpoint}', [MailEndpointController::class, 'regenerateToken'])->name('manager.settings.mailers.endpoints.regenerate-token');
        Route::get('/logs/{emailEndpoint}', [MailEndpointController::class, 'logs'])->name('manager.settings.mailers.endpoints.logs');
    });

});
