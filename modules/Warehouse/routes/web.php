<?php

use Illuminate\Support\Facades\Route;
use Modules\Warehouse\Http\Controllers\Products\BarcodeController as ProductsBarcodesController;
use Modules\Warehouse\Http\Controllers\Products\ProductsController;
use Modules\Warehouse\Http\Controllers\Settings\Shops\Locations\BarcodeController as LocationsBarcodesController;
use Modules\Warehouse\Http\Controllers\Settings\Shops\Locations\LocationsController as ShopsLocationsController;
use Modules\Warehouse\Http\Controllers\Settings\Shops\Locations\ReportController;
use Modules\Warehouse\Http\Controllers\Settings\Shops\Shops\ShopsController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseDashboardController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseFloorsController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseHistoryController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseInventorySlotsController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseLocationsController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseLocationSectionsController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseLocationStylesController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseMapController;
use Modules\Warehouse\Http\Controllers\Settings\WarehouseReportsController;

/*
|--------------------------------------------------------------------------
| Warehouse Manager Routes
|--------------------------------------------------------------------------
|
| Routes for warehouse management by managers
| Prefix: /backups/warehouse (applied by WarehouseServiceProvider)
| Middleware: auth, role:super-admin (applied by WarehouseServiceProvider)
| Name prefix: backups.warehouse. (applied by WarehouseServiceProvider)
|
*/

// Warehouse Map (Visual Floor Plan)
Route::get('/map', [WarehouseMapController::class, 'map'])->name('map');
Route::get('/api/layout-spec', [WarehouseMapController::class, 'getLayoutSpec'])->name('api.layout');
Route::get('/api/config', [WarehouseMapController::class, 'getWarehouseConfig'])->name('api.config');
Route::get('/api/slot/{uid}', [WarehouseMapController::class, 'getSlotDetails'])->name('api.slot');

// Visual editing endpoints
Route::put('/location/{location_uid}/visual-config', [WarehouseMapController::class, 'updateVisualConfig'])->name('location.visual.update');
Route::post('/location/{location_uid}/reset-visual', [WarehouseMapController::class, 'resetVisualConfig'])->name('location.visual.reset');

// Floors Routes
Route::group(['prefix' => 'floors'], function () {
    Route::get('/', [WarehouseFloorsController::class, 'index'])->name('floors');
    Route::get('/create', [WarehouseFloorsController::class, 'create'])->name('floors.create');
    Route::post('/store', [WarehouseFloorsController::class, 'store'])->name('floors.store');
    Route::post('/update', [WarehouseFloorsController::class, 'update'])->name('floors.update');
    Route::get('/edit/{uid}', [WarehouseFloorsController::class, 'edit'])->name('floors.edit');
    Route::get('/view/{uid}', [WarehouseFloorsController::class, 'view'])->name('floors.view');
    Route::get('/destroy/{uid}', [WarehouseFloorsController::class, 'destroy'])->name('floors.destroy');
});

// Stand Styles Routes
Route::group(['prefix' => 'styles'], function () {
    Route::get('/', [WarehouseLocationStylesController::class, 'index'])->name('styles.index');
    Route::get('/create', [WarehouseLocationStylesController::class, 'create'])->name('styles.create');
    Route::post('/store', [WarehouseLocationStylesController::class, 'store'])->name('styles.store');
    Route::post('/update', [WarehouseLocationStylesController::class, 'update'])->name('styles.update');
    Route::get('/edit/{uid}', [WarehouseLocationStylesController::class, 'edit'])->name('styles.edit');
    Route::get('/view/{uid}', [WarehouseLocationStylesController::class, 'view'])->name('styles.view');
    Route::get('/destroy/{uid}', [WarehouseLocationStylesController::class, 'destroy'])->name('styles.destroy');
});

// Stands Routes (Redirects to warehouse list)
Route::get('/stands', function () {
    return redirect()->route('settings.warehouse.index')->with('info', 'Selecciona un almacén para ver sus stands');
})->name('stands');

// Inventory Slots Routes (Redirects to warehouse list)
Route::get('/slots', function () {
    return redirect()->route('settings.warehouse.index')->with('info', 'Selecciona un almacén para ver sus slots de inventario');
})->name('slots');

// Warehouse CRUD (Main List)
Route::get('/', [WarehouseController::class, 'index'])->name('index');
Route::get('/create', [WarehouseController::class, 'create'])->name('create');
Route::post('/store', [WarehouseController::class, 'store'])->name('store');

