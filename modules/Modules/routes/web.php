<?php

use Illuminate\Support\Facades\Route;
use Modules\Modules\Http\Controllers\ModulesController;

/*
|--------------------------------------------------------------------------
| Modules Settings Web Routes
|--------------------------------------------------------------------------
|
| Rutas para la gestión de vistas de configuración de módulos
| URL: /backups/modules (aplicado por RouteServiceProvider)
| Middleware: web, auth, role:manager|super-admin (aplicado por RouteServiceProvider)
| SOLO GET para renderizar vistas. POST, PUT, DELETE están en routes/api/backups.php
|
*/

// Modules management routes (Views)
Route::get('/', [ModulesController::class, 'index'])->name('index');
Route::get('/{moduleAlias}', [ModulesController::class, 'show'])->name('show');
Route::get('/{moduleAlias}/edit', [ModulesController::class, 'edit'])->name('edit');
Route::get('/upload/form', [ModulesController::class, 'uploadForm'])->name('uploadForm');
