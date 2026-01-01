<?php

use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\Administratives\DocumentsController;

/*
|--------------------------------------------------------------------------
| Administrative Document Routes
|--------------------------------------------------------------------------
|
| Rutas para la gestión CRUD de documentos desde el perfil Administrative
| Middleware, prefix y name ya están definidos en DocumentsServiceProvider
|
*/

Route::prefix('documents')->name('documents.')->group(function () {

    Route::get('/', [DocumentsController::class, 'index'])->name('index');
    Route::get('/pending', [DocumentsController::class, 'pending'])->name('pending');
    Route::get('/create', [DocumentsController::class, 'create'])->name('create');
    Route::post('/store', [DocumentsController::class, 'store'])->name('store');
    Route::post('/update', [DocumentsController::class, 'update'])->name('update');
    Route::get('/edit/{slack}', [DocumentsController::class, 'edit'])->name('edit');
    Route::get('/show/{slack}', [DocumentsController::class, 'show'])->name('show');
    Route::get('/destroy/{slack}', [DocumentsController::class, 'destroy'])->name('destroy');

    Route::get('/summary/{id}', [DocumentsController::class, 'summary'])->name('summary');

    // Vista principal de gestión
    Route::get('/manage/{uid}', [DocumentsController::class, 'manage'])->name('manage');

    // Operaciones administrativas (solo web, no API)
    Route::post('/{uid}/admin-upload', [DocumentsController::class, 'adminUploadDocument'])->name('admin-upload');
    Route::get('/{uid}/refresh-section', [DocumentsController::class, 'refreshDocumentsSection'])->name('refresh-section');

});
