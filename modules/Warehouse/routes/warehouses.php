<?php

use App\Http\Controllers\Managers\Users\UsersController;
use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\Warehouses\Locations\BarcodeController as LocationsBarcodesController;
use Modules\Warehouse\Http\Controllers\Warehouses\Locations\TransferController;
use Modules\Warehouse\Http\Controllers\Warehouses\Products\BarcodeController as ProductsBarcodesController;
use Modules\Warehouse\Http\Controllers\Warehouses\Warehouses\LocationsController as WarehousesLocationsController;
use Modules\Warehouse\Http\Controllers\Warehouses\Warehouses\WarehousesController;

/*
|--------------------------------------------------------------------------
| Warehouse Worker Routes
|--------------------------------------------------------------------------
|
| Routes for warehouse workers to manage daily operations like transfers,
| inventory updates, and barcode scanning
| Prefix: /warehouse (applied by RouteServiceProvider)
| Middleware: auth, check.roles.permissions:warehouse (applied by RouteServiceProvider)
| Name prefix: warehouse. (applied by RouteServiceProvider)
|
*/

Route::get('/', [WarehousesController::class, 'index'])->name('dashboard');

// User Management Routes (APPROACH 1: Middleware-Based)
// These routes are protected by CheckRolesAndPermissions middleware
Route::group([
    'prefix' => 'users',
    'name' => 'users.',
    'middleware' => ['check.roles.permissions:warehouse'],
], function () {
    Route::get('/', [UsersController::class, 'index'])->name('index');
    Route::get('/create', [UsersController::class, 'create'])->name('create');
    Route::post('/store', [UsersController::class, 'store'])->name('store');
    Route::get('/{uid}', [UsersController::class, 'view'])->name('view');
    Route::get('/{uid}/edit', [UsersController::class, 'edit'])->name('edit');
    Route::post('/update', [UsersController::class, 'update'])->name('update');
    Route::get('/{uid}/destroy', [UsersController::class, 'destroy'])->name('destroy');
});

Route::group(['prefix' => 'warehouses'], function () {

    Route::get('/', [WarehousesController::class, 'index'])->name('warehouses');
    Route::get('/create', [WarehousesController::class, 'create'])->name('warehouses.create');
    Route::post('/update', [WarehousesController::class, 'update'])->name('warehouses.update');
    Route::get('/edit/{slack}', [WarehousesController::class, 'edit'])->name('warehouses.edit');
    Route::get('/view/{slack}', [WarehousesController::class, 'view'])->name('warehouses.view');
    Route::get('/destroy/{slack}', [WarehousesController::class, 'destroy'])->name('warehouses.destroy');
    Route::get('/report/{slack}', [WarehousesController::class, 'report'])->name('warehouses.report');

    Route::get('/close/{slack}', [WarehousesController::class, 'close'])->name('warehouses.close');
    Route::get('/arrange/{slack}', [WarehousesController::class, 'arrange'])->name('warehouses.arrange');
    Route::get('/content/{slack}', [WarehousesController::class, 'content'])->name('warehouses.content');
    Route::get('/report/{slack}', [WarehousesController::class, 'report'])->name('warehouses.report');

    Route::post('/locations/close', [WarehousesLocationsController::class, 'close'])->name('warehouses.location.close');

    Route::post('/locations/validate/location', [WarehousesLocationsController::class, 'validateLocation'])->name('warehouses.location.validate.location');
    Route::post('/locations/validate/section', [WarehousesLocationsController::class, 'validateSection'])->name('warehouses.location.validate.section');
    Route::post('/locations/validate/location/genrate', [WarehousesLocationsController::class, 'validateGenerate'])->name('warehouses.location.validate.validate');
    Route::post('/locations/validate/product', [WarehousesLocationsController::class, 'validateProduct'])->name('warehouses.location.validate.product');

    Route::get('/locations/{warehouse}/{location}/{section}', [WarehousesLocationsController::class, 'section'])->name('warehouses.location.location.section');
    Route::get('/locations/{warehouse}/{location}/{section}/modalitie', [WarehousesLocationsController::class, 'modalitie'])->name('warehouses.location.modalitie');
    Route::get('/locations/{warehouse}/{location}/{section}/automatic', [WarehousesLocationsController::class, 'automatic'])->name('warehouses.location.automatic');
    Route::get('/locations/{warehouse}/{location}/{section}/manual', [WarehousesLocationsController::class, 'manual'])->name('warehouses.location.manual');

});

Route::group(['prefix' => 'transfer'], function () {
    Route::get('/', [TransferController::class, 'index'])->name('inventories.transfer.index');
    Route::post('/search', [TransferController::class, 'searchProduct'])->name('inventories.transfer.search');
    Route::post('/available-sections', [TransferController::class, 'getAvailableSections'])->name('inventories.transfer.available-sections');
    Route::post('/process', [TransferController::class, 'transfer'])->name('inventories.transfer.process');
    Route::get('/history', [TransferController::class, 'history'])->name('inventories.transfer.history');
});

Route::group(['prefix' => 'locations'], function () {
    Route::get('/all/barcode', [LocationsBarcodesController::class, 'all'])->name('manager.shops.locations.barcodes.all');
    Route::get('/single/barcode/{slack}', [LocationsBarcodesController::class, 'single'])->name('manager.shops.locations.barcodes.single');
});

Route::group(['prefix' => 'inventaries'], function () {
    Route::get('/all/barcode', [ProductsBarcodesController::class, 'all'])->name('manager.inventaries.barcodes.all');
    Route::get('/single/barcode/{slack}', [ProductsBarcodesController::class, 'single'])->name('manager.inventaries.barcodes.single');
});

Route::group(['prefix' => 'settings'], function () {
    // Route::get('/', [SettingsController::class, 'index'])->name('settings');
    // Route::post('/update', [SettingsController::class, 'update'])->name('settings.update');
});
