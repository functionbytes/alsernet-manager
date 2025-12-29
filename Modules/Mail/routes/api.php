<?php

use Modules\Mail\Http\Controllers\Api\EmailEndpointController;
use Illuminate\Support\Facades\Route;

Route::post('{slug}/send', [EmailEndpointController::class, 'send']);
Route::get('{slug}/info', [EmailEndpointController::class, 'info']);
Route::get('{slug}/status', [EmailEndpointController::class, 'status']);
