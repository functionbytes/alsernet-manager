<?php

use Modules\Mail\Http\Controllers\Managers\Settings\Mails\MailTemplateController;
use Modules\Mail\Http\Controllers\Managers\Settings\Mails\MailComponentController;
use Modules\Mail\Http\Controllers\Managers\Settings\Mails\MailVariableController;
use Modules\Mail\Http\Controllers\Managers\Settings\Mails\MailEndpointController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'templates'], function () {
    Route::get('/', [MailTemplateController::class, 'index'])->name('templates.index');
    Route::get('/create', [MailTemplateController::class, 'create'])->name('templates.create');
    Route::post('/', [MailTemplateController::class, 'store'])->name('templates.store');
    Route::get('/edit/{uid}/{translation_uid?}', [MailTemplateController::class, 'edit'])->name('templates.edit');
    Route::patch('/{uid}', [MailTemplateController::class, 'update'])->name('templates.update');
    Route::get('/preview/{uid}', [MailTemplateController::class, 'preview'])->name('templates.preview');
    Route::get('/preview-ajax/{uid}', [MailTemplateController::class, 'previewAjax'])->name('templates.preview-ajax');
    Route::get('/variables/{uid}', [MailTemplateController::class, 'getVariables'])->name('templates.variables');
    Route::get('/variables-by-module', [MailTemplateController::class, 'variablesByModule'])->name('templates.variables-by-module');
    Route::delete('/{uid}', [MailTemplateController::class, 'destroy'])->name('templates.destroy');
    Route::post('/toggle-status/{uid}', [MailTemplateController::class, 'toggleStatus'])->name('templates.toggle-status');
    Route::post('/send-test/{uid}', [MailTemplateController::class, 'sendTest'])->name('templates.send-test');
    Route::post('/format-html', [MailTemplateController::class, 'formatHtml'])->name('templates.format-html');
});

Route::group(['prefix' => 'components'], function () {
    Route::get('/', [MailComponentController::class, 'index'])->name('components.index');
    Route::get('/create', [MailComponentController::class, 'create'])->name('components.create');
    Route::post('/', [MailComponentController::class, 'store'])->name('components.store');
    Route::get('/edit/{uid}/{translation_uid?}', [MailComponentController::class, 'edit'])->name('components.edit');
    Route::patch('/{uid}', [MailComponentController::class, 'update'])->name('components.update');
    Route::get('/preview/{uid}', [MailComponentController::class, 'preview'])->name('components.preview');
    Route::get('/preview-ajax/{uid}', [MailComponentController::class, 'previewAjax'])->name('components.preview-ajax');
    Route::get('/variables', [MailComponentController::class, 'variables'])->name('components.variables');
    Route::delete('/{uid}', [MailComponentController::class, 'destroy'])->name('components.destroy');
    Route::post('/duplicate/{uid}', [MailComponentController::class, 'duplicate'])->name('components.duplicate');
});

Route::group(['prefix' => 'variables'], function () {
    Route::get('/', [MailVariableController::class, 'index'])->name('variables.index');
    Route::get('/create', [MailVariableController::class, 'create'])->name('variables.create');
    Route::post('/', [MailVariableController::class, 'store'])->name('variables.store');
    Route::get('/edit/{variable}', [MailVariableController::class, 'edit'])->name('variables.edit');
    Route::patch('/{variable}', [MailVariableController::class, 'update'])->name('variables.update');
    Route::delete('/{variable}', [MailVariableController::class, 'destroy'])->name('variables.destroy');
    Route::post('/toggle-status/{variable}', [MailVariableController::class, 'toggleStatus'])->name('variables.toggle-status');
    Route::get('/by-module', [MailVariableController::class, 'getByModule'])->name('variables.by-module');
});

Route::group(['prefix' => 'endpoints'], function () {
    Route::get('/documentation', [MailEndpointController::class, 'documentation'])->name('endpoints.documentation');
    Route::get('/', [MailEndpointController::class, 'index'])->name('endpoints.index');
    Route::get('/create', [MailEndpointController::class, 'create'])->name('endpoints.create');
    Route::post('/', [MailEndpointController::class, 'store'])->name('endpoints.store');
    Route::get('/edit/{emailEndpoint}', [MailEndpointController::class, 'edit'])->name('endpoints.edit');
    Route::patch('/{emailEndpoint}', [MailEndpointController::class, 'update'])->name('endpoints.update');
    Route::delete('/{emailEndpoint}', [MailEndpointController::class, 'destroy'])->name('endpoints.destroy');
    Route::post('/regenerate-token/{emailEndpoint}', [MailEndpointController::class, 'regenerateToken'])->name('endpoints.regenerate-token');
    Route::get('/logs/{emailEndpoint}', [MailEndpointController::class, 'logs'])->name('endpoints.logs');
});
