<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'endpoints'], function () {
    require __DIR__.'/api/endpoints.php';
});
