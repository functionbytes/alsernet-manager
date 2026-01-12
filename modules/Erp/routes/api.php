<?php

use Illuminate\Support\Facades\Route;
use Modules\Erp\Http\Controllers\ErpController as V1ErpController;
use Modules\Erp\Http\Controllers\Eloquent\ClienteController as EloquentClienteController;
use Modules\Erp\Http\Controllers\Eloquent\ArticuloController as EloquentArticuloController;
use Modules\Erp\Http\Controllers\Eloquent\AlbaranController as EloquentAlbaranController;
use Modules\Erp\Http\Controllers\Eloquent\BonoController as EloquentBonoController;
use Modules\Erp\Http\Controllers\Eloquent\StockController as EloquentStockController;
use Modules\Erp\Http\Controllers\Eloquent\ValeController as EloquentValeController;
use Modules\Erp\Http\Controllers\Eloquent\PedidoClienteController as EloquentPedidoClienteController;
use Modules\Erp\Http\Controllers\Eloquent\ClienteCatalogoController as EloquentClienteCatalogoController;

use Modules\Erp\Http\Controllers\Direct\ClienteController as DirectClienteController;
use Modules\Erp\Http\Controllers\Direct\ArticuloController as DirectArticuloController;
use Modules\Erp\Http\Controllers\Direct\AlbaranController as DirectAlbaranController;
use Modules\Erp\Http\Controllers\Direct\BonoController as DirectBonoController;
use Modules\Erp\Http\Controllers\Direct\StockController as DirectStockController;
use Modules\Erp\Http\Controllers\Direct\ValeController as DirectValeController;
use Modules\Erp\Http\Controllers\Direct\PedidoClienteController as DirectPedidoClienteController;
use Modules\Erp\Http\Controllers\Direct\ClienteCatalogoController as DirectClienteCatalogoController;

Route::middleware(['api'])->group(function () {

    // ========== API v1 - LEGACY (Mantener sin cambios) ==========
    Route::prefix('erp')->group(function () {
        Route::post('recuperarclienteerp', [V1ErpController::class, 'recuperarclienteerp']);
        Route::post('recuperaridclienteerp', [V1ErpController::class, 'recuperaridclienteerp']);
        Route::post('recuperarclienteerpAlsernet', [V1ErpController::class, 'recuperarclienteerpAlsernet']);
        Route::post('recuperardatosclienteerp', [V1ErpController::class, 'recuperardatosclienteerp']);
        Route::post('recuperardatosclienteerpporidweb', [V1ErpController::class, 'recuperardatosclienteerpporidweb']);
        Route::post('recuperardatosclienteerpporidgestion', [V1ErpController::class, 'recuperardatosclienteerpporidgestion']);
        Route::post('guardardatosclienteerp', [V1ErpController::class, 'guardardatosclienteerp']);
        Route::post('recuperarpedidoscliente', [V1ErpController::class, 'recuperarpedidoscliente']);
        Route::post('recuperarpedido', [V1ErpController::class, 'recuperarpedido']);
        Route::post('recuperarpedidoporid', [V1ErpController::class, 'recuperarpedidoporid']);
        Route::post('recuperarcatalogosclienteerp', [V1ErpController::class, 'recuperarcatalogosclienteerp']);
        Route::post('suscribircatalogosporeamilerp', [V1ErpController::class, 'suscribircatalogosporeamilerp']);
        Route::post('delsuscribircatalogosporeamilerp', [V1ErpController::class, 'delsuscribircatalogosporeamilerp']);
    });

    // ========== API v2 - INTEGRACIÓN ==========
    Route::prefix('erp/v2')->group(function () {

        // ELOQUENT VERSION (ORM)
        Route::prefix('eloquent')->group(function () {
            Route::prefix('clientes')->group(function () {
                Route::get('/', [EloquentClienteController::class, 'index']);
                Route::post('/', [EloquentClienteController::class, 'store']);
                Route::put('/', [EloquentClienteController::class, 'updateLopd']);
            });
            Route::prefix('articulos')->group(function () {
                Route::get('/', [EloquentArticuloController::class, 'index']);
            });
            Route::prefix('albaranes')->group(function () {
                Route::get('/', [EloquentAlbaranController::class, 'index']);
            });
            Route::prefix('bonos')->group(function () {
                Route::get('/', [EloquentBonoController::class, 'index']);
            });
            Route::prefix('stock')->group(function () {
                Route::get('/', [EloquentStockController::class, 'index']);
            });
            Route::prefix('vales')->group(function () {
                Route::get('/', [EloquentValeController::class, 'index']);
            });
            Route::prefix('pedidos')->group(function () {
                Route::get('/', [EloquentPedidoClienteController::class, 'index']);
            });
            Route::prefix('catalogo')->group(function () {
                Route::get('/', [EloquentClienteCatalogoController::class, 'index']);
            });
        });

        // DIRECT SQL VERSION (Performance)
        Route::prefix('direct')->group(function () {
            Route::prefix('clientes')->group(function () {
                Route::get('/', [DirectClienteController::class, 'index']);
                Route::post('/', [DirectClienteController::class, 'store']);
                Route::put('/', [DirectClienteController::class, 'updateLopd']);
            });
            Route::prefix('articulos')->group(function () {
                Route::get('/', [DirectArticuloController::class, 'index']);
            });
            Route::prefix('albaranes')->group(function () {
                Route::get('/', [DirectAlbaranController::class, 'index']);
            });
            Route::prefix('bonos')->group(function () {
                Route::get('/', [DirectBonoController::class, 'index']);
            });
            Route::prefix('stock')->group(function () {
                Route::get('/', [DirectStockController::class, 'index']);
            });
            Route::prefix('vales')->group(function () {
                Route::get('/', [DirectValeController::class, 'index']);
            });
            Route::prefix('pedidos')->group(function () {
                Route::get('/', [DirectPedidoClienteController::class, 'index']);
            });
            Route::prefix('catalogo')->group(function () {
                Route::get('/', [DirectClienteCatalogoController::class, 'index']);
            });
        });
    });
});
