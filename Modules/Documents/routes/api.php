<?php

use Illuminate\Support\Facades\Route;
use Modules\Documents\Http\Controllers\Api\DocumentsController;
use Modules\Documents\Http\Controllers\Api\DocumentValidationController;

/*
|--------------------------------------------------------------------------
| API Document Routes
|--------------------------------------------------------------------------
|
| Rutas API para el procesamiento y sincronización de documentos
|
*/

// Public API routes (webhooks, external processing)
Route::prefix('api/documents')->middleware('throttle:60,1')->group(function () {
    Route::post('/', [DocumentsController::class, 'process']);
    Route::post('/webhooks/prestashop/order-paid', [DocumentsController::class, 'prestashopOrderPaid']);
    Route::post('/resend-reminder', [DocumentsController::class, 'resendDocumentReminder']);
    Route::post('/confirm-upload', [DocumentsController::class, 'confirmDocumentUpload']);
    Route::get('/order/data/{order_id}', [DocumentsController::class, 'getOrderData']);
    Route::post('/fill-order-data', [DocumentsController::class, 'fillDocumentWithOrderData']);
    Route::get('/sync/all', [DocumentsController::class, 'syncAllDocumentsWithOrders']);
    Route::get('/sync/by-query', [DocumentsController::class, 'syncDocumentsByOrderQuery']);
    Route::post('/sync/by-order', [DocumentsController::class, 'syncDocumentByOrderId']);
});

/*
|--------------------------------------------------------------------------
| Authenticated API Routes - Generic for all profiles
|--------------------------------------------------------------------------
|
| El backend detecta automáticamente el perfil del usuario y filtra/valida
| según corresponda. NO se necesita duplicar rutas por perfil.
|
*/
Route::prefix('api/documents')->middleware(['auth', 'throttle:60,1'])->group(function () {

    // Validation workflow actions
    Route::post('/{uid}/approve-stage', [DocumentValidationController::class, 'approveStage'])->name('api.documents.approve-stage');
    Route::post('/{uid}/reject-stage', [DocumentValidationController::class, 'rejectStage'])->name('api.documents.reject-stage');
    Route::post('/{uid}/send-approval', [DocumentValidationController::class, 'sendApproval'])->name('api.documents.send-approval');
    Route::post('/{uid}/send-rejection', [DocumentValidationController::class, 'sendRejection'])->name('api.documents.send-rejection');
    Route::post('/{uid}/send-custom-email', [DocumentValidationController::class, 'sendCustomEmail'])->name('api.documents.send-custom-email');
    Route::post('/{uid}/send-reminder', [DocumentValidationController::class, 'sendReminder'])->name('api.documents.send-reminder');
    Route::post('/{uid}/request-initial-documents', [DocumentValidationController::class, 'requestInitialDocuments'])->name('api.documents.request-initial');
    Route::post('/{uid}/request-missing-documents', [DocumentValidationController::class, 'requestMissingDocuments'])->name('api.documents.request-missing');

    // Document notes
    Route::post('/{uid}/notes', [DocumentValidationController::class, 'addNote'])->name('api.documents.notes.add');
    Route::put('/notes/{noteId}', [DocumentValidationController::class, 'updateNote'])->name('api.documents.notes.update');
    Route::delete('/notes/{noteId}', [DocumentValidationController::class, 'deleteNote'])->name('api.documents.notes.delete');

    // Additional attachments
    Route::post('/{uid}/attachments', [DocumentValidationController::class, 'uploadAttachment'])->name('api.documents.attachments.upload');
    Route::delete('/attachments/{attachmentId}', [DocumentValidationController::class, 'deleteAttachment'])->name('api.documents.attachments.delete');

    // History & timeline
    Route::get('/{uid}/action-history', [DocumentValidationController::class, 'getActionHistory'])->name('api.documents.action-history');
    Route::get('/{uid}/email-history', [DocumentValidationController::class, 'getEmailHistory'])->name('api.documents.email-history');
    Route::get('/{uid}/status-timeline', [DocumentValidationController::class, 'getStatusTimeline'])->name('api.documents.status-timeline');

    // Dynamic data for modals
    Route::get('/{uid}/next-stage-info', [DocumentValidationController::class, 'getNextStageInfo'])->name('api.documents.next-stage-info');
    Route::get('/custom-email-template', [DocumentValidationController::class, 'getCustomEmailTemplate'])->name('api.documents.custom-email-template');

    // Document operations (moved from role-specific routes)
    Route::post('/sync-fields', [DocumentsController::class, 'syncAllDocumentFields'])->name('api.documents.sync-fields');
    Route::get('/{uid}/state', [DocumentsController::class, 'getDocumentState'])->name('api.documents.state');
    Route::delete('/{uid}', [DocumentsController::class, 'deleteSingleDocument'])->name('api.documents.delete');

    // File operations (moved from role-specific routes)
    Route::post('/{uid}/files', [DocumentsController::class, 'storeFiles'])->name('api.documents.files.store');
    Route::get('/files/{id}', [DocumentsController::class, 'getFiles'])->name('api.documents.files.get');
    Route::delete('/files/{id}', [DocumentsController::class, 'deleteFiles'])->name('api.documents.files.delete');
});
