<?php

use Illuminate\Support\Facades\Route;
use Modules\Erp\Http\Controllers\Api\ErpController;

/*
|--------------------------------------------------------------------------
| ERP Module API Routes
|--------------------------------------------------------------------------
|
| All API endpoints for ERP integration
|
*/

Route::middleware(['api'])->prefix('api/erp')->group(function () {
    // Client Management
    Route::post('recuperarclienteerp', [ErpController::class, 'recuperarclienteerp']);
    Route::post('recuperaridclienteerp', [ErpController::class, 'recuperaridclienteerp']);
    Route::post('recuperarclienteerpAlsernet', [ErpController::class, 'recuperarclienteerpAlsernet']);
    Route::post('recuperardatosclienteerp', [ErpController::class, 'recuperardatosclienteerp']);
    Route::post('recuperardatosclienteerpporidweb', [ErpController::class, 'recuperardatosclienteerpporidweb']);
    Route::post('recuperardatosclienteerpporidgestion', [ErpController::class, 'recuperardatosclienteerpporidgestion']);
    Route::post('guardardatosclienteerp', [ErpController::class, 'guardardatosclienteerp']);

    // Order Management
    Route::post('recuperarpedidoscliente', [ErpController::class, 'recuperarpedidoscliente']);
    Route::post('recuperarpedido', [ErpController::class, 'recuperarpedido']);
    Route::post('recuperarpedidoporid', [ErpController::class, 'recuperarpedidoporid']);

    // Catalog Management
    Route::post('recuperarcatalogosclienteerp', [ErpController::class, 'recuperarcatalogosclienteerp']);
    Route::post('suscribircatalogosporeamilerp', [ErpController::class, 'suscribircatalogosporeamilerp']);
    Route::post('delsuscribircatalogosporeamilerp', [ErpController::class, 'delsuscribircatalogosporeamilerp']);

    // Additional endpoints (add as needed from ErpController)
});
