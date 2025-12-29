<?php

use Illuminate\Support\Facades\Route;
use Modules\Event\Http\Controllers\Managers\Events\EventsController;
use Modules\Event\Http\Controllers\Managers\Events\CalendarController;

Route::middleware(['web', 'auth'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::prefix('events')->name('events')->group(function () {
            Route::get('/', [EventsController::class, 'index']);
            Route::get('/create', [EventsController::class, 'create'])->name('.create');
            Route::post('/store', [EventsController::class, 'store'])->name('.store');
            Route::post('/update', [EventsController::class, 'update'])->name('.update');
            Route::get('/edit/{uid}', [EventsController::class, 'edit'])->name('.edit');
            Route::get('/view/{uid}', [EventsController::class, 'view'])->name('.view');
            Route::get('/destroy/{uid}', [EventsController::class, 'destroy'])->name('.destroy');

            // Calendar routes
            Route::get('/calendar', [CalendarController::class, 'index'])->name('.calendar');
            Route::get('/calendar/events', [CalendarController::class, 'events'])->name('.calendar.events');
        });
    });
