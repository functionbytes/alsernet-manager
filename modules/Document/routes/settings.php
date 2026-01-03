<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\DocumentConfigurationController;
use Modules\Document\Http\Controllers\DocumentGroupsController;
use Modules\Document\Http\Controllers\DocumentProductBlockadeController;
use Modules\Document\Http\Controllers\DocumentSettingsController;
use Modules\Document\Http\Controllers\DocumentSlaPoliciesController;
use Modules\Document\Http\Controllers\DocumentTypeController;
use Modules\Document\Http\Controllers\DocumentValidationConditionController;
use Modules\Document\Http\Controllers\Settings\StageEmailActionController;

/*
|--------------------------------------------------------------------------
| Document Configuration Routes
|--------------------------------------------------------------------------
|
| Rutas para la configuración del sistema de documentos
| Prefix: /settings/documents (aplicado por RouteServiceProvider)
| Name: settings.documents.* (aplicado por RouteServiceProvider)
| Middleware: web, auth, role:manager|super-admin
|
*/

Route::middleware('can:view-document-settings')->group(function () {

    Route::get('/', [DocumentConfigurationController::class, 'index'])->name('configurations');

    Route::prefix('configurations')->name('configurations.')->group(function () {
        Route::get('/', [DocumentConfigurationController::class, 'globalSettings'])->name('global');
        Route::get('/search-templates', [DocumentConfigurationController::class, 'searchTemplates'])->name('search-templates');

        // Storage configuration routes
        Route::get('/storage', [DocumentConfigurationController::class, 'storageSettings'])->name('storage');
        Route::get('/storage/stats/{diskName}', [DocumentConfigurationController::class, 'getStorageStats'])->name('storage.stats');
        Route::get('/storage/history', [DocumentConfigurationController::class, 'getStorageConfigurationHistory'])->name('storage.history');
    });

    Route::middleware('can:manage-document-types')->prefix('types')->name('types.')->group(function () {
        Route::get('/', [DocumentTypeController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTypeController::class, 'create'])->name('create');
        Route::get('/edit/{documentType}', [DocumentTypeController::class, 'edit'])->name('edit');
        Route::get('/export/all', [DocumentTypeController::class, 'export'])->name('export');
    });

    // Validation Conditions
    Route::middleware('can:manage-document-conditions')->prefix('conditions')->name('conditions.')->group(function () {
        Route::get('/', [DocumentValidationConditionController::class, 'index'])->name('index');
        Route::get('/create', [DocumentValidationConditionController::class, 'create'])->name('create');
        Route::get('/edit/{condition}', [DocumentValidationConditionController::class, 'edit'])->name('edit');
    });

    // SLA Policies
    Route::middleware('can:manage-document-sla-policies')->prefix('sla-policies')->name('sla-policies.')->group(function () {
        Route::get('/', [DocumentSlaPoliciesController::class, 'index'])->name('index');
        Route::get('create', [DocumentSlaPoliciesController::class, 'create'])->name('create');
        Route::get('{policy}', [DocumentSlaPoliciesController::class, 'show'])->name('show');
        Route::get('/edit/{policy}', [DocumentSlaPoliciesController::class, 'edit'])->name('edit');
    });

    // Document Groups (Validator Groups)
    Route::middleware('can:manage-document-groups')->prefix('groups')->name('groups.')->group(function () {
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
    Route::middleware('can:manage-document-blockades')->prefix('blockades')->name('blockades.')->group(function () {
        Route::get('/', [DocumentProductBlockadeController::class, 'index'])->name('index');
        Route::get('/status', [DocumentProductBlockadeController::class, 'status'])->name('status');
    });

    // Stage email action routes
    Route::prefix('stage-email-actions')->name('stage-email-actions.')->group(function () {
        Route::get('/', [StageEmailActionController::class, 'index'])->name('index');
        Route::get('/{stage}/edit', [StageEmailActionController::class, 'edit'])->name('edit');
        Route::post('/{stage}/update', [StageEmailActionController::class, 'update'])->name('update');
        Route::post('/store', [StageEmailActionController::class, 'store'])->name('store');
        Route::delete('/{id}', [StageEmailActionController::class, 'destroy'])->name('destroy');
    });

});
