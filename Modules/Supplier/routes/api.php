<?php

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Http\Controllers\Api\PromptSelectionApiController;

// Supplier API routes
Route::apiResource('prompt-selection', PromptSelectionApiController::class)->names('prompt-selection');
