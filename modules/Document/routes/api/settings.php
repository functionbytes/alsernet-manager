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
| Document Settings API Routes
|--------------------------------------------------------------------------
|
| Rutas API para la gestión de configuraciones de documentos (POST, PUT, DELETE)
| Solo para acciones de datos. Las vistas GET están en theme.php
|
*/

Route::middleware(['auth', 'role:manager|super-admin'])->group(function () {

    Route::prefix('documents/configurations')->name('documents.configurations.')->group(function () {
        Route::post('/', [DocumentConfigurationController::class, 'updateGlobalSettings'])->name('update');
        Route::post('/storage', [DocumentConfigurationController::class, 'updateStorageSettings'])->name('storage.update');
        Route::post('/storage/test', [DocumentConfigurationController::class, 'testStorageConnection'])->name('storage.test');
    });

    Route::prefix('documents/types')->name('documents.types.')->group(function () {
        Route::post('/', [DocumentTypeController::class, 'store'])->name('store');
        Route::put('/{documentType}', [DocumentTypeController::class, 'update'])->name('update');
        Route::delete('/{documentType}', [DocumentTypeController::class, 'destroy'])->name('destroy');
        Route::post('/{documentType}/toggle-active', [DocumentTypeController::class, 'toggleActive'])->name('toggle-active');
    });

    Route::prefix('documents/conditions')->name('documents.conditions.')->group(function () {
        Route::post('/', [DocumentValidationConditionController::class, 'store'])->name('store');
        Route::put('/{condition}', [DocumentValidationConditionController::class, 'update'])->name('update');
        Route::delete('/{condition}', [DocumentValidationConditionController::class, 'destroy'])->name('destroy');
        Route::post('/{condition}/toggle-active', [DocumentValidationConditionController::class, 'toggleActive'])->name('toggle-active');
    });

    Route::prefix('documents/sla-policies')->name('documents.sla-policies.')->group(function () {
        Route::post('/', [DocumentSlaPoliciesController::class, 'store'])->name('store');
        Route::put('{policy}', [DocumentSlaPoliciesController::class, 'update'])->name('update');
        Route::patch('{policy}/toggle', [DocumentSlaPoliciesController::class, 'toggle'])->name('toggle');
        Route::delete('{policy}', [DocumentSlaPoliciesController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('documents/groups')->name('documents.groups.')->group(function () {
        Route::post('/', [DocumentGroupsController::class, 'store'])->name('store');
        Route::put('{group}', [DocumentGroupsController::class, 'update'])->name('update');
        Route::patch('{group}/toggle', [DocumentGroupsController::class, 'toggle'])->name('toggle');
        Route::delete('{group}', [DocumentGroupsController::class, 'destroy'])->name('destroy');
        Route::post('reorder', [DocumentGroupsController::class, 'reorder'])->name('reorder');
        Route::post('{group}/configuration', [DocumentGroupsController::class, 'updateConfiguration'])->name('update-configuration');
    });

    Route::prefix('documents/settings')->name('documents.settings.')->group(function () {
        Route::post('/update', [DocumentSettingsController::class, 'update'])->name('update');
        Route::post('/store', [DocumentSettingsController::class, 'store'])->name('store');
        Route::post('/reset/{group}', [DocumentSettingsController::class, 'resetToDefaults'])->name('reset');
    });

    Route::prefix('documents/blockades')->name('documents.blockades.')->group(function () {
        Route::post('/sync', [ProductBlockadeController::class, 'sync'])->name('sync');
        Route::post('/', [ProductBlockadeController::class, 'store'])->name('store');
        Route::post('/bulk', [ProductBlockadeController::class, 'storeBulk'])->name('store-bulk');
        Route::post('/labels', [ProductBlockadeController::class, 'saveLabels'])->name('save-labels');
        Route::delete('/{id}', [ProductBlockadeController::class, 'destroy'])->name('destroy');
    });

});
