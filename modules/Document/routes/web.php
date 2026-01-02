<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\DocumentConfigurationController;
use Modules\Document\Http\Controllers\DocumentGroupsController;
use Modules\Document\Http\Controllers\DocumentProductBlockadeController;
use Modules\Document\Http\Controllers\DocumentsController;
use Modules\Document\Http\Controllers\DocumentSettingsController;
use Modules\Document\Http\Controllers\DocumentSlaPoliciesController;
use Modules\Document\Http\Controllers\DocumentTypeController;
use Modules\Document\Http\Controllers\DocumentValidationConditionController;

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

    // Listing routes
    Route::get('/', [DocumentsController::class, 'index'])->name('index');
    Route::get('/pending', [DocumentsController::class, 'pending'])->name('pending');

    // Import routes
    Route::get('/import', [DocumentsController::class, 'importIndex'])->name('import');
    Route::get('/import/api', [DocumentsController::class, 'importApi'])->name('import.api');
    Route::get('/import/erp', [DocumentsController::class, 'importErp'])->name('import.erp');

    // Document CRUD
    Route::post('/update', [DocumentsController::class, 'update'])->name('update');
    Route::get('/show/{uid}', [DocumentsController::class, 'show'])->name('show');
    Route::get('/destroy/{uid}', [DocumentsController::class, 'destroy'])->name('destroy');
    Route::get('/summary/{uid}', [DocumentsController::class, 'summary'])->name('summary');

    // Vista principal de gestión
    Route::get('/manage/{uid}', [DocumentsController::class, 'manage'])->name('manage');

    // Email routes
    Route::get('/{uid}/emails', [DocumentsController::class, 'emailHistory'])->name('emails');
    Route::get('/emails/{mailUid}/preview', [DocumentsController::class, 'emailPreview'])->name('emails.preview');
    Route::post('/{uid}/send-notification', [DocumentsController::class, 'sendNotificationEmail'])->name('send-notification');
    Route::post('/{uid}/send-reminder', [DocumentsController::class, 'sendReminderEmail'])->name('send-reminder');
    Route::post('/{uid}/send-custom-email', [DocumentsController::class, 'sendCustomEmail'])->name('send-custom-email');
    Route::post('/{uid}/send-upload-confirmation', [DocumentsController::class, 'sendUploadConfirmationEmail'])->name('send-upload-confirmation');
    Route::post('/{uid}/send-approval', [DocumentsController::class, 'sendApprovalEmail'])->name('send-approval');
    Route::post('/{uid}/send-rejection', [DocumentsController::class, 'sendRejectionEmail'])->name('send-rejection');
    Route::post('/{uid}/send-missing', [DocumentsController::class, 'sendMissingDocumentsEmail'])->name('send-missing');

    // Validation stages
    Route::post('/{uid}/approve-stage', [DocumentsController::class, 'approveStage'])->name('approve-stage');
    Route::post('/{uid}/reject-stage', [DocumentsController::class, 'rejectStage'])->name('reject-stage');

    // File operations
    Route::post('/{uid}/admin-upload', [DocumentsController::class, 'adminUploadDocument'])->name('admin-upload');
    Route::get('/{uid}/attachments', [DocumentsController::class, 'getAdditionalAttachments'])->name('attachments');
    Route::post('/{uid}/upload-attachment', [DocumentsController::class, 'uploadAdditionalAttachment'])->name('upload-attachment');
    Route::post('/{uid}/delete-attachment', [DocumentsController::class, 'deleteAdditionalAttachment'])->name('delete-attachment');

    // Refresh operations
    Route::get('/{uid}/refresh-section', [DocumentsController::class, 'refreshDocumentsSection'])->name('refresh-section');
    Route::get('/{uid}/refresh-action-history', [DocumentsController::class, 'refreshActionHistory'])->name('refresh-action-history');

});

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
        Route::get('/', [DocumentProductBlockadeController::class, 'index'])->name('index');
        Route::get('/status', [DocumentProductBlockadeController::class, 'status'])->name('status');
    });

});
