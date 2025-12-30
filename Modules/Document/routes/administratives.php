<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\Administratives\DocumentsController;

/*
|--------------------------------------------------------------------------
| Administrative Document Routes
|--------------------------------------------------------------------------
|
| Rutas para la gestión CRUD de documentos desde el perfil Administrative
|
*/

Route::middleware(['auth', 'role:administrative|super-admin'])->prefix('administrative')->name('administrative.')->group(function () {

    Route::group(['prefix' => 'documents'], function () {

        Route::get('/', [DocumentsController::class, 'index'])->name('documents');
        Route::get('/pending', [DocumentsController::class, 'pending'])->name('documents.pending');
        Route::get('/create', [DocumentsController::class, 'create'])->name('documents.create');
        Route::post('/store', [DocumentsController::class, 'store'])->name('documents.store');
        Route::post('/update', [DocumentsController::class, 'update'])->name('documents.update');
        Route::get('/edit/{slack}', [DocumentsController::class, 'edit'])->name('documents.edit');
        Route::get('/show/{slack}', [DocumentsController::class, 'show'])->name('documents.show');
        Route::get('/destroy/{slack}', [DocumentsController::class, 'destroy'])->name('documents.destroy');

        Route::get('/summary/{id}', [DocumentsController::class, 'summary'])->name('documents.summary');

        // Vista principal de gestión
        Route::get('/manage/{uid}', [DocumentsController::class, 'manage'])->name('documents.manage');

        // Operaciones administrativas (solo web, no API)
        Route::post('/{uid}/admin-upload', [DocumentsController::class, 'adminUploadDocument'])->name('documents.admin-upload');
        Route::get('/{uid}/refresh-section', [DocumentsController::class, 'refreshDocumentsSection'])->name('documents.refresh-section');

        /*
         * ═══════════════════════════════════════════════════════════════════════
         * RUTAS MIGRADAS A API - Usar /api/documents en su lugar
         * ═══════════════════════════════════════════════════════════════════════
         *
         * ARCHIVOS (moved to API):
         * - POST   /api/documents/{uid}/files           → route('api.documents.files.store')
         * - GET    /api/documents/files/{id}            → route('api.documents.files.get')
         * - DELETE /api/documents/files/{id}            → route('api.documents.files.delete')
         *
         * ESTADO Y OPERACIONES (moved to API):
         * - POST   /api/documents/sync-fields           → route('api.documents.sync-fields')
         * - GET    /api/documents/{uid}/state           → route('api.documents.state')
         * - DELETE /api/documents/{uid}                 → route('api.documents.delete')
         *
         * TODAS LAS DEMÁS RUTAS API están documentadas en:
         * → Modules/Document/routes/api.php
         * ═══════════════════════════════════════════════════════════════════════
         */

    });
});
