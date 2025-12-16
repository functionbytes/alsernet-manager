<?php

use App\Http\Controllers\Api\DocumentsController;
use App\Http\Controllers\Api\EmailEndpointController;
use App\Http\Controllers\Managers\Helpdesk\ConversationMessagesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Email Endpoint Routes (no authentication required, but token validation in controller)
Route::prefix('email-endpoints')->group(function () {
    Route::post('{slug}/send', [EmailEndpointController::class, 'send']);
    Route::get('{slug}/info', [EmailEndpointController::class, 'info']);
    Route::get('{slug}/status', [EmailEndpointController::class, 'status']);
});

// Public Documents Routes (Prestashop integration) - No authentication, only rate limiting
Route::prefix('documents')->middleware('throttle:60,1')->group(function () {
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

Route::middleware('auth:sanctum')->group(function () {

    // User information
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Helpdesk API Routes
    Route::prefix('helpdesk')->group(function () {

        // Conversations Messages
        Route::prefix('conversations/{conversation}')->group(function () {
            Route::get('messages', [ConversationMessagesController::class, 'index']);
            Route::post('messages', [ConversationMessagesController::class, 'store']);
            Route::post('typing', [ConversationMessagesController::class, 'broadcastTyping']);
        });

        // Messages
        Route::prefix('messages/{item}')->group(function () {
            Route::post('read', [ConversationMessagesController::class, 'markAsRead']);
            Route::delete('', [ConversationMessagesController::class, 'destroy']);
        });

        // Canned Replies
        Route::get('canned-replies', [ConversationMessagesController::class, 'getCannedReplies']);
    });
});
