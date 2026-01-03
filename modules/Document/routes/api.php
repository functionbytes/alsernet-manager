<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\Api\DocumentsController;
use Modules\Document\Http\Controllers\Api\DocumentValidationController;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/', [DocumentsController::class, 'process'])->name('process');
    Route::post('/webhooks/prestashop/order-paid', [DocumentsController::class, 'prestashopOrderPaid'])->name('webhooks.prestashop.order-paid');
    Route::post('/resend-reminder', [DocumentsController::class, 'resendDocumentReminder'])->name('resend-reminder');
    Route::post('/confirm-upload', [DocumentsController::class, 'confirmDocumentUpload'])->name('confirm-upload');
    Route::get('/order/data/{order_id}', [DocumentsController::class, 'getOrderData'])->name('order.data');
    Route::post('/fill-order-data', [DocumentsController::class, 'fillDocumentWithOrderData'])->name('fill-order-data');
    Route::get('/sync/all', [DocumentsController::class, 'syncAllDocumentsWithOrders'])->name('sync.all');
    Route::get('/sync/by-query', [DocumentsController::class, 'syncDocumentsByOrderQuery'])->name('sync.by-query');
    Route::post('/sync/by-order', [DocumentsController::class, 'syncDocumentByOrderId'])->name('sync.by-order');
});

Route::middleware(['auth', 'throttle:60,1'])->group(function () {

    // Validation workflow actions
    Route::post('/{uid}/approve-stage', [DocumentValidationController::class, 'approveStage'])->name('approve-stage');
    Route::post('/{uid}/reject-stage', [DocumentValidationController::class, 'rejectStage'])->name('reject-stage');
    Route::post('/{uid}/send-approval', [DocumentValidationController::class, 'sendApproval'])->name('send-approval');
    Route::post('/{uid}/send-rejection', [DocumentValidationController::class, 'sendRejection'])->name('send-rejection');
    Route::post('/{uid}/send-custom-email', [DocumentValidationController::class, 'sendCustomEmail'])->name('send-custom-email');
    Route::post('/{uid}/send-reminder', [DocumentValidationController::class, 'sendReminder'])->name('send-reminder');
    Route::post('/{uid}/request-initial-documents', [DocumentValidationController::class, 'requestInitialDocuments'])->name('request-initial');
    Route::post('/{uid}/request-missing-documents', [DocumentValidationController::class, 'requestMissingDocuments'])->name('request-missing');

    // Document notes
    Route::post('/{uid}/notes', [DocumentValidationController::class, 'addNote'])->name('notes.add');
    Route::put('/notes/{noteId}', [DocumentValidationController::class, 'updateNote'])->name('notes.update');
    Route::delete('/notes/{noteId}', [DocumentValidationController::class, 'deleteNote'])->name('notes.delete');

    // Additional attachments
    Route::post('/{uid}/attachments', [DocumentValidationController::class, 'uploadAttachment'])->name('attachments.upload');
    Route::delete('/attachments/{attachmentId}', [DocumentValidationController::class, 'deleteAttachment'])->name('attachments.delete');

    // History & timeline
    Route::get('/{uid}/action-history', [DocumentValidationController::class, 'getActionHistory'])->name('action-history');
    Route::get('/{uid}/email-history', [DocumentValidationController::class, 'getEmailHistory'])->name('email-history');
    Route::get('/{uid}/status-timeline', [DocumentValidationController::class, 'getStatusTimeline'])->name('status-timeline');

    // Dynamic data for modals
    Route::get('/{uid}/next-stage-info', [DocumentValidationController::class, 'getNextStageInfo'])->name('next-stage-info');
    Route::get('/custom-email-template', [DocumentValidationController::class, 'getCustomEmailTemplate'])->name('custom-email-template');

    // Document operations (moved from role-specific routes)
    Route::post('/sync-fields', [DocumentsController::class, 'syncAllDocumentFields'])->name('sync-fields');
    Route::get('/{uid}/state', [DocumentsController::class, 'getDocumentState'])->name('state');
    Route::delete('/{uid}', [DocumentsController::class, 'deleteSingleDocument'])->name('delete');

    // File operations (moved from role-specific routes)
    Route::post('/{uid}/files', [DocumentsController::class, 'storeFiles'])->name('files.store');
    Route::get('/files/{id}', [DocumentsController::class, 'getFiles'])->name('files.get');
    Route::delete('/files/{id}', [DocumentsController::class, 'deleteFiles'])->name('files.delete');
});
