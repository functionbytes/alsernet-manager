<?php

use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Managers\Helpdesk\ConversationMessagesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Health Check Routes (no authentication, no rate limiting - for external monitoring)
Route::prefix('health')->group(function () {
    Route::get('ping', [HealthCheckController::class, 'ping']);           // Ping simple
    Route::get('/', [HealthCheckController::class, 'health']);            // Health check completo
    Route::get('documents', [HealthCheckController::class, 'documentsHealth']); // Health específico documentos
    Route::get('detailed', [HealthCheckController::class, 'detailed']);   // Detallado (solo debug)
});

// Email Endpoint routes are now handled by Modules\Mail
// See: Modules/Mail/routes/api.php
/*
Route::prefix('email-endpoints')->group(function () {
    Route::post('{slug}/send', [EmailEndpointController::class, 'send']);
    Route::get('{slug}/info', [EmailEndpointController::class, 'info']);
    Route::get('{slug}/status', [EmailEndpointController::class, 'status']);
});
*/

// Public Document Routes (Prestashop integration) - No authentication, only rate limiting

// AI Prompt Selection API (for n8n integration)
Route::prefix('suppliers/prompts')->middleware('throttle:120,1')->group(function () {
    Route::get('/health', [\App\Http\Controllers\Api\Suppliers\PromptSelectionApiController::class, 'health']);
    Route::post('/select', [\App\Http\Controllers\Api\Suppliers\PromptSelectionApiController::class, 'select']);
    Route::post('/batch-select', [\App\Http\Controllers\Api\Suppliers\PromptSelectionApiController::class, 'batchSelect']);
    Route::get('/explain', [\App\Http\Controllers\Api\Suppliers\PromptSelectionApiController::class, 'explain']);
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
    });

    // Notifications API Routes - Handled by Modules\Notification
    // See: Modules/Notification/routes/api.php
});
