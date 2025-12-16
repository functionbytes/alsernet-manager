<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Managers\Helpdesk\ConversationMessagesController;
use App\Http\Controllers\Api\EmailEndpointController;

// Public Email Endpoint Routes (no authentication required, but token validation in controller)
Route::prefix('email-endpoints')->group(function () {
    Route::post('{slug}/send', [EmailEndpointController::class, 'send']);
    Route::get('{slug}/info', [EmailEndpointController::class, 'info']);
    Route::get('{slug}/status', [EmailEndpointController::class, 'status']);
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
