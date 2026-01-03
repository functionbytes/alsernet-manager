<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationsController;

/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
|
| Prefix: /notifications (applied by NotificationServiceProvider)
| Name: notifications.* (applied by NotificationServiceProvider)
| Middleware: web, auth (applied by NotificationServiceProvider)
|
*/

Route::get('/', [NotificationsController::class, 'index'])->name('index');
Route::get('/create', [NotificationsController::class, 'create'])->name('create');
Route::post('/store', [NotificationsController::class, 'store'])->name('store');
Route::get('/{id}/edit', [NotificationsController::class, 'edit'])->name('edit');
Route::post('/{id}/update', [NotificationsController::class, 'update'])->name('update');
Route::delete('/{id}', [NotificationsController::class, 'destroy'])->name('destroy');
