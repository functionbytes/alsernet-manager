<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Warehouse Manager Routes
|--------------------------------------------------------------------------
|
| Routes for warehouse management by managers
| Prefix: /manager/settings/warehouse (applied by RouteServiceProvider)
| Middleware: auth, role:manager|super-admin (applied by RouteServiceProvider)
| Name prefix: manager.settings.warehouse. (applied by RouteServiceProvider)
|
*/

// Load manager routes
require __DIR__.'/managers.php';
