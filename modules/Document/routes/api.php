<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\Api\DocumentsController;
use Modules\Document\Http\Controllers\Api\DocumentValidationController;

// Public/Webhook routes (external systems, no authentication required)
// Routes will be prefixed and named by the RouteServiceProvider
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/', [DocumentsController::class, 'process'])->name('process');
    // Webhook endpoint for PrestaShop order notifications
    Route::post('/webhooks/prestashop/order-paid', [DocumentsController::class, 'prestashopOrderPaid'])->name('webhooks.prestashop.order-paid');
});

// Authenticated routes - requires user authentication
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    // Document processing and syncing
    Route::get('/order/data/{order_id}', [DocumentsController::class, 'getOrderData'])->name('order.data');
    Route::post('/fill-order-data', [DocumentsController::class, 'fillDocumentWithOrderData'])->name('fill-order-data');
    Route::get('/sync/all', [DocumentsController::class, 'syncAllDocumentsWithOrders'])->name('sync.all');
    Route::get('/sync/by-query', [DocumentsController::class, 'syncDocumentsByOrderQuery'])->name('sync.by-query');
    Route::post('/sync/by-order', [DocumentsController::class, 'syncDocumentByOrderId'])->name('sync.by-order');

    // Document reminders and confirmations
    Route::post('/resend-reminder', [DocumentsController::class, 'resendDocumentReminder'])->name('resend-reminder');
    Route::post('/confirm-upload', [DocumentsController::class, 'confirmDocumentUpload'])->name('confirm-upload');

    // Validation workflow actions
    Route::post('/{uid}/approve-stage', [DocumentValidationController::class, 'approveStage'])->name('approve-stage');
    Route::post('/{uid}/reject-stage', [DocumentValidationController::class, 'rejectStage'])->name('reject-stage');

    // Email operations
    Route::post('/{uid}/send-approval', [DocumentValidationController::class, 'sendApproval'])->name('send-approval');
    Route::post('/{uid}/send-rejection', [DocumentValidationController::class, 'sendRejection'])->name('send-rejection');
    Route::post('/{uid}/send-custom-email', [DocumentValidationController::class, 'sendCustomEmail'])->name('send-custom-email');
    Route::post('/{uid}/send-reminder', [DocumentValidationController::class, 'sendReminder'])->name('send-reminder');
    Route::post('/{uid}/send-notification', [DocumentValidationController::class, 'sendNotification'])->name('send-notification');
    Route::post('/{uid}/send-upload-confirmation', [DocumentValidationController::class, 'sendUploadConfirmation'])->name('send-upload-confirmation');
    Route::post('/{uid}/send-missing', [DocumentValidationController::class, 'sendMissingDocuments'])->name('send-missing');
    Route::post('/{uid}/request-initial-documents', [DocumentValidationController::class, 'requestInitialDocuments'])->name('request-initial');
    Route::post('/{uid}/request-missing-documents', [DocumentValidationController::class, 'requestMissingDocuments'])->name('request-missing');

    // Document notes
    Route::post('/{uid}/notes', [DocumentValidationController::class, 'addNote'])->name('notes.add');
    Route::put('/{uid}/notes/{noteId}', [DocumentValidationController::class, 'updateNote'])->name('notes.update');
    Route::delete('/{uid}/notes/{noteId}', [DocumentValidationController::class, 'deleteNote'])->name('notes.delete');

    // File operations
    Route::post('/{uid}/admin-upload', [DocumentValidationController::class, 'adminUploadDocument'])->name('admin-upload');
    Route::post('/{uid}/attachments', [DocumentValidationController::class, 'uploadAttachment'])->name('attachments.upload');
    Route::post('/{uid}/upload-attachment', [DocumentValidationController::class, 'uploadAdditionalAttachment'])->name('upload-attachment');
    Route::delete('/attachments/{attachmentId}', [DocumentValidationController::class, 'deleteAttachment'])->name('attachments.delete');
    Route::delete('/{uid}/delete-attachment/{mediaId}', [DocumentValidationController::class, 'deleteAdditionalAttachment'])->name('delete-attachment');

    // History & timeline
    Route::get('/{uid}/action-history', [DocumentValidationController::class, 'getActionHistory'])->name('action-history');
    Route::get('/{uid}/email-history', [DocumentValidationController::class, 'getEmailHistory'])->name('email-history');
    Route::get('/{uid}/emails', [DocumentValidationController::class, 'emailHistory'])->name('emails');
    Route::get('/emails/{mailUid}/preview', [DocumentValidationController::class, 'emailPreview'])->name('emails.preview');
    Route::get('/{uid}/status-timeline', [DocumentValidationController::class, 'getStatusTimeline'])->name('status-timeline');
    Route::get('/{uid}/attachments', [DocumentValidationController::class, 'getAdditionalAttachments'])->name('attachments');

    // Dynamic data for modals
    Route::get('/{uid}/next-stage-info', [DocumentValidationController::class, 'getNextStageInfo'])->name('next-stage-info');
    Route::get('/custom-email-template', [DocumentValidationController::class, 'getCustomEmailTemplate'])->name('custom-email-template');

    // Document operations (moved from role-specific routes)
    Route::post('/update', [DocumentsController::class, 'update'])->name('update');
    Route::post('/sync-fields', [DocumentsController::class, 'syncAllDocumentFields'])->name('sync-fields');
    Route::get('/{uid}/state', [DocumentsController::class, 'getDocumentState'])->name('state');
    Route::get('/destroy/{uid}', [DocumentsController::class, 'destroy'])->name('destroy');
    Route::delete('/{uid}', [DocumentsController::class, 'deleteSingleDocument'])->name('delete');

    // Refresh operations
    Route::get('/{uid}/refresh-section', [DocumentsController::class, 'refreshDocumentsSection'])->name('refresh-section');
    Route::get('/{uid}/refresh-action-history', [DocumentsController::class, 'refreshActionHistory'])->name('refresh-action-history');

    // File operations (moved from role-specific routes)
    Route::post('/{uid}/files', [DocumentsController::class, 'storeFiles'])->name('files.store');
    Route::get('/files/{id}', [DocumentsController::class, 'getFiles'])->name('files.get');
    Route::delete('/files/{id}', [DocumentsController::class, 'deleteFiles'])->name('files.delete');
});