// Individual Warehouse Routes
Route::group(['prefix' => '{warehouse_uid}'], function () {
    Route::get('/', [WarehouseController::class, 'view'])->name('view');
    Route::get('/edit', [WarehouseController::class, 'edit'])->name('edit');
    Route::post('/update', [WarehouseController::class, 'update'])->name('update');
    Route::get('/destroy', [WarehouseController::class, 'destroy'])->name('destroy');
    Route::get('/summary', [WarehouseController::class, 'getSummary'])->name('summary');

    // Warehouse Resources (scoped to warehouse)
    Route::group(['prefix' => 'details'], function () {

        // Dashboard del warehouse
        Route::get('/dashboard', [WarehouseDashboardController::class, 'dashboard'])->name('dashboard.index');
        Route::get('/api/floors', [WarehouseDashboardController::class, 'getFloors'])->name('api.floors');

        // Mapa del warehouse
        Route::get('/map', [WarehouseMapController::class, 'map'])->name('detail.map');
        Route::get('/api/layout-spec', [WarehouseMapController::class, 'getLayoutSpec'])->name('detail.api.layout');
        Route::get('/api/config', [WarehouseMapController::class, 'getWarehouseConfig'])->name('detail.api.config');
        Route::get('/api/slot/{slot_uid}', [WarehouseMapController::class, 'getSlotDetails'])->name('detail.api.slot');

        // Old map API endpoints (legacy support for warehouse map views)
        Route::get('/map/locations', [WarehouseLocationsController::class, 'getByWarehouse'])->name('map.locations');
        Route::post('/map/create', [WarehouseLocationsController::class, 'store'])->name('map.create');
        Route::post('/map/update', [WarehouseLocationsController::class, 'update'])->name('map.update');
        Route::delete('/map/delete/{location_uid}', [WarehouseLocationsController::class, 'destroy'])->name('map.delete');

        // Location sections routes (legacy support with alias)
        Route::get('/locations/{location_uid}/sections', [WarehouseLocationSectionsController::class, 'index'])->name('locations.sections.index');

        // Visual editing endpoints
        Route::put('/location/{location_uid}/visual-config', [WarehouseMapController::class, 'updateVisualConfig'])->name('detail.location.visual.update');
        Route::post('/location/{location_uid}/reset-visual', [WarehouseMapController::class, 'resetVisualConfig'])->name('detail.location.visual.reset');

        // Historial del warehouse
        Route::group(['prefix' => 'history'], function () {
            Route::get('/', [WarehouseHistoryController::class, 'index'])->name('history');
            Route::get('/{uid}', [WarehouseHistoryController::class, 'view'])->name('history.view');
            Route::get('/{uid}/edit', [WarehouseHistoryController::class, 'edit'])->name('history.edit');
            Route::post('/update', [WarehouseHistoryController::class, 'update'])->name('history.update');
            Route::get('/api/slot/{slot_uid}', [WarehouseHistoryController::class, 'getSlotHistory'])->name('history.api.slot');
            Route::post('/api/filter', [WarehouseHistoryController::class, 'filterByDateRange'])->name('history.api.filter');
            Route::get('/api/statistics', [WarehouseHistoryController::class, 'getStatistics'])->name('history.api.statistics');
        });

        // Reportes del warehouse
        Route::group(['prefix' => 'reports'], function () {
            Route::get('/', [WarehouseReportsController::class, 'report'])->name('reports');
            Route::post('/inventory', [WarehouseReportsController::class, 'generateInventory'])->name('reports.inventory');
            Route::post('/movements', [WarehouseReportsController::class, 'generateMovements'])->name('reports.movements');
            Route::post('/occupancy', [WarehouseReportsController::class, 'generateOccupancy'])->name('reports.occupancy');
            Route::post('/capacity', [WarehouseReportsController::class, 'generateCapacity'])->name('reports.capacity');
        });

        // Pisos del warehouse
        Route::group(['prefix' => 'floors'], function () {

            Route::get('/', [WarehouseFloorsController::class, 'index'])->name('detail.floors');
            Route::get('/create', [WarehouseFloorsController::class, 'create'])->name('detail.floors.create');
            Route::post('/store', [WarehouseFloorsController::class, 'store'])->name('detail.floors.store');
            Route::get('/{floor_uid}', [WarehouseFloorsController::class, 'view'])->name('detail.floors.view');
            Route::get('/{floor_uid}/edit', [WarehouseFloorsController::class, 'edit'])->name('detail.floors.edit');
            Route::post('/update', [WarehouseFloorsController::class, 'update'])->name('detail.floors.update');
            Route::get('/{floor_uid}/destroy', [WarehouseFloorsController::class, 'destroy'])->name('detail.floors.destroy');

            // Locaciones dentro del piso
            Route::group(['prefix' => '{floor_uid}/locations'], function () {
                Route::get('/', [WarehouseLocationsController::class, 'index'])->name('detail.locations');
                Route::get('/create', [WarehouseLocationsController::class, 'create'])->name('detail.locations.create');
                Route::post('/store', [WarehouseLocationsController::class, 'store'])->name('detail.locations.store');
                Route::get('/{location_uid}', [WarehouseLocationsController::class, 'view'])->name('detail.locations.view');
                Route::get('/{location_uid}/edit', [WarehouseLocationsController::class, 'edit'])->name('detail.locations.edit');
                Route::post('/update', [WarehouseLocationsController::class, 'update'])->name('detail.locations.update');
                Route::get('/{location_uid}/destroy', [WarehouseLocationsController::class, 'destroy'])->name('detail.locations.destroy');
                Route::get('/api/warehouse', [WarehouseLocationsController::class, 'getByWarehouse'])->name('detail.locations.api.warehouse');
                Route::get('/api/barcode/{barcode}', [WarehouseLocationsController::class, 'getByBarcode'])->name('detail.locations.api.barcode');
                Route::get('/{location_uid}/api/details', [WarehouseLocationsController::class, 'getLocationDetails'])->name('detail.locations.api.details');
                Route::get('/{location_uid}/sections/{section_uid}/api/details', [WarehouseLocationsController::class, 'getSectionDetails'])->name('detail.sections.api.details');

                // Print barcodes routes
                Route::get('/print-all-barcodes', [WarehouseLocationsController::class, 'printAllBarcodes'])->name('detail.locations.print-all');
                Route::get('/{location_uid}/print-barcodes', [WarehouseLocationsController::class, 'printBarcodes'])->name('detail.locations.print');

                // Transferir múltiples ubicaciones (bulk)
                Route::get('/transfer/bulk', [WarehouseLocationsController::class, 'transferBulkForm'])->name('detail.locations.transfer.bulk');
                Route::post('/transfer/bulk', [WarehouseLocationsController::class, 'transferBulkSubmit'])->name('detail.locations.transfer.bulk.store');

                // Transferir ubicación individual
                Route::group(['prefix' => '{location_uid}/transfer'], function () {
                    Route::get('/', [WarehouseLocationsController::class, 'transfer'])->name('detail.locations.transfer');
                    Route::post('/store', [WarehouseLocationsController::class, 'transferSubmit'])->name('detail.locations.transfer.store');
                    Route::get('/api/available-floors', [WarehouseLocationsController::class, 'getAvailableFloorsForTransfer'])->name('detail.locations.transfer.api.available-floors');
                });

                // Secciones dentro de la locación
                Route::group(['prefix' => '{location_uid}/sections'], function () {
                    Route::get('/', [WarehouseLocationSectionsController::class, 'index'])->name('detail.sections');
                    Route::get('/create', [WarehouseLocationSectionsController::class, 'create'])->name('detail.sections.create');
                    Route::post('/store', [WarehouseLocationSectionsController::class, 'store'])->name('detail.sections.store');
                    Route::get('/{section_uid}', [WarehouseLocationSectionsController::class, 'view'])->name('detail.section.view');
                    Route::get('/{section_uid}/edit', [WarehouseLocationSectionsController::class, 'edit'])->name('detail.section.edit');
                    Route::post('/update', [WarehouseLocationSectionsController::class, 'update'])->name('detail.section.update');
                    Route::get('/{section_uid}/destroy', [WarehouseLocationSectionsController::class, 'destroy'])->name('detail.section.destroy');
                    Route::get('/api/list/{location_id}', [WarehouseLocationSectionsController::class, 'getSectionsList'])->name('detail.sections.api.list');
                    Route::post('/quick-create', [WarehouseLocationSectionsController::class, 'quickCreate'])->name('detail.sections.quick-create');

                    // Inventory Slots dentro de la sección
                    Route::group(['prefix' => '{section_uid}/slots'], function () {
                        Route::get('/', [WarehouseInventorySlotsController::class, 'index'])->name('detail.section.slots');
                        Route::get('/create', [WarehouseInventorySlotsController::class, 'create'])->name('detail.section.slots.create');
                        Route::post('/store', [WarehouseInventorySlotsController::class, 'store'])->name('detail.section.slots.store');
                        Route::get('/{slot_uid}', [WarehouseInventorySlotsController::class, 'view'])->name('detail.section.slots.view');
                        Route::get('/{slot_uid}/edit', [WarehouseInventorySlotsController::class, 'edit'])->name('detail.section.slots.edit');
                        Route::post('/update', [WarehouseInventorySlotsController::class, 'update'])->name('detail.section.slots.update');
                        Route::get('/{slot_uid}/destroy', [WarehouseInventorySlotsController::class, 'destroy'])->name('detail.section.slots.destroy');

                        // Inventory operations
                        Route::post('/{slot_uid}/add-quantity', [WarehouseInventorySlotsController::class, 'addQuantity'])->name('detail.section.slots.add-quantity');
                        Route::post('/{slot_uid}/subtract-quantity', [WarehouseInventorySlotsController::class, 'subtractQuantity'])->name('detail.section.slots.subtract-quantity');
                        Route::post('/{slot_uid}/clear', [WarehouseInventorySlotsController::class, 'clear'])->name('detail.section.slots.clear');
                        Route::post('/{slot_uid}/move-to', [WarehouseInventorySlotsController::class, 'moveTo'])->name('detail.section.slots.move-to');
                    });
                });
            });
        });
    });
});

