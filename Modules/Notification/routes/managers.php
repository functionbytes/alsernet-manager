<?php

use Modules\Notification\Http\Controllers\Managers\NotificationController;
use Modules\Notification\Http\Controllers\Managers\NotificationsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/notifications'], function () {
    Route::get('/', [NotificationsController::class, 'index'])->name('manager.notifications');
    Route::get('/create', [NotificationsController::class, 'create'])->name('manager.notifications.create');
    Route::post('/store', [NotificationsController::class, 'store'])->name('manager.notifications.store');
    Route::get('/{id}/edit', [NotificationsController::class, 'edit'])->name('manager.notifications.edit');
    Route::post('/{id}/update', [NotificationsController::class, 'update'])->name('manager.notifications.update');
    Route::delete('/{id}', [NotificationsController::class, 'destroy'])->name('manager.notifications.destroy');

});

Route::group(['prefix' => 'api', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
});
