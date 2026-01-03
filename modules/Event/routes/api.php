<?php

use Illuminate\Support\Facades\Route;
use Modules\Event\Http\Controllers\Api\EventApiController;

Route::middleware(['api', 'auth:sanctum'])
    ->prefix('api')
    ->name('api.')
    ->group(function () {
        Route::apiResource('events', EventApiController::class)->parameters([
            'events' => 'uid',
        ]);
    });