Route::group(['prefix' => 'shops'], function () {

    Route::get('/', [ShopsController::class, 'index'])->name('manager.shops');
    Route::get('/create', [ShopsController::class, 'create'])->name('manager.shops.create');
    Route::post('/update', [ShopsController::class, 'update'])->name('manager.shops.update');
    Route::get('/edit/{uid}', [ShopsController::class, 'edit'])->name('manager.shops.edit');
    Route::get('/view/{uid}', [ShopsController::class, 'view'])->name('manager.shops.view');
    Route::get('/destroy/{uid}', [ShopsController::class, 'destroy'])->name('manager.shops.destroy');
    Route::get('/locations/{uid}', [ShopsLocationsController::class, 'index'])->name('manager.shops.locations');

    Route::post('/locations/store', [ShopsLocationsController::class, 'store'])->name('manager.shops.locations.store');
    Route::post('/locations/update', [ShopsLocationsController::class, 'update'])->name('manager.shops.locations.update');
    Route::get('/locations/create/{uid}', [ShopsLocationsController::class, 'create'])->name('manager.shops.locations.create');
    Route::get('/locations/edit/{uid}', [ShopsLocationsController::class, 'edit'])->name('manager.shops.locations.edit');
    Route::get('/locations/view/{uid}', [ShopsLocationsController::class, 'view'])->name('manager.shops.locations.view');
    Route::get('/locations/exists/{uid}', [ShopsLocationsController::class, 'exists'])->name('manager.shops.locations.exists');
    Route::get('/locations/destroy/{uid}', [ShopsLocationsController::class, 'destroy'])->name('manager.shops.locations.destroy');
    Route::get('/locations/all/barcode', [LocationsBarcodesController::class, 'index'])->name('manager.shops.locations.barcodes.all');
    Route::get('/locations/single/barcode/{uid}', [LocationsBarcodesController::class, 'destroy'])->name('manager.shops.locations.barcodes.single');
    Route::get('/locations/historys/{uid}', [ShopsLocationsController::class, 'history'])->name('manager.shops.locations.history');
    Route::post('/locations/exists/validate', [ShopsLocationsController::class, 'checkLocationExists'])->name('manager.shops.locations.exists.validate');

});

