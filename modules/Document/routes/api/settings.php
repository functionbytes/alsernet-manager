<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\DocumentConfigurationController;
use Modules\Document\Http\Controllers\DocumentGroupsController;
use Modules\Document\Http\Controllers\DocumentProductBlockadeController;
use Modules\Document\Http\Controllers\DocumentSettingsController;
use Modules\Document\Http\Controllers\DocumentSlaPoliciesController;
use Modules\Document\Http\Controllers\DocumentTypeController;
use Modules\Document\Http\Controllers\DocumentValidationConditionController;

/*
|--------------------------------------------------------------------------
| Document Settings API Routes
|--------------------------------------------------------------------------
|
| Rutas API para la gestión de configuraciones de documentos (POST, PUT, DELETE)
| Solo para acciones de datos. Las vistas GET están en web.php
| Middleware, prefix y name base ya están definidos en DocumentsServiceProvider
|
*/

Route::middleware('can:view-document-settings')->prefix('documents/configurations')->name('configurations.')->group(function () {
    Route::post('/', [DocumentConfigurationController::class, 'updateGlobalSettings'])->name('update');
    Route::post('/storage', [DocumentConfigurationController::class, 'updateStorageSettings'])->name('storage.update');
    Route::post('/storage/test', [DocumentConfigurationController::class, 'testStorageConnection'])->name('storage.test');
});

Route::middleware('can:manage-document-types')->prefix('documents/types')->name('types.')->group(function () {
    Route::post('/', [DocumentTypeController::class, 'store'])->name('store');
    Route::put('/{documentType}', [DocumentTypeController::class, 'update'])->name('update');
    Route::delete('/{documentType}', [DocumentTypeController::class, 'destroy'])->name('destroy');
    Route::post('/{documentType}/toggle-active', [DocumentTypeController::class, 'toggleActive'])->name('toggle-active');
});

Route::middleware('can:manage-document-conditions')->prefix('documents/conditions')->name('conditions.')->group(function () {
    Route::post('/', [DocumentValidationConditionController::class, 'store'])->name('store');
    Route::put('/{condition}', [DocumentValidationConditionController::class, 'update'])->name('update');
    Route::delete('/{condition}', [DocumentValidationConditionController::class, 'destroy'])->name('destroy');
    Route::post('/{condition}/toggle-active', [DocumentValidationConditionController::class, 'toggleActive'])->name('toggle-active');
});

Route::middleware('can:manage-document-sla-policies')->prefix('documents/sla-policies')->name('sla-policies.')->group(function () {
    Route::post('/', [DocumentSlaPoliciesController::class, 'store'])->name('store');
    Route::put('{policy}', [DocumentSlaPoliciesController::class, 'update'])->name('update');
    Route::patch('{policy}/toggle', [DocumentSlaPoliciesController::class, 'toggle'])->name('toggle');
    Route::delete('{policy}', [DocumentSlaPoliciesController::class, 'destroy'])->name('destroy');
});

Route::middleware('can:manage-document-groups')->prefix('documents/groups')->name('groups.')->group(function () {
    Route::post('/', [DocumentGroupsController::class, 'store'])->name('store');
    Route::put('{group}', [DocumentGroupsController::class, 'update'])->name('update');
    Route::patch('{group}/toggle', [DocumentGroupsController::class, 'toggle'])->name('toggle');
    Route::delete('{group}', [DocumentGroupsController::class, 'destroy'])->name('destroy');
    Route::post('reorder', [DocumentGroupsController::class, 'reorder'])->name('reorder');
    Route::post('{group}/configuration', [DocumentGroupsController::class, 'updateConfiguration'])->name('update-configuration');
});

Route::middleware('can:view-document-settings')->prefix('documents/settings')->name('settings.')->group(function () {
    Route::post('/update', [DocumentSettingsController::class, 'update'])->name('update');
    Route::post('/store', [DocumentSettingsController::class, 'store'])->name('store');
    Route::post('/reset/{group}', [DocumentSettingsController::class, 'resetToDefaults'])->name('reset');
});

Route::middleware('can:manage-document-blockades')->prefix('documents/blockades')->name('blockades.')->group(function () {
    Route::post('/sync', [DocumentProductBlockadeController::class, 'sync'])->name('sync');
    Route::post('/', [DocumentProductBlockadeController::class, 'store'])->name('store');
    Route::post('/bulk', [DocumentProductBlockadeController::class, 'storeBulk'])->name('store-bulk');
    Route::post('/labels', [DocumentProductBlockadeController::class, 'saveLabels'])->name('save-labels');
    Route::delete('/{id}', [DocumentProductBlockadeController::class, 'destroy'])->name('destroy');
});
