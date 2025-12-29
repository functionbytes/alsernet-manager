<?php

use Modules\Mail\Http\Controllers\Managers\Settings\EmailSettingsController;
use Modules\Mail\Http\Controllers\Managers\Settings\IncomingEmailSettingsController;
use Modules\Mail\Http\Controllers\Managers\Settings\OutgoingEmailSettingsController;
use Modules\Mail\Http\Controllers\Managers\Settings\StageEmailActionController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/settings/email'], function () {
    Route::get('/', [EmailSettingsController::class, 'index'])->name('manager.settings.email');
    Route::post('/update', [EmailSettingsController::class, 'update'])->name('manager.settings.email.update');
});

Route::group(['prefix' => 'manager/settings/incoming-email'], function () {
    Route::get('/', [IncomingEmailSettingsController::class, 'index'])->name('manager.settings.incoming-email');
    Route::post('/update', [IncomingEmailSettingsController::class, 'update'])->name('manager.settings.incoming-email.update');
});

Route::group(['prefix' => 'manager/settings/outgoing-email'], function () {
    Route::get('/', [OutgoingEmailSettingsController::class, 'index'])->name('manager.settings.outgoing-email');
    Route::post('/update', [OutgoingEmailSettingsController::class, 'update'])->name('manager.settings.outgoing-email.update');
});

Route::group(['prefix' => 'manager/settings/stage-email-action'], function () {
    Route::get('/', [StageEmailActionController::class, 'index'])->name('manager.settings.stage-email-action');
    Route::post('/store', [StageEmailActionController::class, 'store'])->name('manager.settings.stage-email-action.store');
    Route::post('/update', [StageEmailActionController::class, 'update'])->name('manager.settings.stage-email-action.update');
    Route::delete('/{id}', [StageEmailActionController::class, 'destroy'])->name('manager.settings.stage-email-action.destroy');
});
