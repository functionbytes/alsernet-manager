<?php

use Illuminate\Support\Facades\Route;
use Modules\Modules\Http\Controllers\ModulesController;

/*
|--------------------------------------------------------------------------
| Modules Settings API Routes
|--------------------------------------------------------------------------
|
| Rutas API para la gestión de módulos en el panel de administración
| Operaciones: PUT (actualizar), POST (instalar), DELETE (desinstalar)
|
*/

// Modules management operations (API)
Route::put('/{moduleAlias}', [ModulesController::class, 'update'])->name('update');
Route::post('/install', [ModulesController::class, 'install'])->name('install');
Route::post('/{moduleAlias}/enable', [ModulesController::class, 'enable'])->name('enable');
Route::post('/{moduleAlias}/disable', [ModulesController::class, 'disable'])->name('disable');
Route::post('/{moduleAlias}/uninstall', [ModulesController::class, 'uninstall'])->name('uninstall');
