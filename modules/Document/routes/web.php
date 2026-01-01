<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\Managers\Settings\DocumentConfigurationController;
use Modules\Document\Http\Controllers\Managers\Settings\DocumentGroupsController;
use Modules\Document\Http\Controllers\Managers\Settings\DocumentSettingsController;
use Modules\Document\Http\Controllers\Managers\Settings\DocumentSlaPoliciesController;
use Modules\Document\Http\Controllers\Managers\Settings\DocumentTypeController;
use Modules\Document\Http\Controllers\Managers\Settings\DocumentValidationConditionController;
use Modules\Document\Http\Controllers\Managers\Settings\ProductBlockadeController;

/*
|--------------------------------------------------------------------------
| Manager Document Routes
|--------------------------------------------------------------------------
|
| Rutas para la gestión de vistas de documentos desde el perfil Manager
| SOLO GET para renderizar vistas. POST, PUT, DELETE están en routes/api/settings.php
|
*/

Route::prefix('documents')->name('documents.')->group(function () {

    Route::get('/', [DocumentConfigurationController::class, 'index'])->name('configurations');

    Route::prefix('configurations')->name('configurations.')->group(function () {
        Route::get('/', [DocumentConfigurationController::class, 'globalSettings'])->name('global');
        Route::get('/search-templates', [DocumentConfigurationController::class, 'searchTemplates'])->name('search-templates');

        // Storage configuration routes
        Route::get('/storage', [DocumentConfigurationController::class, 'storageSettings'])->name('storage');
        Route::get('/storage/stats/{diskName}', [DocumentConfigurationController::class, 'getStorageStats'])->name('storage.stats');
        Route::get('/storage/history', [DocumentConfigurationController::class, 'getStorageConfigurationHistory'])->name('storage.history');
    });

    Route::prefix('types')->name('types.')->group(function () {
        Route::get('/', [DocumentTypeController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTypeController::class, 'create'])->name('create');
        Route::get('/edit/{documentType}', [DocumentTypeController::class, 'edit'])->name('edit');
        Route::get('/export/all', [DocumentTypeController::class, 'export'])->name('export');
    });

    // Validation Conditions
    Route::prefix('conditions')->name('conditions.')->group(function () {
        Route::get('/', [DocumentValidationConditionController::class, 'index'])->name('index');
        Route::get('/create', [DocumentValidationConditionController::class, 'create'])->name('create');
        Route::get('/edit/{condition}', [DocumentValidationConditionController::class, 'edit'])->name('edit');
    });

    // SLA Policies
    Route::prefix('sla-policies')->name('sla-policies.')->group(function () {
        Route::get('/', [DocumentSlaPoliciesController::class, 'index'])->name('index');
        Route::get('create', [DocumentSlaPoliciesController::class, 'create'])->name('create');
        Route::get('{policy}', [DocumentSlaPoliciesController::class, 'show'])->name('show');
        Route::get('/edit/{policy}', [DocumentSlaPoliciesController::class, 'edit'])->name('edit');
    });

    // Document Groups (Validator Groups)
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::get('/', [DocumentGroupsController::class, 'index'])->name('index');
        Route::get('create', [DocumentGroupsController::class, 'create'])->name('create');
        Route::get('{group}/edit', [DocumentGroupsController::class, 'edit'])->name('edit');
        Route::get('{group}/configuration', [DocumentGroupsController::class, 'configuration'])->name('configuration');
    });

    // Document Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [DocumentSettingsController::class, 'index'])->name('index');
        Route::get('/sections/{section}', [DocumentSettingsController::class, 'getSectionSettings'])->name('get-section');
    });

    // Product Blockades
    Route::prefix('blockades')->name('blockades.')->group(function () {
        Route::get('/', [ProductBlockadeController::class, 'index'])->name('index');
        Route::get('/status', [ProductBlockadeController::class, 'status'])->name('status');
    });

});
