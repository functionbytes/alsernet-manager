<?php

// Campaign routes are now handled by modules\Campaign
// See: modules/Campaign/routes/theme.php
// Dashboard now handled by Core module
use App\Http\Controllers\Managers\PulseController;
use App\Http\Controllers\Managers\Settings\BackupController;
use App\Http\Controllers\Managers\Settings\BackupScheduleController;
use App\Http\Controllers\Managers\Settings\CategoriesController;
use App\Http\Controllers\Managers\Settings\CategoryController;
use App\Http\Controllers\Managers\Settings\DatabaseCleanupController;
use App\Http\Controllers\Managers\Settings\DatabaseSettingsController;
use App\Http\Controllers\Managers\Settings\EmailSettingsController;
use App\Http\Controllers\Managers\Settings\HoursSettingsController;
use App\Http\Controllers\Managers\Settings\IncomingEmailSettingsController;
use App\Http\Controllers\Managers\Settings\LangsController;
use App\Http\Controllers\Managers\Settings\LocalizationSettingsController;
use App\Http\Controllers\Managers\Settings\OutgoingEmailSettingsController;
use App\Http\Controllers\Managers\Settings\SearchSettingsController;
use App\Http\Controllers\Managers\Settings\SettingsController;
use App\Http\Controllers\Managers\Settings\StorageController;
use App\Http\Controllers\Managers\Settings\TranslationController;
// @deprecated System module controllers moved to modules/System
// use App\Http\Controllers\Managers\Settings\MantenanceSettingsController;
// use App\Http\Controllers\Managers\Settings\ServerAccessController;
// use App\Http\Controllers\Managers\Settings\SupervisorController;
// use App\Http\Controllers\Managers\Settings\SystemCacheController;
// use App\Http\Controllers\Managers\Settings\SystemSettingsController;
// use App\Http\Controllers\Managers\Settings\UploadingSettingsController;
// use App\Http\Controllers\Managers\SystemInfoController;
use Illuminate\Support\Facades\Route;
use Modules\Campaign\Http\Controllers\Managers\Campaigns\Products\BarcodeController as ProductsBarcodesController;
use Modules\Campaign\Http\Controllers\Managers\Campaigns\Products\ProductsController;
use Modules\Campaign\Http\Controllers\Managers\Campaigns\Products\ReportController;
use Modules\Helpdesk\Http\Controllers\Managers\AiAgentFlowsController;
use Modules\Helpdesk\Http\Controllers\Managers\AiAgentSettingsController;
use Modules\Helpdesk\Http\Controllers\Managers\CampaignsController as HelpdeskCampaignsController;
use Modules\Helpdesk\Http\Controllers\Managers\ConversationsController as HelpdeskConversationsController;
// Mail controller imports are now handled by modules\Mail
// use App\Http\Controllers\Managers\Settings\Mail\MailVariableController;
// use App\Http\Controllers\Managers\Settings\Mails\MailComponentController;
// use App\Http\Controllers\Managers\Settings\Mails\MailEndpointController;
// use App\Http\Controllers\Managers\Settings\Mails\MailTemplateController;
use Modules\Helpdesk\Http\Controllers\Managers\CustomersController as HelpdeskCustomersController;
use Modules\Helpdesk\Http\Controllers\Managers\HelpCenterController;
// Role and Permission routes are now handled by modules\Role
// See: modules/Role/routes/theme.php
use Modules\Helpdesk\Http\Controllers\Managers\Settings\AttributesController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\SettingsController as SettingsHelpdeskController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\StatusesController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\TagsController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\TeamController;
// @deprecated Supplier controllers moved to modules/Supplier
// use App\Http\Controllers\Managers\Settings\Suppliers\PromptTemplatesController;
// use App\Http\Controllers\Managers\Settings\Suppliers\SupplierAutomationController;
// use App\Http\Controllers\Managers\Settings\Suppliers\SupplierCategoriesController;
// use App\Http\Controllers\Managers\Settings\Suppliers\SupplierContentController;
// use App\Http\Controllers\Managers\Settings\Suppliers\SupplierPromptsController;
// use App\Http\Controllers\Managers\Settings\Suppliers\SuppliersController;
// use App\Http\Controllers\Managers\Settings\Suppliers\SupplierSourcesController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\TicketCannedRepliesController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\TicketCategoriesController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\TicketGroupsController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\TicketSlaPoliciesController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\TicketStatusesController;
use Modules\Helpdesk\Http\Controllers\Managers\Settings\TicketViewsController;
use Modules\Helpdesk\Http\Controllers\Managers\TicketCommentsController;
use Modules\Helpdesk\Http\Controllers\Managers\TicketNotesController;
use Modules\Helpdesk\Http\Controllers\Managers\TicketsController as HelpdeskTicketsController;
use Modules\User\Http\Controllers\UsersController;
// @deprecated Subscriber controllers moved to modules/Subscriber
// use App\Http\Controllers\Managers\Subscribers\SubscribersConditionsController;
// use App\Http\Controllers\Managers\Subscribers\SubscribersController;
// use App\Http\Controllers\Managers\Subscribers\SubscribersListsController;
// use App\Http\Controllers\Managers\Subscribers\SubscribersReportController;
use Modules\Warehouse\Http\Controllers\Settings\Shops\Locations\BarcodeController as LocationsBarcodesController;
use Modules\Warehouse\Http\Controllers\Settings\Shops\Locations\LocationsController as ShopsLocationsController;
// @deprecated Warehouse routes moved to modules/Warehouse/routes/theme.php
// use App\Http\Controllers\Managers\Warehouses\WarehouseController;
// use App\Http\Controllers\Managers\Warehouses\WarehouseDashboardController;
// use App\Http\Controllers\Managers\Warehouses\WarehouseFloorsController;
// use App\Http\Controllers\Managers\Warehouses\WarehouseHistoryController;
// use App\Http\Controllers\Managers\Warehouses\WarehouseInventorySlotsController;
// use App\Http\Controllers\Managers\Warehouses\WarehouseLocationsController;
// use App\Http\Controllers\Managers\Warehouses\WarehouseLocationSectionsController;
// use App\Http\Controllers\Managers\Warehouses\WarehouseLocationStylesController;
// use App\Http\Controllers\Managers\Warehouses\WarehouseMapController;
// use App\Http\Controllers\Managers\Warehouses\WarehouseReportsController;
// Warehouse routes are now handled by the Warehouse module
// use Modules\Warehouse\Http\Controllers\Settings\Shops\Shops\ShopsController;

