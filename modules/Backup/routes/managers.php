<?php

use Modules\Backup\Http\Controllers\Managers\Settings\BackupController;
use Modules\Backup\Http\Controllers\Managers\Settings\BackupScheduleController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'manager/settings/backups'], function () {
    // Backup management
    Route::get('/', [BackupController::class, 'index'])->name('manager.settings.backups');
    Route::get('/create', [BackupController::class, 'create'])->name('manager.settings.backups.create');
    Route::post('/store', [BackupController::class, 'store'])->name('manager.settings.backups.store');
    Route::get('/{id}/download', [BackupController::class, 'download'])->name('manager.settings.backups.download');
    Route::delete('/{id}', [BackupController::class, 'destroy'])->name('manager.settings.backups.destroy');
});

Route::group(['prefix' => 'manager/settings/backup-schedules'], function () {
    // Backup schedule management
    Route::get('/', [BackupScheduleController::class, 'index'])->name('manager.settings.backup-schedules');
    Route::get('/create', [BackupScheduleController::class, 'create'])->name('manager.settings.backup-schedules.create');
    Route::post('/store', [BackupScheduleController::class, 'store'])->name('manager.settings.backup-schedules.store');
    Route::get('/{id}/edit', [BackupScheduleController::class, 'edit'])->name('manager.settings.backup-schedules.edit');
    Route::post('/{id}/update', [BackupScheduleController::class, 'update'])->name('manager.settings.backup-schedules.update');
    Route::delete('/{id}', [BackupScheduleController::class, 'destroy'])->name('manager.settings.backup-schedules.destroy');
});
