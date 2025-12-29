<?php

use Illuminate\Support\Facades\Route;
use Modules\Documents\Http\Controllers\Managers\Settings\DocumentConfigurationController;
use Modules\Documents\Http\Controllers\Managers\Settings\DocumentGroupsController;
use Modules\Documents\Http\Controllers\Managers\Settings\DocumentSettingsController;
use Modules\Documents\Http\Controllers\Managers\Settings\DocumentSlaPoliciesController;
use Modules\Documents\Http\Controllers\Managers\Settings\DocumentTypeController;
use Modules\Documents\Http\Controllers\Managers\Settings\DocumentValidationConditionController;
use Modules\Documents\Http\Controllers\Managers\Settings\ProductBlockadeController;

/*
|--------------------------------------------------------------------------
| Manager Document Routes
|--------------------------------------------------------------------------
|
| Rutas para la gestión de configuraciones de documentos desde el perfil Manager
|
*/

Route::middleware(['auth', 'role:manager|super-admin'])->prefix('manager/settings')->name('manager.settings.')->group(function () {

    Route::group(['prefix' => 'documents'], function () {

        Route::get('/', [DocumentConfigurationController::class, 'index'])->name('documents.configurations');

        Route::group(['prefix' => 'configurations'], function () {
            Route::get('/', [DocumentConfigurationController::class, 'globalSettings'])->name('documents.configurations.global');
            Route::post('/', [DocumentConfigurationController::class, 'updateGlobalSettings'])->name('documents.configurations.update');
            Route::get('/search-templates', [DocumentConfigurationController::class, 'searchTemplates'])->name('documents.configurations.search-templates');

            // Storage configuration routes
            Route::get('/storage', [DocumentConfigurationController::class, 'storageSettings'])->name('documents.configurations.storage');
            Route::post('/storage', [DocumentConfigurationController::class, 'updateStorageSettings'])->name('documents.configurations.storage.update');
            Route::post('/storage/test', [DocumentConfigurationController::class, 'testStorageConnection'])->name('documents.configurations.storage.test');
            Route::get('/storage/stats/{diskName}', [DocumentConfigurationController::class, 'getStorageStats'])->name('documents.configurations.storage.stats');
            Route::get('/storage/history', [DocumentConfigurationController::class, 'getStorageConfigurationHistory'])->name('documents.configurations.storage.history');
        });

        Route::group(['prefix' => 'types'], function () {
            Route::get('/', [DocumentTypeController::class, 'index'])->name('documents.types');
            Route::get('/create', [DocumentTypeController::class, 'create'])->name('documents.types.create');
            Route::post('/', [DocumentTypeController::class, 'store'])->name('documents.types.store');
            Route::get('/edit/{documentType}', [DocumentTypeController::class, 'edit'])->name('documents.types.edit');
            Route::post('/{documentType}', [DocumentTypeController::class, 'update'])->name('documents.types.update');
            Route::delete('/{documentType}', [DocumentTypeController::class, 'destroy'])->name('documents.types.destroy');
            Route::post('/{documentType}/toggle-active', [DocumentTypeController::class, 'toggleActive'])->name('documents.types.toggle-active');
            Route::get('/export/all', [DocumentTypeController::class, 'export'])->name('documents.types.export');
        });

        // Validation Conditions
        Route::group(['prefix' => 'conditions'], function () {
            Route::get('/', [DocumentValidationConditionController::class, 'index'])->name('documents.conditions');
            Route::get('/create', [DocumentValidationConditionController::class, 'create'])->name('documents.conditions.create');
            Route::post('/', [DocumentValidationConditionController::class, 'store'])->name('documents.conditions.store');
            Route::get('/edit/{condition}', [DocumentValidationConditionController::class, 'edit'])->name('documents.conditions.edit');
            Route::put('/{condition}', [DocumentValidationConditionController::class, 'update'])->name('documents.conditions.update');
            Route::delete('/{condition}', [DocumentValidationConditionController::class, 'destroy'])->name('documents.conditions.destroy');
            Route::post('/{condition}/toggle-active', [DocumentValidationConditionController::class, 'toggleActive'])->name('documents.conditions.toggle-active');
        });

        // SLA Policies
        Route::group(['prefix' => 'sla-policies'], function () {
            Route::get('/', [DocumentSlaPoliciesController::class, 'index'])->name('documents.sla-policies.index');
            Route::get('create', [DocumentSlaPoliciesController::class, 'create'])->name('documents.sla-policies.create');
            Route::post('/', [DocumentSlaPoliciesController::class, 'store'])->name('documents.sla-policies.store');
            Route::get('{policy}', [DocumentSlaPoliciesController::class, 'show'])->name('documents.sla-policies.show');
            Route::get('/edit/{policy}', [DocumentSlaPoliciesController::class, 'edit'])->name('documents.sla-policies.edit');
            Route::put('{policy}', [DocumentSlaPoliciesController::class, 'update'])->name('documents.sla-policies.update');
            Route::patch('{policy}/toggle', [DocumentSlaPoliciesController::class, 'toggle'])->name('documents.sla-policies.toggle');
            Route::delete('{policy}', [DocumentSlaPoliciesController::class, 'destroy'])->name('documents.sla-policies.destroy');
        });

        // Document Groups
        Route::group(['prefix' => 'groups'], function () {
            Route::get('/', [DocumentGroupsController::class, 'index'])->name('documents.groups.index');
            Route::get('create', [DocumentGroupsController::class, 'create'])->name('documents.groups.create');
            Route::post('/', [DocumentGroupsController::class, 'store'])->name('documents.groups.store');
            Route::get('{group}/edit', [DocumentGroupsController::class, 'edit'])->name('documents.groups.edit');
            Route::put('{group}', [DocumentGroupsController::class, 'update'])->name('documents.groups.update');
            Route::patch('{group}/toggle', [DocumentGroupsController::class, 'toggle'])->name('documents.groups.toggle');
            Route::delete('{group}', [DocumentGroupsController::class, 'destroy'])->name('documents.groups.destroy');
            Route::post('reorder', [DocumentGroupsController::class, 'reorder'])->name('documents.groups.reorder');
            Route::get('{group}/configuration', [DocumentGroupsController::class, 'configuration'])->name('documents.groups.configuration');
            Route::post('{group}/configuration', [DocumentGroupsController::class, 'updateConfiguration'])->name('documents.groups.update-configuration');
        });

        // Document Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [DocumentSettingsController::class, 'index'])->name('index');
            Route::post('/update', [DocumentSettingsController::class, 'update'])->name('update');
            Route::post('/store', [DocumentSettingsController::class, 'store'])->name('update');
            Route::get('/sections/{section}', [DocumentSettingsController::class, 'getSectionSettings'])->name('get-section');
            Route::post('/reset/{group}', [DocumentSettingsController::class, 'resetToDefaults'])->name('reset');
        });

        // Product Blockades
        Route::prefix('blockades')->name('documents.blockades.')->group(function () {
            Route::get('/', [ProductBlockadeController::class, 'index'])->name('index');
            Route::post('/sync', [ProductBlockadeController::class, 'sync'])->name('sync');
            Route::get('/status', [ProductBlockadeController::class, 'status'])->name('status');
            Route::post('/', [ProductBlockadeController::class, 'store'])->name('store');
            Route::post('/bulk', [ProductBlockadeController::class, 'storeBulk'])->name('store-bulk');
            Route::post('/labels', [ProductBlockadeController::class, 'saveLabels'])->name('save-labels');
            Route::delete('/{id}', [ProductBlockadeController::class, 'destroy'])->name('destroy');
        });
    });
});
