<?php

use Illuminate\Support\Facades\Route;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\{
    CategoriesController,
    PrioritiesController,
    StatusesController,
    CannedRepliesController,
    SlaPoliciesController,
    SettingsController
};

Route::get('/', [SettingsController::class, 'index'])->name('index');
Route::post('/update', [SettingsController::class, 'update'])->name('update');

Route::resource('categories', CategoriesController::class);
Route::post('categories/reorder', [CategoriesController::class, 'reorder'])->name('categories.reorder');

Route::resource('priorities', PrioritiesController::class);

Route::resource('statuses', StatusesController::class);
Route::post('statuses/reorder', [StatusesController::class, 'reorder'])->name('statuses.reorder');

Route::resource('canned-replies', CannedRepliesController::class);
Route::get('canned-replies/search', [CannedRepliesController::class, 'search'])->name('canned-replies.search');

Route::resource('sla-policies', SlaPoliciesController::class);
