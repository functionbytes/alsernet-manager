<?php

use Illuminate\Support\Facades\Route;
use Modules\Helpdesk\Http\Controllers\Api\{
    TicketsController,
    MessagesController,
    CategoriesController,
    PrioritiesController,
    StatusesController
};

Route::get('tickets', [TicketsController::class, 'index']);
Route::post('tickets', [TicketsController::class, 'store']);
Route::get('tickets/{ticketNumber}', [TicketsController::class, 'show']);
Route::put('tickets/{ticketNumber}', [TicketsController::class, 'update']);

Route::get('tickets/{ticketNumber}/messages', [MessagesController::class, 'index']);
Route::post('tickets/{ticketNumber}/messages', [MessagesController::class, 'store']);

Route::get('categories', [CategoriesController::class, 'index']);
Route::get('priorities', [PrioritiesController::class, 'index']);
Route::get('statuses', [StatusesController::class, 'index']);
