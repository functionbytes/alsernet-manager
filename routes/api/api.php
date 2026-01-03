<?php

use App\Http\Controllers\Api\ErpController;
use App\Http\Controllers\Api\Return\PublicReturnController;
use App\Http\Controllers\Api\Return\ReturnController;
use Illuminate\Support\Facades\Route;
use Modules\Document\Http\Controllers\Api\DocumentsController;
use Modules\Helpdesk\Http\Controllers\Api\TicketsController;


Route::group(['prefix' => 'documents'], function () {
    Route::post('/', [DocumentsController::class, 'process']);
    // @deprecated Webhook routes are now handled by modules\Webhook
    // See: modules/Webhook/routes/api.php
    // Route::post('/webhooks/prestashop/order-paid', [DocumentsController::class, 'prestashopOrderPaid']);
    Route::post('/resend-reminder', [DocumentsController::class, 'resendDocumentReminder']);
    Route::post('/confirm-upload', [DocumentsController::class, 'confirmDocumentUpload']);
    Route::get('/order/data/{order_id}', [DocumentsController::class, 'getOrderData']);
    Route::post('/fill-order-data', [DocumentsController::class, 'fillDocumentWithOrderData']);
    Route::get('/sync/all', [DocumentsController::class, 'syncAllDocumentsWithOrders']);
    Route::get('/sync/by-query', [DocumentsController::class, 'syncDocumentsByOrderQuery']);
    Route::post('/sync/by-order', [DocumentsController::class, 'syncDocumentByOrderId']);
});
