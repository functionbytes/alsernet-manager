<?php

use App\Http\Controllers\Accountings\DashboardController;
use App\Http\Controllers\Accountings\Documents\DocumentsController;

Route::group(['prefix' => 'accounting', 'middleware' => []], function () {

    Route::get('/dashboard/v4', [DashboardController::class, 'dashboard'])->name('accounting.dashboard.v4');
    Route::get('/dashboard/v1', [DashboardController::class, 'dashboardV1'])->name('accounting.dashboard.v1');
    Route::get('/dashboard/v2', [DashboardController::class, 'dashboardV2'])->name('accounting.dashboard.v2');
    Route::get('/dashboard/v3', [DashboardController::class, 'dashboardV3'])->name('accounting.dashboard.v3');
    Route::get('/', [DashboardController::class, 'dashboardV4'])->name('accounting.dashboard');

    Route::group(['prefix' => 'documents'], function () {

        Route::get('/', [DocumentsController::class, 'index'])->name('accounting.documents');
        Route::get('/pending', [DocumentsController::class, 'pending'])->name('accounting.documents.pending');
        Route::get('/create', [DocumentsController::class, 'create'])->name('accounting.documents.create');
        Route::post('/store', [DocumentsController::class, 'store'])->name('accounting.documents.store');
        Route::post('/update', [DocumentsController::class, 'update'])->name('accounting.documents.update');
        Route::get('/edit/{slack}', [DocumentsController::class, 'edit'])->name('accounting.documents.edit');
        Route::get('/show/{slack}', [DocumentsController::class, 'show'])->name('accounting.documents.show');
        Route::get('/destroy/{slack}', [DocumentsController::class, 'destroy'])->name('accounting.documents.destroy');

        Route::post('/files', [DocumentsController::class, 'storeFiles'])->name('accounting.documents.files');
        Route::get('/delete/files/{id}', [DocumentsController::class, 'deleteFiles'])->name('accounting.documents.files.delete');
        Route::get('/get/files/{id}', [DocumentsController::class, 'getFiles'])->name('accounting.documents.files.get');

        Route::get('/summary/{id}', [DocumentsController::class, 'summary'])->name('accounting.documents.summary');
        Route::post('/{uid}/resend-reminder', [DocumentsController::class, 'resendReminderEmail'])->name('accounting.documents.resend-reminder');
        Route::post('/{uid}/confirm-upload', [DocumentsController::class, 'confirmDocumentUpload'])->name('accounting.documents.confirm-upload');

        Route::get('/manage/{uid}', [DocumentsController::class, 'manage'])->name('accounting.documents.manage');
        Route::post('/{uid}/send-notification', [DocumentsController::class, 'sendNotificationEmail'])->name('accounting.documents.send-notification');
        Route::post('/{uid}/send-reminder', [DocumentsController::class, 'sendReminderEmail'])->name('accounting.documents.send-reminder');
        Route::post('/{uid}/send-missing', [DocumentsController::class, 'sendMissingDocumentsEmail'])->name('accounting.documents.send-missing');
        Route::post('/{uid}/send-custom-email', [DocumentsController::class, 'sendCustomEmail'])->name('accounting.documents.send-custom-email');
        Route::post('/{uid}/send-upload-confirmation', [DocumentsController::class, 'sendUploadConfirmationEmail'])->name('accounting.documents.send-upload-confirmation');
        Route::post('/{uid}/send-approval', [DocumentsController::class, 'sendApprovalEmail'])->name('accounting.documents.send-approval');
        Route::post('/{uid}/send-rejection', [DocumentsController::class, 'sendRejectionEmail'])->name('accounting.documents.send-rejection');
        Route::post('/{uid}/admin-upload', [DocumentsController::class, 'adminUploadDocument'])->name('accounting.documents.admin-upload');
        Route::post('/sync/fields', [DocumentsController::class, 'syncAllDocumentFields'])->name('accounting.documents.sync-fields');
        Route::get('/{uid}/document-state', [DocumentsController::class, 'getDocumentState'])->name('accounting.documents.state');
        Route::get('/{uid}/refresh-section', [DocumentsController::class, 'refreshDocumentsSection'])->name('accounting.documents.refresh-section');
        Route::get('/{uid}/refresh-action-history', [DocumentsController::class, 'refreshActionHistory'])->name('accounting.documents.refresh-action-history');
        Route::get('/{uid}/missing-documents', [DocumentsController::class, 'getMissingDocuments'])->name('accounting.documents.missing-documents');
        Route::post('/{uid}/delete-single', [DocumentsController::class, 'deleteSingleDocument'])->name('accounting.documents.delete-single');

        // Nested routes - must come BEFORE generic /manage/{uid} route
        Route::post('/manage/{uid}/add-note', [DocumentsController::class, 'addNote'])->name('accounting.documents.add-note');
        Route::put('/manage/{uid}/update-note/{noteId}', [DocumentsController::class, 'updateNote'])->name('accounting.documents.update-note');
        Route::delete('/manage/{uid}/delete-note/{noteId}', [DocumentsController::class, 'deleteNote'])->name('accounting.documents.delete-note');
        Route::get('/manage/{uid}', [DocumentsController::class, 'manage'])->name('accounting.documents.manage');

        Route::get('/sync/all', [DocumentsController::class, 'syncAllDocuments'])->name('accounting.documents.sync.all');
        Route::post('/sync/by-order', [DocumentsController::class, 'syncByOrderId'])->name('accounting.documents.sync.by-order');
        Route::get('/sync/by-order', [DocumentsController::class, 'syncByOrderId'])->name('accounting.documents.sync.by-order.query');
        Route::get('/sync/from-erp', [DocumentsController::class, 'syncFromErp'])->name('accounting.documents.sync.from-erp.query');
        Route::post('/sync/from-erp', [DocumentsController::class, 'syncFromErp'])->name('accounting.documents.sync.from-erp');

        // Import routes
        Route::get('/import', [DocumentsController::class, 'importIndex'])->name('accounting.documents.import');
        Route::get('/import/api', [DocumentsController::class, 'importApi'])->name('accounting.documents.import.api');
        Route::get('/import/erp', [DocumentsController::class, 'importErp'])->name('accounting.documents.import.erp');

        // Email history routes
        Route::get('/{uid}/emails', [DocumentsController::class, 'emailHistory'])->name('accounting.documents.emails');
        Route::get('/emails/preview/{mailUid}', [DocumentsController::class, 'emailPreview'])->name('accounting.documents.emails.preview');

    });

});
