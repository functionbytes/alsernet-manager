<?php

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Http\Controllers\Settings\PromptTemplatesController;
use Modules\Supplier\Http\Controllers\Settings\SupplierAutomationController;
use Modules\Supplier\Http\Controllers\Settings\SupplierCategoriesController;
use Modules\Supplier\Http\Controllers\Settings\SupplierContentController;
use Modules\Supplier\Http\Controllers\Settings\SupplierPromptsController;
use Modules\Supplier\Http\Controllers\Settings\SuppliersController;
use Modules\Supplier\Http\Controllers\Settings\SupplierSourcesController;
use Modules\Supplier\Http\Controllers\Settings\SupplierSyncController;
use Modules\Supplier\Http\Controllers\Settings\SupplierSyncFailuresController;

/*
|--------------------------------------------------------------------------
| Supplier Routes - Operational & Configuration
|--------------------------------------------------------------------------
|
| Rutas para la gestión operacional y configuración de proveedores
| Operational routes use /suppliers prefix
| Configuration routes use /settings/suppliers prefix
| Middleware: web, auth, permission-based access
| Authorization: Handled in controllers via traits
|
*/

Route::middleware(['web', 'auth'])->group(function () {

    // ====================================================================
    // OPERATIONAL ROUTES - /suppliers
    // ====================================================================
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        // Listing routes
        Route::get('/', [SuppliersController::class, 'index'])->name('index');
        Route::get('/data', [SuppliersController::class, 'getData'])->name('data');

        // Supplier detail views
        Route::get('/show/{uid}', [SuppliersController::class, 'show'])->name('show');
        Route::get('/edit/{uid}', [SuppliersController::class, 'edit'])->name('edit');

        // Supplier management
        Route::put('/{uid}', [SuppliersController::class, 'update'])->name('update');
        Route::delete('/{uid}', [SuppliersController::class, 'destroy'])->name('destroy');
        Route::post('/{uid}/toggle', [SuppliersController::class, 'toggle'])->name('toggle');
        Route::post('/test-all', [SuppliersController::class, 'testAll'])->name('test-all');

        // Supplier sources (per supplier)
        Route::prefix('{supplierUid}/sources')->name('sources.')->group(function () {
            Route::get('/', [SupplierSourcesController::class, 'index'])->name('index');
            Route::get('/create', [SupplierSourcesController::class, 'create'])->name('create');
            Route::post('/', [SupplierSourcesController::class, 'store'])->name('store');
            Route::get('/{uid}/edit', [SupplierSourcesController::class, 'edit'])->name('edit');
            Route::put('/{uid}', [SupplierSourcesController::class, 'update'])->name('update');
            Route::delete('/{uid}', [SupplierSourcesController::class, 'destroy'])->name('destroy');
            Route::post('/{uid}/test', [SupplierSourcesController::class, 'testConnection'])->name('test');
        });

        // Supplier categories (per supplier)
        Route::prefix('{supplierUid}/categories')->name('categories.')->group(function () {
            Route::get('/', [SupplierCategoriesController::class, 'index'])->name('index');
            Route::post('/', [SupplierCategoriesController::class, 'store'])->name('store');
            Route::put('/{categoryId}', [SupplierCategoriesController::class, 'update'])->name('update');
            Route::patch('/{categoryId}/toggle', [SupplierCategoriesController::class, 'toggle'])->name('toggle');
            Route::delete('/{categoryId}', [SupplierCategoriesController::class, 'destroy'])->name('destroy');
        });

    });

    // ====================================================================
    // CONFIGURATION ROUTES - /settings/suppliers (Admin only)
    // ====================================================================
    Route::prefix('settings/suppliers')->name('settings.suppliers.')
        ->middleware(['role:super-admin'])
        ->group(function () {

            // Create new supplier
            Route::get('/create', [SuppliersController::class, 'create'])->name('create');
            Route::post('/', [SuppliersController::class, 'store'])->name('store');

            // ================================================================
            // PROMPTS - Configuration
            // ================================================================
            Route::prefix('prompts')->name('prompts.')->group(function () {
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

            // ================================================================
            // TEMPLATES - Configuration
            // ================================================================
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [PromptTemplatesController::class, 'index'])->name('index');
                Route::get('/create', [PromptTemplatesController::class, 'create'])->name('create');
                Route::post('/', [PromptTemplatesController::class, 'store'])->name('store');
                Route::get('/{templateUid}/edit', [PromptTemplatesController::class, 'edit'])->name('edit');
                Route::put('/{templateUid}', [PromptTemplatesController::class, 'update'])->name('update');
                Route::delete('/{templateUid}', [PromptTemplatesController::class, 'destroy'])->name('destroy');
                Route::post('/{templateUid}/clone', [PromptTemplatesController::class, 'clone'])->name('clone');
            });

            // ================================================================
            // AUTOMATION - Configuration
            // ================================================================
            Route::prefix('automation')->name('automation.')->group(function () {

                Route::get('/', [SupplierAutomationController::class, 'index'])->name('index');
                Route::get('/stats', [SupplierAutomationController::class, 'getHealthMetrics'])->name('stats');

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

                // Logs - Page & API endpoints
                Route::get('/logs', [SupplierAutomationController::class, 'logsPage'])->name('logs');
                Route::prefix('logs/api')->name('logs.')->group(function () {
                    Route::get('/', [SupplierAutomationController::class, 'getLogs'])->name('data');
                    Route::post('/download', [SupplierAutomationController::class, 'downloadLogs'])->name('download');
                });
            });

            // ================================================================
            // CONTENT - Configuration
            // ================================================================
            Route::prefix('content')->name('content.')->group(function () {
                Route::get('/', [SupplierContentController::class, 'index'])->name('index');
                Route::get('/data', [SupplierContentController::class, 'getData'])->name('data');
                Route::get('/stats', [SupplierContentController::class, 'getStats'])->name('stats');
                Route::get('/show/{uid}', [SupplierContentController::class, 'show'])->name('show');
                Route::post('/action/{uid}', [SupplierContentController::class, 'action'])->name('action');
                Route::post('/publish/{uid}', [SupplierContentController::class, 'publish'])->name('publish');
                Route::put('/{uid}', [SupplierContentController::class, 'update'])->name('update');
                Route::post('/bulk-action', [SupplierContentController::class, 'bulkAction'])->name('bulk-action');
            });

            // ================================================================
            // SYNCHRONIZATION - Configuration
            // ================================================================
            Route::prefix('sync')->name('sync.')->group(function () {
                Route::get('/', [SupplierSyncController::class, 'index'])->name('index');
                Route::get('/{statusId}', [SupplierSyncController::class, 'show'])->name('show');
                Route::post('/start', [SupplierSyncController::class, 'startSync'])->name('start');
                Route::post('/cancel/{statusId}', [SupplierSyncController::class, 'cancelSync'])->name('cancel');
                Route::post('/retry/{statusId}', [SupplierSyncController::class, 'retrySync'])->name('retry');
            });

            // ================================================================
            // SYNC FAILURES - Configuration
            // ================================================================
            Route::prefix('sync-failures')->name('sync-failures.')->group(function () {
                Route::get('/', [SupplierSyncFailuresController::class, 'index'])->name('index');
                Route::post('/{id}/retry', [SupplierSyncFailuresController::class, 'retry'])->name('retry');
                Route::post('/bulk-retry', [SupplierSyncFailuresController::class, 'bulkRetry'])->name('bulk-retry');
                Route::delete('/{id}', [SupplierSyncFailuresController::class, 'destroy'])->name('destroy');
                Route::delete('/bulk-delete', [SupplierSyncFailuresController::class, 'bulkDestroy'])->name('bulk-delete');
                Route::get('/conflicts/{id}', [SupplierSyncFailuresController::class, 'showConflict'])->name('conflicts.show');
            });

        });

});
