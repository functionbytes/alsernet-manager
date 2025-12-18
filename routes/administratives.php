<?php

use App\Http\Controllers\Administratives\DashboardController;
use App\Http\Controllers\Administratives\Documents\DocumentsController;

Route::group(['prefix' => 'administrative', 'middleware' => ['auth']], function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('administrative.dashboard');

    Route::group(['prefix' => 'documents'], function () {

        Route::get('/', [DocumentsController::class, 'index'])->name('administrative.documents');
        Route::get('/pending', [DocumentsController::class, 'pending'])->name('administrative.documents.pending');
        Route::get('/create', [DocumentsController::class, 'create'])->name('administrative.documents.create');
        Route::post('/store', [DocumentsController::class, 'store'])->name('administrative.documents.store');
        Route::post('/update', [DocumentsController::class, 'update'])->name('administrative.documents.update');
        Route::get('/edit/{slack}', [DocumentsController::class, 'edit'])->name('administrative.documents.edit');
        Route::get('/show/{slack}', [DocumentsController::class, 'show'])->name('administrative.documents.show');
        Route::get('/destroy/{slack}', [DocumentsController::class, 'destroy'])->name('administrative.documents.destroy');

        Route::post('/files', [DocumentsController::class, 'storeFiles'])->name('administrative.documents.files');
        Route::get('/delete/files/{id}', [DocumentsController::class, 'deleteFiles'])->name('administrative.documents.files.delete');
        Route::get('/get/files/{id}', [DocumentsController::class, 'getFiles'])->name('administrative.documents.files.get');

        Route::get('/summary/{id}', [DocumentsController::class, 'summary'])->name('administrative.documents.summary');
        Route::post('/{uid}/resend-reminder', [DocumentsController::class, 'resendReminderEmail'])->name('administrative.documents.resend-reminder');
        Route::post('/{uid}/confirm-upload', [DocumentsController::class, 'confirmDocumentUpload'])->name('administrative.documents.confirm-upload');

        Route::get('/manage/{uid}', [DocumentsController::class, 'manage'])->name('administrative.documents.manage');
        Route::post('/{uid}/send-notification', [DocumentsController::class, 'sendNotificationEmail'])->name('administrative.documents.send-notification');
        Route::post('/{uid}/send-reminder', [DocumentsController::class, 'sendReminderEmail'])->name('administrative.documents.send-reminder');
        Route::post('/{uid}/send-missing', [DocumentsController::class, 'sendMissingDocumentsEmail'])->name('administrative.documents.send-missing');
        Route::post('/{uid}/send-custom-email', [DocumentsController::class, 'sendCustomEmail'])->name('administrative.documents.send-custom-email');
        Route::post('/{uid}/send-upload-confirmation', [DocumentsController::class, 'sendUploadConfirmationEmail'])->name('administrative.documents.send-upload-confirmation');
        Route::post('/{uid}/send-approval', [DocumentsController::class, 'sendApprovalEmail'])->name('administrative.documents.send-approval');
        Route::post('/{uid}/send-rejection', [DocumentsController::class, 'sendRejectionEmail'])->name('administrative.documents.send-rejection');
        Route::post('/{uid}/admin-upload', [DocumentsController::class, 'adminUploadDocument'])->name('administrative.documents.admin-upload');
        Route::post('/sync/fields', [DocumentsController::class, 'syncAllDocumentFields'])->name('administrative.documents.sync-fields');
        Route::get('/{uid}/document-state', [DocumentsController::class, 'getDocumentState'])->name('administrative.documents.state');
        Route::get('/{uid}/refresh-section', [DocumentsController::class, 'refreshDocumentsSection'])->name('administrative.documents.refresh-section');
        Route::get('/{uid}/refresh-action-history', [DocumentsController::class, 'refreshActionHistory'])->name('administrative.documents.refresh-action-history');
        Route::get('/{uid}/missing-documents', [DocumentsController::class, 'getMissingDocuments'])->name('administrative.documents.missing-documents');
        Route::post('/{uid}/delete-single', [DocumentsController::class, 'deleteSingleDocument'])->name('administrative.documents.delete-single');

        // Nested routes - must come BEFORE generic /manage/{uid} route
        Route::post('/manage/{uid}/add-note', [DocumentsController::class, 'addNote'])->name('administrative.documents.add-note');
        Route::put('/manage/{uid}/update-note/{noteId}', [DocumentsController::class, 'updateNote'])->name('administrative.documents.update-note');
        Route::delete('/manage/{uid}/delete-note/{noteId}', [DocumentsController::class, 'deleteNote'])->name('administrative.documents.delete-note');
        Route::get('/manage/{uid}', [DocumentsController::class, 'manage'])->name('administrative.documents.manage');

        Route::get('/sync/all', [DocumentsController::class, 'syncAllDocuments'])->name('administrative.documents.sync.all');
        Route::post('/sync/by-order', [DocumentsController::class, 'syncByOrderId'])->name('administrative.documents.sync.by-order');
        Route::get('/sync/by-order', [DocumentsController::class, 'syncByOrderId'])->name('administrative.documents.sync.by-order.query');
        Route::get('/sync/from-erp', [DocumentsController::class, 'syncFromErp'])->name('administrative.documents.sync.from-erp');

        Route::get('/import', [DocumentsController::class, 'import'])->name('administrative.documents.import');
        Route::get('/import-erp', [DocumentsController::class, 'importFromERP'])->name('administrative.documents.import-erp');

    });

});
