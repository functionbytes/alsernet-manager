<?php

use Illuminate\Support\Facades\Route;
use modules\Database\Http\Controllers\DatabaseController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('databases', DatabaseController::class)->names('database');
});