Route::group(['prefix' => 'inventaries'], function () {

    Route::get('/validate', [ProductsController::class, 'validate'])->name('manager.inventaries.validate');
    Route::get('/validate/inventaries', [ProductsController::class, 'validateProductShop'])->name('manager.inventaries.shop');
    Route::get('/validate/productss', [ProductsController::class, 'validateProductShops'])->name('manager.inventaries.shops');
    Route::get('/validate/apps', [ProductsController::class, 'validateManagement'])->name('manager.inventaries.apps');

    Route::get('/', [ProductsController::class, 'index'])->name('manager.inventaries');
    Route::get('/all/barcode', [ProductsBarcodesController::class, 'index'])->name('manager.inventaries.barcodes.all');
    Route::get('/reporte/generate/inventary', [ReportController::class, 'generateInventary'])->name('manager.inventaries.generate.inventary');
    Route::get('/reporte/generate/kardex', [ReportController::class, 'generateKardex'])->name('manager.inventaries.generate.kardex');
    Route::get('/create', [ProductsController::class, 'create'])->name('manager.inventaries.create');
    Route::post('/store', [ProductsController::class, 'store'])->name('manager.inventaries.store');
    Route::post('/update', [ProductsController::class, 'update'])->name('manager.inventaries.update');
    Route::get('/edit/{uid}', [ProductsController::class, 'edit'])->name('manager.inventaries.edit');
    Route::get('/view/{uid}', [ProductsController::class, 'view'])->name('manager.locations.view');
    Route::get('/destroy/{uid}', [ProductsController::class, 'destroy'])->name('manager.inventaries.destroy');

    Route::get('/locations/{uid}', [ProductsController::class, 'locations'])->name('manager.inventaries.locations');
    Route::get('/locations/details/{uid}', [ProductsController::class, 'details'])->name('manager.inventaries.locations.details');

    Route::get('/single/barcode/{uid}', [ProductsBarcodesController::class, 'destroy'])->name('manager.inventaries.barcodes.single');
});
