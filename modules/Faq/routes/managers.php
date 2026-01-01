<?php

use Illuminate\Support\Facades\Route;
use Modules\Faq\Http\Controllers\Managers\CategoriesController;
use Modules\Faq\Http\Controllers\Managers\FaqsController;

Route::group(['prefix' => 'manager/faqs'], function () {

    Route::get('/', [FaqsController::class, 'index'])->name('manager.faqs');
    Route::get('/create', [FaqsController::class, 'create'])->name('manager.faqs.create');
    Route::post('/store', [FaqsController::class, 'store'])->name('manager.faqs.store');
    Route::post('/update', [FaqsController::class, 'update'])->name('manager.faqs.update');
    Route::get('/edit/{uid}', [FaqsController::class, 'edit'])->name('manager.faqs.edit');
    Route::get('/destroy/{uid}', [FaqsController::class, 'destroy'])->name('manager.faqs.destroy');

    Route::get('/categories', [CategoriesController::class, 'index'])->name('manager.faqs.categories');
    Route::get('/categories/create', [CategoriesController::class, 'create'])->name('manager.faqs.categories.create');
    Route::post('/categories/store', [CategoriesController::class, 'store'])->name('manager.faqs.categories.store');
    Route::post('/categories/update', [CategoriesController::class, 'update'])->name('manager.faqs.categories.update');
    Route::get('/categories/edit/{uid}', [CategoriesController::class, 'edit'])->name('manager.faqs.categories.edit');
    Route::get('/categories/destroy/{uid}', [CategoriesController::class, 'destroy'])->name('manager.faqs.categories.destroy');

});
