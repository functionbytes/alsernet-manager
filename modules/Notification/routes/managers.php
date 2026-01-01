<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\Managers\NotificationsController;
use Modules\Notification\Http\Controllers\Managers\Settings\NotificationSettingsController;

Route::group(['prefix' => 'manager/notifications'], function () {
    Route::get('/', [NotificationsController::class, 'index'])->name('manager.notifications');
    Route::get('/create', [NotificationsController::class, 'create'])->name('manager.notifications.create');
    Route::post('/store', [NotificationsController::class, 'store'])->name('manager.notifications.store');
    Route::get('/{id}/edit', [NotificationsController::class, 'edit'])->name('manager.notifications.edit');
    Route::post('/{id}/update', [NotificationsController::class, 'update'])->name('manager.notifications.update');
    Route::delete('/{id}', [NotificationsController::class, 'destroy'])->name('manager.notifications.destroy');
});

Route::group(['prefix' => 'manager/settings/notifications'], function () {
    Route::get('/', [NotificationSettingsController::class, 'index'])->name('manager.settings.notifications');
    Route::post('/update', [NotificationSettingsController::class, 'update'])->name('manager.settings.notifications.update');
});
