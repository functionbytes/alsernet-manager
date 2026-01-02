<?php

use Illuminate\Support\Facades\Route;
use Modules\Modules\Http\Controllers\ModulesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('modules', ModulesController::class)->names('modules');
});
