<?php

use App\Http\Controllers\Weapons\DashboardController;
use App\Http\Controllers\Weapons\Documents\DocumentsController;

Route::group(['prefix' => 'weapons', 'middleware' => []], function () {

    Route::get('/dashboard/v4', [DashboardController::class, 'dashboard'])->name('weapons.dashboard.v4');
    Route::get('/dashboard/v1', [DashboardController::class, 'dashboardV1'])->name('weapons.dashboard.v1');
    Route::get('/dashboard/v2', [DashboardController::class, 'dashboardV2'])->name('weapons.dashboard.v2');
    Route::get('/dashboard/v3', [DashboardController::class, 'dashboardV3'])->name('weapons.dashboard.v3');
    Route::get('/', [DashboardController::class, 'dashboardV4'])->name('weapons.dashboard');

    Route::group(['prefix' => 'documents'], function () {

        Route::get('/', [DocumentsController::class, 'index'])->name('weapons.documents');
        Route::get('/pending', [DocumentsController::class, 'pending'])->name('weapons.documents.pending');
        Route::get('/create', [DocumentsController::class, 'create'])->name('weapons.documents.create');
        Route::post('/store', [DocumentsController::class, 'store'])->name('weapons.documents.store');
        Route::post('/update', [DocumentsController::class, 'update'])->name('weapons.documents.update');
        Route::get('/edit/{slack}', [DocumentsController::class, 'edit'])->name('weapons.documents.edit');
        Route::get('/show/{slack}', [DocumentsController::class, 'show'])->name('weapons.documents.show');
        Route::get('/destroy/{slack}', [DocumentsController::class, 'destroy'])->name('weapons.documents.destroy');

        Route::post('/files', [DocumentsController::class, 'storeFiles'])->name('weapons.documents.files');
        Route::get('/delete/files/{id}', [DocumentsController::class, 'deleteFiles'])->name('weapons.documents.files.delete');
        Route::get('/get/files/{id}', [DocumentsController::class, 'getFiles'])->name('weapons.documents.files.get');

        Route::get('/summary/{id}', [DocumentsController::class, 'summary'])->name('weapons.documents.summary');
        Route::post('/{uid}/resend-reminder', [DocumentsController::class, 'resendReminderEmail'])->name('weapons.documents.resend-reminder');
        Route::post('/{uid}/confirm-upload', [DocumentsController::class, 'confirmDocumentUpload'])->name('weapons.documents.confirm-upload');

        Route::get('/manage/{uid}', [DocumentsController::class, 'manage'])->name('weapons.documents.manage');
        Route::post('/{uid}/send-notification', [DocumentsController::class, 'sendNotificationEmail'])->name('weapons.documents.send-notification');
        Route::post('/{uid}/send-reminder', [DocumentsController::class, 'sendReminderEmail'])->name('weapons.documents.send-reminder');
        Route::post('/{uid}/send-missing', [DocumentsController::class, 'sendMissingDocumentsEmail'])->name('weapons.documents.send-missing');
        Route::post('/{uid}/send-custom-email', [DocumentsController::class, 'sendCustomEmail'])->name('weapons.documents.send-custom-email');
        Route::post('/{uid}/send-upload-confirmation', [DocumentsController::class, 'sendUploadConfirmationEmail'])->name('weapons.documents.send-upload-confirmation');
        Route::post('/{uid}/send-approval', [DocumentsController::class, 'sendApprovalEmail'])->name('weapons.documents.send-approval');
        Route::post('/{uid}/send-rejection', [DocumentsController::class, 'sendRejectionEmail'])->name('weapons.documents.send-rejection');
        Route::post('/{uid}/admin-upload', [DocumentsController::class, 'adminUploadDocument'])->name('weapons.documents.admin-upload');
        Route::post('/sync/fields', [DocumentsController::class, 'syncAllDocumentFields'])->name('weapons.documents.sync-fields');
        Route::get('/{uid}/document-state', [DocumentsController::class, 'getDocumentState'])->name('weapons.documents.state');
        Route::get('/{uid}/refresh-section', [DocumentsController::class, 'refreshDocumentsSection'])->name('weapons.documents.refresh-section');
        Route::get('/{uid}/refresh-action-history', [DocumentsController::class, 'refreshActionHistory'])->name('weapons.documents.refresh-action-history');
        Route::get('/{uid}/missing-documents', [DocumentsController::class, 'getMissingDocuments'])->name('weapons.documents.missing-documents');
        Route::post('/{uid}/delete-single', [DocumentsController::class, 'deleteSingleDocument'])->name('weapons.documents.delete-single');

        // Nested routes - must come BEFORE generic /manage/{uid} route
        Route::post('/manage/{uid}/add-note', [DocumentsController::class, 'addNote'])->name('weapons.documents.add-note');
        Route::put('/manage/{uid}/update-note/{noteId}', [DocumentsController::class, 'updateNote'])->name('weapons.documents.update-note');
        Route::delete('/manage/{uid}/delete-note/{noteId}', [DocumentsController::class, 'deleteNote'])->name('weapons.documents.delete-note');
        Route::get('/manage/{uid}', [DocumentsController::class, 'manage'])->name('weapons.documents.manage');

        Route::get('/sync/all', [DocumentsController::class, 'syncAllDocuments'])->name('weapons.documents.sync.all');
        Route::post('/sync/by-order', [DocumentsController::class, 'syncByOrderId'])->name('weapons.documents.sync.by-order');
        Route::get('/sync/by-order', [DocumentsController::class, 'syncByOrderId'])->name('weapons.documents.sync.by-order.query');
        Route::get('/sync/from-erp', [DocumentsController::class, 'syncFromErp'])->name('weapons.documents.sync.from-erp.query');
        Route::post('/sync/from-erp', [DocumentsController::class, 'syncFromErp'])->name('weapons.documents.sync.from-erp');

        // Import routes
        Route::get('/import', [DocumentsController::class, 'importIndex'])->name('weapons.documents.import');
        Route::get('/import/api', [DocumentsController::class, 'importApi'])->name('weapons.documents.import.api');
        Route::get('/import/erp', [DocumentsController::class, 'importErp'])->name('weapons.documents.import.erp');

        // Email history routes
        Route::get('/{uid}/emails', [DocumentsController::class, 'emailHistory'])->name('weapons.documents.emails');
        Route::get('/emails/preview/{mailUid}', [DocumentsController::class, 'emailPreview'])->name('weapons.documents.emails.preview');

    });

});
