<?php

use Illuminate\Support\Facades\Route;
use Modules\Mail\Http\Controllers\Api\EmailEndpointController;

Route::post('{slug}/send', [EmailEndpointController::class, 'send']);
Route::get('{slug}/info', [EmailEndpointController::class, 'info']);
Route::get('{slug}/status', [EmailEndpointController::class, 'status']);
