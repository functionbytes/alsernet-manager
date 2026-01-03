<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\DocumentsController;

/*
|--------------------------------------------------------------------------
| Document Operational Routes
|--------------------------------------------------------------------------
|
| Rutas para la gestión operacional de documentos (día a día)
| Prefix: /documents (aplicado por ServiceProvider)
| Name: documents.* (aplicado por ServiceProvider)
| Middleware: web, auth, role:manager|super-admin
|
*/

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
