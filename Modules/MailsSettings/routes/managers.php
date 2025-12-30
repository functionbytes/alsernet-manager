<?php

use Illuminate\Support\Facades\Route;
use Modules\MailsSettings\Http\Controllers\Managers\Settings\EmailSettingsController;
use Modules\MailsSettings\Http\Controllers\Managers\Settings\IncomingEmailSettingsController;
use Modules\MailsSettings\Http\Controllers\Managers\Settings\OutgoingEmailSettingsController;
use Modules\MailsSettings\Http\Controllers\Managers\Settings\StageEmailActionController;

// Email settings routes
Route::middleware(['web', 'auth', 'role:manager|super-admin'])
    ->prefix('manager/settings/email')
    ->name('manager.settings.email.')
    ->group(function () {
        Route::get('/', [EmailSettingsController::class, 'index'])->name('index');
        Route::post('/update', [EmailSettingsController::class, 'update'])->name('update');
    });

// Incoming email settings routes
Route::middleware(['web', 'auth', 'role:manager|super-admin'])
    ->prefix('manager/settings/incoming-email')
    ->name('manager.settings.incoming-email.')
    ->group(function () {
        Route::get('/', [IncomingEmailSettingsController::class, 'index'])->name('index');
        Route::post('/update', [IncomingEmailSettingsController::class, 'update'])->name('update');

        // IMAP connections
        Route::post('/imap/store', [IncomingEmailSettingsController::class, 'storeImapConnection'])->name('imap.store');
        Route::delete('/imap/{id}', [IncomingEmailSettingsController::class, 'deleteImapConnection'])->name('imap.delete');
        Route::post('/imap/test', [IncomingEmailSettingsController::class, 'testImapConnection'])->name('imap.test');

        // Gmail connections
        Route::post('/gmail/update', [IncomingEmailSettingsController::class, 'updateGmail'])->name('gmail.update');
        Route::get('/gmail/authorize', [IncomingEmailSettingsController::class, 'gmailAuthorize'])->name('gmail.authorize');
        Route::get('/gmail/callback', [IncomingEmailSettingsController::class, 'gmailCallback'])->name('gmail.callback');
        Route::delete('/gmail/{id}', [IncomingEmailSettingsController::class, 'deleteGmailConnection'])->name('gmail.delete');

        // Other handlers
        Route::post('/pipe/update', [IncomingEmailSettingsController::class, 'updatePipe'])->name('pipe.update');
        Route::post('/api/update', [IncomingEmailSettingsController::class, 'updateApi'])->name('api.update');
        Route::post('/api/generate-key', [IncomingEmailSettingsController::class, 'generateApiKey'])->name('api.generate-key');
        Route::get('/api/documentation', [IncomingEmailSettingsController::class, 'apiDocumentation'])->name('api.documentation');
        Route::post('/mailgun/update', [IncomingEmailSettingsController::class, 'updateMailgun'])->name('mailgun.update');

        // phpList integration
        Route::post('/phplist/update', [IncomingEmailSettingsController::class, 'updatePhplist'])->name('phplist.update');
        Route::post('/phplist/test', [IncomingEmailSettingsController::class, 'testPhplistConnection'])->name('phplist.test');
        Route::get('/phplist/lists', [IncomingEmailSettingsController::class, 'getPhplistLists'])->name('phplist.lists');
        Route::post('/phplist/subscribe', [IncomingEmailSettingsController::class, 'phplistSubscribe'])->name('phplist.subscribe');
    });

// Outgoing email settings routes
Route::middleware(['web', 'auth', 'role:manager|super-admin'])
    ->prefix('manager/settings/outgoing-email')
    ->name('manager.settings.outgoing-email.')
    ->group(function () {
        Route::get('/', [OutgoingEmailSettingsController::class, 'index'])->name('index');
        Route::post('/update', [OutgoingEmailSettingsController::class, 'update'])->name('update');
    });

// Stage email action routes
Route::middleware(['web', 'auth', 'role:manager|super-admin'])
    ->prefix('manager/settings/stage-email-action')
    ->name('manager.settings.stage-email-action.')
    ->group(function () {
        Route::get('/', [StageEmailActionController::class, 'index'])->name('index');
        Route::post('/store', [StageEmailActionController::class, 'store'])->name('store');
        Route::post('/update', [StageEmailActionController::class, 'update'])->name('update');
        Route::delete('/{id}', [StageEmailActionController::class, 'destroy'])->name('destroy');
    });
