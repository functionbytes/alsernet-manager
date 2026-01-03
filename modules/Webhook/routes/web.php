<?php

use Illuminate\Support\Facades\Route;
use Modules\Webhook\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Operational Routes
|--------------------------------------------------------------------------
|
| Rutas para la gestión operacional de webhooks (día a día)
| Prefix: /webhooks (aplicado por ServiceProvider)
| Name: manager.webhooks.* (aplicado por ServiceProvider)
| Middleware: web, auth, role:manager|super-admin
|
*/

// Listing routes
Route::get('/', [WebhookController::class, 'index'])->name('index');

// Detail view
Route::get('/{uid}', [WebhookController::class, 'show'])->name('show');

// Monitoring routes
Route::get('/deliveries', [WebhookController::class, 'deliveries'])->name('deliveries');
Route::get('/events', [WebhookController::class, 'events'])->name('events');
