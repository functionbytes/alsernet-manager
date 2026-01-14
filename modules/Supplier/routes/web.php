<?php

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Http\Controllers\Settings\PromptTemplatesController;
use Modules\Supplier\Http\Controllers\Settings\SupplierAutomationController;
use Modules\Supplier\Http\Controllers\Settings\SupplierCategoriesController;
use Modules\Supplier\Http\Controllers\Settings\SupplierContentController;
use Modules\Supplier\Http\Controllers\Settings\SupplierPromptsController;
use Modules\Supplier\Http\Controllers\Settings\SuppliersController;
use Modules\Supplier\Http\Controllers\Settings\SupplierSourcesController;

Route::group(['prefix' => 'backups/suppliers'], function () {

    // Main supplier routes (specific paths first before parametrized routes)
    Route::get('/', [SuppliersController::class, 'index'])->name('settings.suppliers.index');
    Route::get('/data', [SuppliersController::class, 'getData'])->name('settings.suppliers.data');
    Route::get('/create', [SuppliersController::class, 'create'])->name('settings.suppliers.create');
    Route::post('/', [SuppliersController::class, 'store'])->name('settings.suppliers.store');
    Route::post('/test-all', [SuppliersController::class, 'testAll'])->name('settings.suppliers.test-all');

    // Specific resource routes (before parametrized routes to avoid route collision)
    // Prompts
    Route::prefix('prompts')->name('settings.suppliers.prompts.')->group(function () {
        Route::get('/', [SupplierPromptsController::class, 'index'])->name('index');
        Route::get('/data', [SupplierPromptsController::class, 'getData'])->name('data');
        Route::get('/create', [SupplierPromptsController::class, 'create'])->name('create');
        Route::post('/', [SupplierPromptsController::class, 'store'])->name('store');
        Route::get('/{uid}', [SupplierPromptsController::class, 'show'])->name('show');
        Route::get('/{uid}/edit', [SupplierPromptsController::class, 'edit'])->name('edit');
        Route::put('/{uid}', [SupplierPromptsController::class, 'update'])->name('update');
        Route::delete('/{uid}', [SupplierPromptsController::class, 'destroy'])->name('destroy');
        Route::post('/{uid}/toggle', [SupplierPromptsController::class, 'toggle'])->name('toggle');
        Route::post('/{uid}/test', [SupplierPromptsController::class, 'test'])->name('test');
        Route::post('/{uid}/duplicate', [SupplierPromptsController::class, 'duplicate'])->name('duplicate');
        Route::get('/{uid}/preview', [SupplierPromptsController::class, 'preview'])->name('preview');
        Route::get('/{uid}/metrics', [SupplierPromptsController::class, 'getMetrics'])->name('metrics');
    });

    // Templates
    Route::prefix('templates')->name('settings.suppliers.templates.')->group(function () {
        Route::get('/', [PromptTemplatesController::class, 'index'])->name('index');
        Route::get('/create', [PromptTemplatesController::class, 'create'])->name('create');
        Route::post('/', [PromptTemplatesController::class, 'store'])->name('store');
        Route::get('/{templateUid}/edit', [PromptTemplatesController::class, 'edit'])->name('edit');
        Route::put('/{templateUid}', [PromptTemplatesController::class, 'update'])->name('update');
        Route::delete('/{templateUid}', [PromptTemplatesController::class, 'destroy'])->name('destroy');
        Route::post('/{templateUid}/clone', [PromptTemplatesController::class, 'clone'])->name('clone');
    });

    // Automation
    Route::prefix('automation')->name('settings.suppliers.automation.')->group(function () {

        Route::get('/', [SupplierAutomationController::class, 'index'])->name('index');
        Route::get('/stats', [SupplierAutomationController::class, 'getHealthMetrics'])->name('stats');
        Route::get('/logs', [SupplierAutomationController::class, 'logsPage'])->name('logs');

        // Workflows
        Route::prefix('workflows')->name('workflows.')->group(function () {
            Route::get('/', [SupplierAutomationController::class, 'workflows'])->name('data');
            Route::get('/create', [SupplierAutomationController::class, 'createWorkflow'])->name('create');
            Route::post('/', [SupplierAutomationController::class, 'storeWorkflow'])->name('store');
            Route::get('/{uid}/edit', [SupplierAutomationController::class, 'editWorkflow'])->name('edit');
            Route::put('/{uid}', [SupplierAutomationController::class, 'updateWorkflow'])->name('update');
            Route::delete('/{uid}', [SupplierAutomationController::class, 'destroyWorkflow'])->name('destroy');
            Route::post('/{uid}/run', [SupplierAutomationController::class, 'runWorkflow'])->name('run');
            Route::post('/run-all', [SupplierAutomationController::class, 'runAllWorkflows'])->name('run-all');
        });

        // Executions
        Route::prefix('executions')->name('executions.')->group(function () {
            Route::get('/', [SupplierAutomationController::class, 'executions'])->name('data');
            Route::get('/{uid}', [SupplierAutomationController::class, 'getExecutionDetails'])->name('show');
            Route::post('/{uid}/retry', [SupplierAutomationController::class, 'retryExecution'])->name('retry');
            Route::post('/{uid}/cancel', [SupplierAutomationController::class, 'cancelExecution'])->name('cancel');
            Route::post('/clear-failed', [SupplierAutomationController::class, 'clearFailedExecutions'])->name('clear-failed');
        });

        // Triggers
        Route::prefix('triggers')->name('triggers.')->group(function () {
            Route::get('/', [SupplierAutomationController::class, 'triggers'])->name('data');
        });

        // Alerts
        Route::prefix('alerts')->name('alerts.')->group(function () {
            Route::get('/', [SupplierAutomationController::class, 'alerts'])->name('data');
        });

        // Logs
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [SupplierAutomationController::class, 'getLogs'])->name('data');
            Route::post('/download', [SupplierAutomationController::class, 'downloadLogs'])->name('download');
        });
    });

    // Content
    Route::prefix('content')->name('settings.suppliers.content.')->group(function () {
        Route::get('/', [SupplierContentController::class, 'index'])->name('index');
        Route::get('/data', [SupplierContentController::class, 'getData'])->name('data');
        Route::get('/stats', [SupplierContentController::class, 'getStats'])->name('stats');
        Route::get('/show/{uid}', [SupplierContentController::class, 'show'])->name('show');
        Route::post('/action/{uid}', [SupplierContentController::class, 'action'])->name('action');
        Route::post('/publish/{uid}', [SupplierContentController::class, 'publish'])->name('publish');
        Route::put('/{uid}', [SupplierContentController::class, 'update'])->name('update');
        Route::post('/bulk-action', [SupplierContentController::class, 'bulkAction'])->name('bulk-action');
    });

    // Parametrized supplier routes (after specific routes to avoid collision)
    Route::get('/show/{uid}', [SuppliersController::class, 'show'])->name('settings.suppliers.show');
    Route::get('/edit/{uid}', [SuppliersController::class, 'edit'])->name('settings.suppliers.edit');
    Route::put('/{uid}', [SuppliersController::class, 'update'])->name('settings.suppliers.update');
    Route::delete('/{uid}', [SuppliersController::class, 'destroy'])->name('settings.suppliers.destroy');
    Route::post('/{uid}/toggle', [SuppliersController::class, 'toggle'])->name('settings.suppliers.toggle');

    // Sources
    Route::prefix('{supplierUid}/sources')->name('settings.suppliers.sources.')->group(function () {
        Route::get('/', [SupplierSourcesController::class, 'index'])->name('index');
        Route::get('/create', [SupplierSourcesController::class, 'create'])->name('create');
        Route::post('/', [SupplierSourcesController::class, 'store'])->name('store');
        Route::get('/{uid}/edit', [SupplierSourcesController::class, 'edit'])->name('edit');
        Route::put('/{uid}', [SupplierSourcesController::class, 'update'])->name('update');
        Route::delete('/{uid}', [SupplierSourcesController::class, 'destroy'])->name('destroy');
        Route::post('/{uid}/test', [SupplierSourcesController::class, 'testConnection'])->name('test');
    });

    // Categories
    Route::prefix('{supplierUid}/categories')->name('settings.suppliers.categories.')->group(function () {
        Route::get('/', [SupplierCategoriesController::class, 'index'])->name('index');
        Route::post('/', [SupplierCategoriesController::class, 'store'])->name('store');
        Route::put('/{categoryId}', [SupplierCategoriesController::class, 'update'])->name('update');
        Route::patch('/{categoryId}/toggle', [SupplierCategoriesController::class, 'toggle'])->name('toggle');
        Route::delete('/{categoryId}', [SupplierCategoriesController::class, 'destroy'])->name('destroy');
    });

});