// use App\Http\Controllers\Managers\Settings\ErpIntegrationSettingsController; // TODO: Controller doesn't exist

Route::prefix('manager')->middleware(['auth'])->group(function () {

    // Redirect to Core module dashboard
    Route::get('/', fn () => redirect()->route('core.dashboard'))->name('manager.dashboard');

    // User Management Routes (APPROACH 1: Middleware-Based)
    // These routes are protected by CheckRolesAndPermissions middleware
    Route::group([
        'prefix' => 'users',
        'name' => 'users.',
        'middleware' => ['check.roles.permissions:manager'],
    ], function () {
        Route::get('/', [UsersController::class, 'index'])->name('index');
        Route::get('/create', [UsersController::class, 'create'])->name('create');
        Route::post('/store', [UsersController::class, 'store'])->name('store');
        Route::get('/{uid}', [UsersController::class, 'view'])->name('view');
        Route::get('/{uid}/edit', [UsersController::class, 'edit'])->name('edit');
        Route::post('/update', [UsersController::class, 'update'])->name('update');
        Route::get('/{uid}/destroy', [UsersController::class, 'destroy'])->name('destroy');
    });


    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoriesController::class, 'index'])->name('manager.categories');
        Route::get('/create', [CategoriesController::class, 'create'])->name('manager.categories.create');
        Route::post('/store', [CategoriesController::class, 'store'])->name('manager.categories.store');
        Route::post('/update', [CategoriesController::class, 'update'])->name('manager.categories.update');
        Route::get('/edit/{uid}', [CategoriesController::class, 'edit'])->name('manager.categories.edit');
        Route::get('/view/{uid}', [CategoriesController::class, 'view'])->name('manager.categories.view');
        Route::get('/destroy/{uid}', [CategoriesController::class, 'destroy'])->name('manager.categories.destroy');
    });

    Route::group(['prefix' => 'langs'], function () {
        Route::get('/', [LangsController::class, 'index'])->name('manager.langs');
        Route::get('/create', [LangsController::class, 'create'])->name('manager.langs.create');
        Route::post('/store', [LangsController::class, 'store'])->name('manager.langs.store');
        Route::post('/update', [LangsController::class, 'update'])->name('manager.langs.update');
        Route::get('/edit/{uid}', [LangsController::class, 'edit'])->name('manager.langs.edit');
        Route::get('/view/{uid}', [LangsController::class, 'view'])->name('manager.langs.view');
        Route::get('/destroy/{uid}', [LangsController::class, 'destroy'])->name('manager.langs.destroy');
        Route::get('/categories', [LangsController::class, 'getCategories'])->name('manager.langs.categories');
    });


    Route::group(['prefix' => 'backups'], function () {

        Route::get('/', [SettingsController::class, 'index'])->name('manager.backups');
        Route::post('/update', [SettingsController::class, 'update'])->name('manager.backups.update');

        Route::post('/favicon', [SettingsController::class, 'storeFavicon'])->name('manager.backups.favicon');
        Route::get('/delete/favicon/{id}', [SettingsController::class, 'deleteFavicon'])->name('manager.backups.favicon.delete');
        Route::get('/get/favicon/{id}', [SettingsController::class, 'getFavicon'])->name('manager.backups.favicon.get');

        Route::post('/logo', [SettingsController::class, 'storeLogo'])->name('manager.backups.logo');
        Route::get('/delete/logo/{id}', [SettingsController::class, 'deleteLogo'])->name('manager.backups.logo.delete');
        Route::get('/get/logo/{id}', [SettingsController::class, 'getLogo'])->name('manager.backups.logo.get');

        // @deprecated Maintenance routes moved to modules/System/routes/web.php
        // Route::get('/maintenance', [MantenanceSettingsController::class, 'index'])->name('manager.backups.maintenance');
        // Route::post('/maintenance/update', [MantenanceSettingsController::class, 'update'])->name('manager.backups.maintenance.update');

        Route::get('/hours', [HoursSettingsController::class, 'index'])->name('manager.backups.hours');
        Route::post('/hours/update', [HoursSettingsController::class, 'update'])->name('manager.backups.hours.update');

        // Storage management routes
        Route::get('/storage', [StorageController::class, 'index'])->name('manager.backups.storage');
        Route::get('/storage/create', [StorageController::class, 'create'])->name('manager.backups.storage.create');
        Route::post('/storage/update', [StorageController::class, 'update'])->name('manager.backups.storage.update');
        Route::delete('/storage', [StorageController::class, 'destroy'])->name('manager.backups.storage.destroy');
        Route::post('/storage', [StorageController::class, 'store'])->name('manager.backups.storage.store');
        Route::get('/storage/{index}/edit', [StorageController::class, 'edit'])->name('manager.backups.storage.edit');
        Route::patch('/storage/{index}', [StorageController::class, 'updateDisk'])->name('manager.backups.storage.update-disk');

        // Category management routes (hierarchical with PrestaShop sync)
        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [CategoryController::class, 'index'])->name('manager.backups.categories.index');
            Route::get('/create', [CategoryController::class, 'create'])->name('manager.backups.categories.create');
            Route::post('/', [CategoryController::class, 'store'])->name('manager.backups.categories.store');
            Route::get('/{uid}/edit', [CategoryController::class, 'edit'])->name('manager.backups.categories.edit');
            Route::put('/{uid}', [CategoryController::class, 'update'])->name('manager.backups.categories.update');
            Route::delete('/{uid}', [CategoryController::class, 'destroy'])->name('manager.backups.categories.destroy');
            Route::post('/reorder', [CategoryController::class, 'reorder'])->name('manager.backups.categories.reorder');
            Route::post('/{uid}/sync-to-prestashop', [CategoryController::class, 'syncToPrestaShop'])->name('manager.backups.categories.sync-to-prestashop');
            Route::post('/sync-from-prestashop', [CategoryController::class, 'syncFromPrestaShop'])->name('manager.backups.categories.sync-from-prestashop');
            Route::get('/conflicts', [CategoryController::class, 'conflicts'])->name('manager.backups.categories.conflicts');
            Route::post('/conflicts/{mapping}/resolve', [CategoryController::class, 'resolveConflict'])->name('manager.backups.categories.conflicts.resolve');
        });

        // Mailer routes are now handled by modules\Mail
        // See: modules/Mail/routes/theme.php
        /*
        Route::group(['prefix' => 'mailers'], function () { ... });
        */


        // Search Settings
        Route::group(['prefix' => 'search'], function () {
            Route::get('/', [SearchSettingsController::class, 'index'])->name('manager.backups.search.index');
            Route::put('/update', [SearchSettingsController::class, 'update'])->name('manager.backups.search.update');
        });

        // Localization Settings
        Route::group(['prefix' => 'localization'], function () {
            Route::get('/', [LocalizationSettingsController::class, 'index'])->name('manager.backups.localization.index');
            Route::put('/update', [LocalizationSettingsController::class, 'update'])->name('manager.backups.localization.update');
        });

        Route::group(['prefix' => 'translations'], function () {
            Route::get('/', [TranslationController::class, 'index'])->name('manager.backups.translations.index');
            Route::get('/edit/{locale}/{file}', [TranslationController::class, 'edit'])->name('manager.backups.translations.edit');
            Route::patch('/update/{locale}/{file}', [TranslationController::class, 'update'])->name('manager.backups.translations.update');
        });

    });


});
