<?php

use Illuminate\Support\Facades\Route;
use Modules\Event\Http\Controllers\EventApiController;

Route::apiResource('events', EventApiController::class)->parameters([
    'events' => 'uid',
]);
