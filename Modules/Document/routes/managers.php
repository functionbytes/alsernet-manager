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
| Rutas para la gestión de configuraciones de documentos desde el perfil Manager
|
*/

Route::prefix('documents')->name('documents.')->group(function () {

    Route::get('/', [DocumentConfigurationController::class, 'index'])->name('configurations');

    Route::prefix('configurations')->name('configurations.')->group(function () {
        Route::get('/', [DocumentConfigurationController::class, 'globalSettings'])->name('global');
        Route::post('/', [DocumentConfigurationController::class, 'updateGlobalSettings'])->name('update');
        Route::get('/search-templates', [DocumentConfigurationController::class, 'searchTemplates'])->name('search-templates');

        // Storage configuration routes
        Route::get('/storage', [DocumentConfigurationController::class, 'storageSettings'])->name('storage');
        Route::post('/storage', [DocumentConfigurationController::class, 'updateStorageSettings'])->name('storage.update');
        Route::post('/storage/test', [DocumentConfigurationController::class, 'testStorageConnection'])->name('storage.test');
        Route::get('/storage/stats/{diskName}', [DocumentConfigurationController::class, 'getStorageStats'])->name('storage.stats');
        Route::get('/storage/history', [DocumentConfigurationController::class, 'getStorageConfigurationHistory'])->name('storage.history');
    });

    Route::prefix('types')->name('types.')->group(function () {
        Route::get('/', [DocumentTypeController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTypeController::class, 'create'])->name('create');
        Route::post('/', [DocumentTypeController::class, 'store'])->name('store');
        Route::get('/edit/{documentType}', [DocumentTypeController::class, 'edit'])->name('edit');
        Route::post('/{documentType}', [DocumentTypeController::class, 'update'])->name('update');
        Route::delete('/{documentType}', [DocumentTypeController::class, 'destroy'])->name('destroy');
        Route::post('/{documentType}/toggle-active', [DocumentTypeController::class, 'toggleActive'])->name('toggle-active');
        Route::get('/export/all', [DocumentTypeController::class, 'export'])->name('export');
    });

    // Validation Conditions
    Route::prefix('conditions')->name('conditions.')->group(function () {
        Route::get('/', [DocumentValidationConditionController::class, 'index'])->name('index');
        Route::get('/create', [DocumentValidationConditionController::class, 'create'])->name('create');
        Route::post('/', [DocumentValidationConditionController::class, 'store'])->name('store');
        Route::get('/edit/{condition}', [DocumentValidationConditionController::class, 'edit'])->name('edit');
        Route::put('/{condition}', [DocumentValidationConditionController::class, 'update'])->name('update');
        Route::delete('/{condition}', [DocumentValidationConditionController::class, 'destroy'])->name('destroy');
        Route::post('/{condition}/toggle-active', [DocumentValidationConditionController::class, 'toggleActive'])->name('toggle-active');
    });

    // SLA Policies
    Route::prefix('sla-policies')->name('sla-policies.')->group(function () {
        Route::get('/', [DocumentSlaPoliciesController::class, 'index'])->name('index');
        Route::get('create', [DocumentSlaPoliciesController::class, 'create'])->name('create');
        Route::post('/', [DocumentSlaPoliciesController::class, 'store'])->name('store');
        Route::get('{policy}', [DocumentSlaPoliciesController::class, 'show'])->name('show');
        Route::get('/edit/{policy}', [DocumentSlaPoliciesController::class, 'edit'])->name('edit');
        Route::put('{policy}', [DocumentSlaPoliciesController::class, 'update'])->name('update');
        Route::patch('{policy}/toggle', [DocumentSlaPoliciesController::class, 'toggle'])->name('toggle');
        Route::delete('{policy}', [DocumentSlaPoliciesController::class, 'destroy'])->name('destroy');
    });

    // Document Groups (Validator Groups)
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::get('/', [DocumentGroupsController::class, 'index'])->name('index');
        Route::get('create', [DocumentGroupsController::class, 'create'])->name('create');
        Route::post('/', [DocumentGroupsController::class, 'store'])->name('store');
        Route::get('{group}/edit', [DocumentGroupsController::class, 'edit'])->name('edit');
        Route::put('{group}', [DocumentGroupsController::class, 'update'])->name('update');
        Route::patch('{group}/toggle', [DocumentGroupsController::class, 'toggle'])->name('toggle');
        Route::delete('{group}', [DocumentGroupsController::class, 'destroy'])->name('destroy');
        Route::post('reorder', [DocumentGroupsController::class, 'reorder'])->name('reorder');
        Route::get('{group}/configuration', [DocumentGroupsController::class, 'configuration'])->name('configuration');
        Route::post('{group}/configuration', [DocumentGroupsController::class, 'updateConfiguration'])->name('update-configuration');
    });

    // Document Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [DocumentSettingsController::class, 'index'])->name('index');
        Route::post('/update', [DocumentSettingsController::class, 'update'])->name('update');
        Route::post('/store', [DocumentSettingsController::class, 'store'])->name('store');
        Route::get('/sections/{section}', [DocumentSettingsController::class, 'getSectionSettings'])->name('get-section');
        Route::post('/reset/{group}', [DocumentSettingsController::class, 'resetToDefaults'])->name('reset');
    });

    // Product Blockades
    Route::prefix('blockades')->name('blockades.')->group(function () {
        Route::get('/', [ProductBlockadeController::class, 'index'])->name('index');
        Route::post('/sync', [ProductBlockadeController::class, 'sync'])->name('sync');
        Route::get('/status', [ProductBlockadeController::class, 'status'])->name('status');
        Route::post('/', [ProductBlockadeController::class, 'store'])->name('store');
        Route::post('/bulk', [ProductBlockadeController::class, 'storeBulk'])->name('store-bulk');
        Route::post('/labels', [ProductBlockadeController::class, 'saveLabels'])->name('save-labels');
        Route::delete('/{id}', [ProductBlockadeController::class, 'destroy'])->name('destroy');
    });

});
