<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\Api\NotificationController;

Route::prefix('api')->middleware(['web', 'auth'])->group(function () {
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('api.notifications.index');
        Route::get('/stats', [NotificationController::class, 'stats']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy']);

        // Preferencias
        Route::get('/preferences', [NotificationController::class, 'preferences']);
        Route::put('/preferences', [NotificationController::class, 'updatePreferences']);

        // Push Tokens
        Route::post('/push-tokens', [NotificationController::class, 'registerPushToken']);
        Route::delete('/push-tokens/{id}', [NotificationController::class, 'deactivatePushToken']);
    });
});
