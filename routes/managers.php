<?php

use App\Http\Controllers\Managers\Campaigns\Automations\AutomationsController;
use App\Http\Controllers\Managers\Campaigns\CampaignsController;
use App\Http\Controllers\Managers\Campaigns\Layouts\LayoutController;
use App\Http\Controllers\Managers\Campaigns\Maillists\MaillistController;
use App\Http\Controllers\Managers\Campaigns\Maillists\SegmentController;
use App\Http\Controllers\Managers\Campaigns\Products\BarcodeController as ProductsBarcodesController;
use App\Http\Controllers\Managers\Campaigns\Products\ProductsController;
use App\Http\Controllers\Managers\Campaigns\Products\ReportController;
use App\Http\Controllers\Managers\Campaigns\Templates\TemplatesController;
use App\Http\Controllers\Managers\DashboardController;
use App\Http\Controllers\Managers\Events\EventsController;
use App\Http\Controllers\Managers\Faqs\CategoriesController as FaqsCategoriesController;
use App\Http\Controllers\Managers\Faqs\FaqsController;
use App\Http\Controllers\Managers\Helpdesk\AiAgentFlowsController;
use App\Http\Controllers\Managers\Helpdesk\AiAgentSettingsController;
use App\Http\Controllers\Managers\Helpdesk\CampaignsController as HelpdeskCampaignsController;
use App\Http\Controllers\Managers\Helpdesk\ConversationsController as HelpdeskConversationsController;
use App\Http\Controllers\Managers\Helpdesk\CustomersController as HelpdeskCustomersController;
use App\Http\Controllers\Managers\Helpdesk\HelpCenterController;
use App\Http\Controllers\Managers\Helpdesk\Settings\AttributesController;
use App\Http\Controllers\Managers\Helpdesk\Settings\StatusesController;
use App\Http\Controllers\Managers\Helpdesk\Settings\TagsController;
use App\Http\Controllers\Managers\Helpdesk\Settings\TeamController;
use App\Http\Controllers\Managers\Helpdesk\TicketCommentsController;
use App\Http\Controllers\Managers\Helpdesk\TicketNotesController;
use App\Http\Controllers\Managers\Helpdesk\TicketsController as HelpdeskTicketsController;
use App\Http\Controllers\Managers\Media\MediaManagerController;
use App\Http\Controllers\Managers\NotificationController;
use App\Http\Controllers\Managers\PulseController;
use App\Http\Controllers\Managers\Settings\BackupController;
use App\Http\Controllers\Managers\Settings\BackupScheduleController;
use App\Http\Controllers\Managers\Settings\CategoriesController;
use App\Http\Controllers\Managers\Settings\DatabaseCleanupController;
use App\Http\Controllers\Managers\Settings\DatabaseSettingsController;
use App\Http\Controllers\Managers\Settings\Documents\DocumentConfigurationController;
use App\Http\Controllers\Managers\Settings\Documents\DocumentTypeController;
use App\Http\Controllers\Managers\Settings\EmailSettingsController;
use App\Http\Controllers\Managers\Settings\ErpSettingsController;
use App\Http\Controllers\Managers\Settings\HoursSettingsController;
use App\Http\Controllers\Managers\Settings\IncomingEmailSettingsController;
use App\Http\Controllers\Managers\Settings\LangsController;
use App\Http\Controllers\Managers\Settings\LocalizationSettingsController;
use App\Http\Controllers\Managers\Settings\Mail\MailVariableController;
use App\Http\Controllers\Managers\Settings\Mails\MailComponentController;
use App\Http\Controllers\Managers\Settings\Mails\MailEndpointController;
use App\Http\Controllers\Managers\Settings\Mails\MailTemplateController;
use App\Http\Controllers\Managers\Settings\MantenanceSettingsController;
use App\Http\Controllers\Managers\Settings\OutgoingEmailSettingsController;
use App\Http\Controllers\Managers\Settings\PrestashopSettingsController;
use App\Http\Controllers\Managers\Settings\Roles\PermissionController;
use App\Http\Controllers\Managers\Settings\Roles\RoleController;
use App\Http\Controllers\Managers\Settings\SearchSettingsController;
use App\Http\Controllers\Managers\Settings\ServerAccessController;
use App\Http\Controllers\Managers\Settings\SettingsController;
use App\Http\Controllers\Managers\Settings\SupervisorController;
use App\Http\Controllers\Managers\Settings\SystemCacheController;
use App\Http\Controllers\Managers\Settings\SystemSettingsController;
use App\Http\Controllers\Managers\Settings\TranslationController;
use App\Http\Controllers\Managers\Settings\UploadingSettingsController;
use App\Http\Controllers\Managers\Shops\Locations\BarcodeController as LocationsBarcodesController;
use App\Http\Controllers\Managers\Shops\Locations\LocationsController as ShopsLocationsController;
use App\Http\Controllers\Managers\Shops\Shops\ShopsController;
use App\Http\Controllers\Managers\Subscribers\SubscribersConditionsController;
use App\Http\Controllers\Managers\Subscribers\SubscribersController;
use App\Http\Controllers\Managers\Subscribers\SubscribersListsController;
use App\Http\Controllers\Managers\Subscribers\SubscribersReportController;
use App\Http\Controllers\Managers\SystemInfoController;
use App\Http\Controllers\Managers\Users\UsersController;
use App\Http\Controllers\Managers\Warehouses\WarehouseController;
use App\Http\Controllers\Managers\Warehouses\WarehouseDashboardController;
use App\Http\Controllers\Managers\Warehouses\WarehouseFloorsController;
use App\Http\Controllers\Managers\Warehouses\WarehouseHistoryController;
use App\Http\Controllers\Managers\Warehouses\WarehouseInventorySlotsController;
use App\Http\Controllers\Managers\Warehouses\WarehouseLocationsController;
use App\Http\Controllers\Managers\Warehouses\WarehouseLocationSectionsController;
use App\Http\Controllers\Managers\Warehouses\WarehouseLocationStylesController;
use App\Http\Controllers\Managers\Warehouses\WarehouseMapController;
use App\Http\Controllers\Managers\Warehouses\WarehouseReportsController;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\Managers\Settings\ErpIntegrationSettingsController; // TODO: Controller doesn't exist

Route::prefix('manager')->middleware(['auth'])->group(function () {

    Route::get('/', [DashboardController::class, 'dashboard'])->name('manager.dashboard');

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

    Route::group(['prefix' => 'pulse'], function () {
        Route::get('/', [PulseController::class, 'dashboard'])->name('manager.pulse');
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

    Route::group(['prefix' => 'products'], function () {

        Route::get('/validate', [ProductsController::class, 'validate'])->name('manager.products.validate');
        Route::get('/validate/products', [ProductsController::class, 'validateProductShop'])->name('manager.products.shop');
        Route::get('/validate/productss', [ProductsController::class, 'validateProductShops'])->name('manager.products.shops');
        Route::get('/validate/apps', [ProductsController::class, 'validateManagement'])->name('manager.products.apps');

        Route::get('/', [ProductsController::class, 'index'])->name('manager.products');
        Route::get('/all/barcode', [ProductsBarcodesController::class, 'index'])->name('manager.products.barcodes.all');
        Route::get('/reporte/generate/inventary', [ReportController::class, 'generateInventary'])->name('manager.products.generate.inventary');
        Route::get('/reporte/generate/kardex', [ReportController::class, 'generateKardex'])->name('manager.products.generate.kardex');
        Route::get('/create', [ProductsController::class, 'create'])->name('manager.products.create');
        Route::post('/store', [ProductsController::class, 'store'])->name('manager.products.store');
        Route::post('/update', [ProductsController::class, 'update'])->name('manager.products.update');
        Route::get('/edit/{uid}', [ProductsController::class, 'edit'])->name('manager.products.edit');
        Route::get('/view/{uid}', [ProductsController::class, 'view'])->name('manager.locations.view');
        Route::get('/destroy/{uid}', [ProductsController::class, 'destroy'])->name('manager.products.destroy');

        Route::get('/locations/{uid}', [ProductsController::class, 'locations'])->name('manager.products.locations');
        Route::get('/locations/details/{uid}', [ProductsController::class, 'details'])->name('manager.products.locations.details');

        Route::get('/single/barcode/{uid}', [ProductsBarcodesController::class, 'destroy'])->name('manager.products.barcodes.single');
    });

    Route::group(['prefix' => 'inventaries'], function () {
        // Redirigir a warehouse
        Route::get('/', [WarehouseController::class, 'index'])->name('manager.inventaries');
        Route::get('/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/update', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::get('/edit/{uid}', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::get('/view/{uid}', [WarehouseController::class, 'view'])->name('warehouses.view');
        Route::get('/destroy/{uid}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

        // Rutas de ubicaciones (consolidadas)
        Route::get('/locations/{uid}', [WarehouseLocationsController::class, 'index'])->name('warehouses.locations');
        Route::get('/locations/view/{uid}', [WarehouseLocationsController::class, 'view'])->name('warehouses.locations.details');
        Route::get('/locations/edit/{uid}', [WarehouseLocationsController::class, 'edit'])->name('warehouses.locations.edit');
        Route::get('/locations/destroy/{uid}', [WarehouseLocationsController::class, 'destroy'])->name('warehouses.locations.destroy');
        Route::post('/locations/update', [WarehouseLocationsController::class, 'update'])->name('warehouses.locations.update');

        // Rutas de histórico (consolidadas)
        Route::get('/history', [WarehouseHistoryController::class, 'index'])->name('warehouses.history');
        Route::get('/history/{uid}', [WarehouseHistoryController::class, 'view'])->name('warehouses.history.view');
        Route::get('/history/edit/{uid}', [WarehouseHistoryController::class, 'edit'])->name('warehouses.history.edit');
        Route::post('/history/update', [WarehouseHistoryController::class, 'update'])->name('warehouses.history.update');

        // Rutas de reportes
        Route::get('/report', [WarehouseReportsController::class, 'report'])->name('warehouses.report');
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UsersController::class, 'index'])->name('manager.users');
        Route::get('/create', [UsersController::class, 'create'])->name('manager.users.create');
        Route::post('/store', [UsersController::class, 'store'])->name('manager.users.store');
        Route::post('/update', [UsersController::class, 'update'])->name('manager.users.update');
        Route::get('/edit/{uid}', [UsersController::class, 'edit'])->name('manager.users.edit');
        Route::get('/view/{uid}', [UsersController::class, 'view'])->name('manager.users.view');
        Route::get('/destroy/{uid}', [UsersController::class, 'destroy'])->name('manager.users.destroy');
    });

    Route::group(['prefix' => 'events'], function () {
        Route::get('/', [EventsController::class, 'index'])->name('manager.events');
        Route::get('/create', [EventsController::class, 'create'])->name('manager.events.create');
        Route::post('/store', [EventsController::class, 'store'])->name('manager.events.store');
        Route::post('/update', [EventsController::class, 'update'])->name('manager.events.update');
        Route::get('/edit/{uid}', [EventsController::class, 'edit'])->name('manager.events.edit');
        Route::get('/view/{uid}', [EventsController::class, 'view'])->name('manager.events.view');
        Route::get('/destroy/{uid}', [EventsController::class, 'destroy'])->name('manager.events.destroy');
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

    Route::group(['prefix' => 'subscribers'], function () {

        Route::get('/', [SubscribersController::class, 'index'])->name('manager.subscribers');
        Route::get('/create', [SubscribersController::class, 'create'])->name('manager.subscribers.create');
        Route::post('/update', [SubscribersController::class, 'update'])->name('manager.subscribers.update');
        Route::get('/edit/{uid}', [SubscribersController::class, 'edit'])->name('manager.subscribers.edit');
        Route::get('/view/{uid}', [SubscribersController::class, 'view'])->name('manager.subscribers.view');
        Route::get('/destroy/{uid}', [SubscribersController::class, 'destroy'])->name('manager.subscribers.destroy');
        Route::get('/logs/{slack}', [SubscribersController::class, 'logs'])->name('manager.subscribers.logs');

        Route::get('/imports/create', [SubscribersController::class, 'createImport'])->name('manager.subscribers.imports.create');
        Route::get('/imports/{import_uid}', [SubscribersController::class, 'createImports'])->name('manager.subscribers.import');
        Route::post('/imports/{import_uid}/dispatch', [SubscribersController::class, 'dispatchImportListsJobs'])->name('manager.subscribers.import.dispatch');
        Route::get('/imports/{job_uid}/progress', [SubscribersController::class, 'importListsProgress'])->name('manager.subscribers.import.progress');
        Route::get('/imports/{job_uid}/log/download', [SubscribersController::class, 'downloadImportListsLog'])->name('manager.subscribers.import.log.download');
        Route::post('/imports/{job_uid}/cancel', [SubscribersController::class, 'cancelImportLists'])->name('manager.subscribers.import.cancel');

        Route::get('/lists', [SubscribersListsController::class, 'index'])->name('manager.subscribers.lists');
        Route::get('/list/{uid}', [SubscribersListsController::class, 'list'])->name('manager.subscribers.list');
        Route::get('/lists/report', [SubscribersListsController::class, 'report'])->name('manager.subscribers.lists.report');
        Route::get('/lists/create', [SubscribersListsController::class, 'create'])->name('manager.subscribers.lists.create');
        Route::post('/lists/update', [SubscribersListsController::class, 'update'])->name('manager.subscribers.lists.update');
        Route::post('/lists/store', [SubscribersListsController::class, 'store'])->name('manager.subscribers.lists.store');
        Route::get('/lists/reports', [SubscribersReportController::class, 'report'])->name('manager.subscribers.lists.reports');
        Route::get('/lists/details/{uid}', [SubscribersListsController::class, 'details'])->name('manager.subscribers.lists.details');
        Route::get('/lists/edit/{uid}', [SubscribersListsController::class, 'edit'])->name('manager.subscribers.lists.edit');
        Route::get('/lists/view/{uid}', [SubscribersListsController::class, 'view'])->name('manager.subscribers.lists.view');
        Route::get('/lists/categories/{uid}', [SubscribersListsController::class, 'categories'])->name('manager.subscribers.lists.categories');
        Route::get('/lists/destroy/{uid}', [SubscribersListsController::class, 'destroy'])->name('manager.subscribers.lists.destroy');
        Route::get('/lists/includes/{uid}', [SubscribersListsController::class, 'includes'])->name('manager.subscribers.lists.includes');
        Route::post('/lists/includes/update', [SubscribersListsController::class, 'updateIncludes'])->name('manager.subscribers.lists.includes.update');
        Route::post('/lists/categories/update', [SubscribersListsController::class, 'updateCategories'])->name('manager.subscribers.lists.categories.update');

        Route::get('/lists/report/generate', [SubscribersReportController::class, 'generate'])->name('manager.subscribers.lists.reports.generate');

        Route::get('/conditions', [SubscribersConditionsController::class, 'index'])->name('manager.subscribers.conditions');
        Route::get('/conditions/create', [SubscribersConditionsController::class, 'create'])->name('manager.subscribers.conditions.create');
        Route::post('/conditions/store', [SubscribersConditionsController::class, 'store'])->name('manager.subscribers.conditions.store');
        Route::post('/conditions/update', [SubscribersConditionsController::class, 'update'])->name('manager.subscribers.conditions.update');
        Route::get('/conditions/edit/{uid}', [SubscribersConditionsController::class, 'edit'])->name('manager.subscribers.conditions.edit');
        Route::get('/conditions/view/{uid}', [SubscribersConditionsController::class, 'view'])->name('manager.subscribers.conditions.view');
        Route::get('/conditions/destroy/{uid}', [SubscribersConditionsController::class, 'destroy'])->name('manager.subscribers.conditions.destroy');

    });

    Route::group(['prefix' => 'settings'], function () {

        Route::get('/', [SettingsController::class, 'index'])->name('manager.settings');
        Route::post('/update', [SettingsController::class, 'update'])->name('manager.settings.update');

        Route::post('/favicon', [SettingsController::class, 'storeFavicon'])->name('manager.settings.favicon');
        Route::get('/delete/favicon/{id}', [SettingsController::class, 'deleteFavicon'])->name('manager.settings.favicon.delete');
        Route::get('/get/favicon/{id}', [SettingsController::class, 'getFavicon'])->name('manager.settings.favicon.get');

        Route::post('/logo', [SettingsController::class, 'storeLogo'])->name('manager.settings.logo');
        Route::get('/delete/logo/{id}', [SettingsController::class, 'deleteLogo'])->name('manager.settings.logo.delete');
        Route::get('/get/logo/{id}', [SettingsController::class, 'getLogo'])->name('manager.settings.logo.get');

        Route::get('/maintenance', [MantenanceSettingsController::class, 'index'])->name('manager.settings.maintenance');
        Route::post('/maintenance/update', [MantenanceSettingsController::class, 'update'])->name('manager.settings.maintenance.update');

        Route::get('/hours', [HoursSettingsController::class, 'index'])->name('manager.settings.hours');
        Route::post('/hours/update', [HoursSettingsController::class, 'update'])->name('manager.settings.hours.update');

        Route::group(['prefix' => 'mailers'], function () {

            // Templates
            Route::group(['prefix' => 'templates'], function () {
                Route::get('/', [MailTemplateController::class, 'index'])->name('manager.settings.mailers.templates.index');
                Route::get('/create', [MailTemplateController::class, 'create'])->name('manager.settings.mailers.templates.create');
                Route::post('/', [MailTemplateController::class, 'store'])->name('manager.settings.mailers.templates.store');
                Route::get('/edit/{uid}/{translation_uid?}', [MailTemplateController::class, 'edit'])->name('manager.settings.mailers.templates.edit');
                Route::patch('/{uid}', [MailTemplateController::class, 'update'])->name('manager.settings.mailers.templates.update');
                Route::get('/preview/{uid}', [MailTemplateController::class, 'preview'])->name('manager.settings.mailers.templates.preview');
                Route::get('/preview-ajax/{uid}', [MailTemplateController::class, 'previewAjax'])->name('manager.settings.mailers.templates.preview-ajax');
                Route::get('/variables/{uid}', [MailTemplateController::class, 'getVariables'])->name('manager.settings.mailers.templates.variables');
                Route::delete('/{uid}', [MailTemplateController::class, 'destroy'])->name('manager.settings.mailers.templates.destroy');
                Route::post('/toggle-status/{uid}', [MailTemplateController::class, 'toggleStatus'])->name('manager.settings.mailers.templates.toggle-status');
                Route::post('/send-test/{uid}', [MailTemplateController::class, 'sendTest'])->name('manager.settings.mailers.templates.send-test');
                Route::post('/format-html', [MailTemplateController::class, 'formatHtml'])->name('manager.settings.mailers.templates.format-html');
            });

            // Components
            Route::group(['prefix' => 'components'], function () {
                Route::get('/', [MailComponentController::class, 'index'])->name('manager.settings.mailers.components.index');
                Route::get('/create', [MailComponentController::class, 'create'])->name('manager.settings.mailers.components.create');
                Route::post('/', [MailComponentController::class, 'store'])->name('manager.settings.mailers.components.store');
                Route::get('/edit/{uid}/{translation_uid?}', [MailComponentController::class, 'edit'])->name('manager.settings.mailers.components.edit');
                Route::patch('/{uid}', [MailComponentController::class, 'update'])->name('manager.settings.mailers.components.update');
                Route::get('/preview/{uid}', [MailComponentController::class, 'preview'])->name('manager.settings.mailers.components.preview');
                Route::get('/preview-ajax/{uid}', [MailComponentController::class, 'previewAjax'])->name('manager.settings.mailers.components.preview-ajax');
                Route::get('/variables', [MailComponentController::class, 'variables'])->name('manager.settings.mailers.components.variables');
                Route::delete('/{uid}', [MailComponentController::class, 'destroy'])->name('manager.settings.mailers.components.destroy');
                Route::post('/duplicate/{uid}', [MailComponentController::class, 'duplicate'])->name('manager.settings.mailers.components.duplicate');
            });

            // Variables
            Route::group(['prefix' => 'variables'], function () {
                Route::get('/', [MailVariableController::class, 'index'])->name('manager.settings.mailers.variables.index');
                Route::get('/create', [MailVariableController::class, 'create'])->name('manager.settings.mailers.variables.create');
                Route::post('/', [MailVariableController::class, 'store'])->name('manager.settings.mailers.variables.store');
                Route::get('/edit/{variable}', [MailVariableController::class, 'edit'])->name('manager.settings.mailers.variables.edit');
                Route::patch('/{variable}', [MailVariableController::class, 'update'])->name('manager.settings.mailers.variables.update');
                Route::delete('/{variable}', [MailVariableController::class, 'destroy'])->name('manager.settings.mailers.variables.destroy');
                Route::post('/toggle-status/{variable}', [MailVariableController::class, 'toggleStatus'])->name('manager.settings.mailers.variables.toggle-status');
                Route::get('/by-module', [MailVariableController::class, 'getByModule'])->name('manager.settings.mailers.variables.by-module');
            });

            // Email Endpoints
            Route::group(['prefix' => 'endpoints'], function () {
                Route::get('/documentation', [MailEndpointController::class, 'documentation'])->name('manager.settings.mailers.endpoints.documentation');
                Route::get('/', [MailEndpointController::class, 'index'])->name('manager.settings.mailers.endpoints.index');
                Route::get('/create', [MailEndpointController::class, 'create'])->name('manager.settings.mailers.endpoints.create');
                Route::post('/', [MailEndpointController::class, 'store'])->name('manager.settings.mailers.endpoints.store');
                Route::get('/edit/{emailEndpoint}', [MailEndpointController::class, 'edit'])->name('manager.settings.mailers.endpoints.edit');
                Route::patch('/{emailEndpoint}', [MailEndpointController::class, 'update'])->name('manager.settings.mailers.endpoints.update');
                Route::delete('/{emailEndpoint}', [MailEndpointController::class, 'destroy'])->name('manager.settings.mailers.endpoints.destroy');
                Route::post('/regenerate-token/{emailEndpoint}', [MailEndpointController::class, 'regenerateToken'])->name('manager.settings.mailers.endpoints.regenerate-token');
                Route::get('/logs/{emailEndpoint}', [MailEndpointController::class, 'logs'])->name('manager.settings.mailers.endpoints.logs');
            });
        });

        Route::group(['prefix' => 'documents'], function () {

            Route::get('/', [DocumentConfigurationController::class, 'index'])->name('manager.settings.documents.configurations');

            Route::group(['prefix' => 'configurations'], function () {
                Route::get('/', [DocumentConfigurationController::class, 'globalSettings'])->name('manager.settings.documents.configurations.global');
                Route::post('/', [DocumentConfigurationController::class, 'updateGlobalSettings'])->name('manager.settings.documents.configurations.update');
                Route::get('/search-templates', [DocumentConfigurationController::class, 'searchTemplates'])->name('manager.settings.documents.configurations.search-templates');
            });

            Route::group(['prefix' => 'types'], function () {
                Route::get('/', [DocumentTypeController::class, 'index'])->name('manager.settings.documents.types');
                Route::get('/create', [DocumentTypeController::class, 'create'])->name('manager.settings.documents.types.create');
                Route::post('/', [DocumentTypeController::class, 'store'])->name('manager.settings.documents.types.store');
                Route::get('/edit/{documentType}', [DocumentTypeController::class, 'edit'])->name('manager.settings.documents.types.edit');
                Route::post('/{documentType}', [DocumentTypeController::class, 'update'])->name('manager.settings.documents.types.update');
                Route::delete('/{documentType}', [DocumentTypeController::class, 'destroy'])->name('manager.settings.documents.types.destroy');
                Route::post('/{documentType}/toggle-active', [DocumentTypeController::class, 'toggleActive'])->name('manager.settings.documents.types.toggle-active');
                Route::get('/export/all', [DocumentTypeController::class, 'export'])->name('manager.settings.documents.types.export');
            });

            // SLA Policies
            Route::prefix('sla-policies')->name('sla-policies.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Managers\Settings\DocumentSlaPoliciesController::class, 'index'])->name('index');
                Route::get('create', [\App\Http\Controllers\Managers\Settings\DocumentSlaPoliciesController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Managers\Settings\DocumentSlaPoliciesController::class, 'store'])->name('store');
                Route::get('{policy}/edit', [\App\Http\Controllers\Managers\Settings\DocumentSlaPoliciesController::class, 'edit'])->name('edit');
                Route::put('{policy}', [\App\Http\Controllers\Managers\Settings\DocumentSlaPoliciesController::class, 'update'])->name('update');
                Route::patch('{policy}/toggle', [\App\Http\Controllers\Managers\Settings\DocumentSlaPoliciesController::class, 'toggle'])->name('toggle');
                Route::delete('{policy}', [\App\Http\Controllers\Managers\Settings\DocumentSlaPoliciesController::class, 'destroy'])->name('destroy');
            });

            // Document Settings
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Managers\Settings\Documents\DocumentSettingsController::class, 'index'])->name('index');
                Route::post('/update', [\App\Http\Controllers\Managers\Settings\Documents\DocumentSettingsController::class, 'update'])->name('update');
                Route::post('/store', [\App\Http\Controllers\Managers\Settings\Documents\DocumentSettingsController::class, 'store'])->name('store');
                Route::get('/sections/{section}', [\App\Http\Controllers\Managers\Settings\Documents\DocumentSettingsController::class, 'getSectionSettings'])->name('get-section');
                Route::post('/reset/{group}', [\App\Http\Controllers\Managers\Settings\Documents\DocumentSettingsController::class, 'resetToDefaults'])->name('reset');
            });
        });

        Route::group(['prefix' => 'erp'], function () {
            Route::get('/', [ErpSettingsController::class, 'index'])->name('manager.settings.erp.index');
            Route::get('/edit', [ErpSettingsController::class, 'edit'])->name('manager.settings.erp.edit');
            Route::put('/update', [ErpSettingsController::class, 'update'])->name('manager.settings.erp.update');

            // AJAX Endpoints
            Route::post('/check-connection', [ErpSettingsController::class, 'checkConnection'])->name('manager.settings.erp.check-connection');
            Route::post('/toggle-active', [ErpSettingsController::class, 'toggleActive'])->name('manager.settings.erp.toggle-active');
            Route::post('/clear-cache', [ErpSettingsController::class, 'clearCache'])->name('manager.settings.erp.clear-cache');
            Route::post('/reset-stats', [ErpSettingsController::class, 'resetStats'])->name('manager.settings.erp.reset-stats');
            Route::get('/get-stats', [ErpSettingsController::class, 'getStats'])->name('manager.settings.erp.get-stats');
            Route::post('/test-sync', [ErpSettingsController::class, 'testSync'])->name('manager.settings.erp.test-sync');
        });

        // TODO: ErpIntegrationSettingsController doesn't exist - needs to be created
        // Route::group(['prefix' => 'erp-integration'], function () {
        //     Route::get('/', [ErpIntegrationSettingsController::class, 'index'])->name('manager.settings.erp-integration.index');
        //     Route::get('/edit', [ErpIntegrationSettingsController::class, 'edit'])->name('manager.settings.erp-integration.edit');
        //     Route::put('/update', [ErpIntegrationSettingsController::class, 'update'])->name('manager.settings.erp-integration.update');
        //     Route::post('/toggle', [ErpIntegrationSettingsController::class, 'toggle'])->name('manager.settings.erp-integration.toggle');
        // });

        Route::group(['prefix' => 'prestashop'], function () {
            Route::get('/', [PrestashopSettingsController::class, 'index'])->name('manager.settings.prestashop.index');
            Route::get('/edit', [PrestashopSettingsController::class, 'edit'])->name('manager.settings.prestashop.edit');
            Route::put('/update', [PrestashopSettingsController::class, 'update'])->name('manager.settings.prestashop.update');

            // AJAX Endpoints
            Route::post('/check-connection', [PrestashopSettingsController::class, 'checkConnection'])->name('manager.settings.prestashop.check-connection');
            Route::post('/toggle-active', [PrestashopSettingsController::class, 'toggleActive'])->name('manager.settings.prestashop.toggle-active');
            Route::post('/reset-stats', [PrestashopSettingsController::class, 'resetStats'])->name('manager.settings.prestashop.reset-stats');
            Route::get('/get-stats', [PrestashopSettingsController::class, 'getStats'])->name('manager.settings.prestashop.get-stats');
            Route::post('/test-sync', [PrestashopSettingsController::class, 'testSync'])->name('manager.settings.prestashop.test-sync');
        });

        Route::group(['prefix' => 'email'], function () {
            // Main selection page
            Route::get('/', [EmailSettingsController::class, 'index'])->name('manager.settings.email.index');

            // Outgoing Email (SMTP)
            Route::prefix('outgoing')->group(function () {
                Route::get('/', [OutgoingEmailSettingsController::class, 'index'])->name('manager.settings.email.outgoing.index');
                Route::get('/edit', [OutgoingEmailSettingsController::class, 'edit'])->name('manager.settings.email.outgoing.edit');
                Route::put('/update', [OutgoingEmailSettingsController::class, 'update'])->name('manager.settings.email.outgoing.update');

                // AJAX Endpoints
                Route::post('/test-connection', [OutgoingEmailSettingsController::class, 'testConnection'])->name('manager.settings.email.outgoing.test-connection');
                Route::post('/send-test', [OutgoingEmailSettingsController::class, 'sendTestEmail'])->name('manager.settings.email.outgoing.send-test');
            });

            // Incoming Email (IMAP, Pipe, API, etc.)
            Route::prefix('incoming')->group(function () {
                Route::get('/', [IncomingEmailSettingsController::class, 'index'])->name('manager.settings.email.incoming.index');

                // IMAP Connections
                Route::post('/imap/store', [IncomingEmailSettingsController::class, 'storeImapConnection'])->name('manager.settings.email.incoming.imap.store');
                Route::delete('/imap/{id}', [IncomingEmailSettingsController::class, 'deleteImapConnection'])->name('manager.settings.email.incoming.imap.delete');
                Route::post('/imap/test', [IncomingEmailSettingsController::class, 'testImapConnection'])->name('manager.settings.email.incoming.imap.test');

                // Pipe Handler
                Route::put('/pipe/update', [IncomingEmailSettingsController::class, 'updatePipe'])->name('manager.settings.email.incoming.pipe.update');

                // REST API Handler
                Route::put('/api/update', [IncomingEmailSettingsController::class, 'updateApi'])->name('manager.settings.email.incoming.api.update');
                Route::post('/api/generate-key', [IncomingEmailSettingsController::class, 'generateApiKey'])->name('manager.settings.email.incoming.api.generate-key');
                Route::get('/api/documentation', [IncomingEmailSettingsController::class, 'apiDocumentation'])->name('manager.settings.email.incoming.api.documentation');

                // Gmail API Handler
                Route::put('/gmail/update', [IncomingEmailSettingsController::class, 'updateGmail'])->name('manager.settings.email.incoming.gmail.update');
                Route::get('/gmail/authorize', [IncomingEmailSettingsController::class, 'gmailAuthorize'])->name('manager.settings.email.incoming.gmail.authorize');
                Route::get('/gmail/callback', [IncomingEmailSettingsController::class, 'gmailCallback'])->name('manager.settings.email.incoming.gmail.callback');
                Route::delete('/gmail/{id}', [IncomingEmailSettingsController::class, 'deleteGmailConnection'])->name('manager.settings.email.incoming.gmail.delete');

                // Mailgun Handler
                Route::put('/mailgun/update', [IncomingEmailSettingsController::class, 'updateMailgun'])->name('manager.settings.email.incoming.mailgun.update');

                // phpList Handler
                Route::put('/phplist/update', [IncomingEmailSettingsController::class, 'updatePhplist'])->name('manager.settings.email.incoming.phplist.update');
                Route::post('/phplist/test', [IncomingEmailSettingsController::class, 'testPhplistConnection'])->name('manager.settings.email.incoming.phplist.test');
                Route::get('/phplist/lists', [IncomingEmailSettingsController::class, 'getPhplistLists'])->name('manager.settings.email.incoming.phplist.lists');
                Route::post('/phplist/subscribe', [IncomingEmailSettingsController::class, 'phplistSubscribe'])->name('manager.settings.email.incoming.phplist.subscribe');
            });

            // Legacy routes for backward compatibility
            Route::get('/edit', [OutgoingEmailSettingsController::class, 'edit'])->name('manager.settings.email.edit');
            Route::put('/update', [OutgoingEmailSettingsController::class, 'update'])->name('manager.settings.email.update');
            Route::post('/test-connection', [OutgoingEmailSettingsController::class, 'testConnection'])->name('manager.settings.email.test-connection');
            Route::post('/send-test', [OutgoingEmailSettingsController::class, 'sendTestEmail'])->name('manager.settings.email.send-test');
        });

        Route::group(['prefix' => 'database'], function () {
            Route::get('/', [DatabaseSettingsController::class, 'index'])->name('manager.settings.database.index');
            Route::get('/edit', [DatabaseSettingsController::class, 'edit'])->name('manager.settings.database.edit');
            Route::put('/update', [DatabaseSettingsController::class, 'update'])->name('manager.settings.database.update');

            // AJAX Endpoints
            Route::post('/check-connection', [DatabaseSettingsController::class, 'checkConnection'])->name('manager.settings.database.check-connection');

            // Database Cleanup Routes
            Route::group(['prefix' => 'cleanup'], function () {
                Route::get('/', [DatabaseCleanupController::class, 'index'])->name('manager.settings.database.cleanup.index');
                Route::post('/truncate', [DatabaseCleanupController::class, 'truncate'])->name('manager.settings.database.cleanup.truncate');
                Route::post('/get-table-count', [DatabaseCleanupController::class, 'getTableCount'])->name('manager.settings.database.cleanup.table-count');
            });
        });

        Route::group(['prefix' => 'backups'], function () {
            Route::get('/', [BackupController::class, 'index'])->name('manager.settings.backups.index');
            Route::get('/create', [BackupController::class, 'createForm'])->name('manager.settings.backups.createForm');
            Route::post('/create', [BackupController::class, 'create'])->name('manager.settings.backups.create');
            Route::get('/download/{filename}', [BackupController::class, 'download'])->name('manager.settings.backups.download');
            Route::delete('/delete/{filename}', [BackupController::class, 'delete'])->name('manager.settings.backups.delete');

            // AJAX Endpoints
            Route::get('/status', [BackupController::class, 'getStatus'])->name('manager.settings.backups.status');

            // Backup Schedules
            Route::group(['prefix' => 'schedules'], function () {
                Route::get('/', [BackupScheduleController::class, 'index'])->name('manager.settings.backup-schedules.index');
                Route::get('/create', [BackupScheduleController::class, 'createForm'])->name('manager.settings.backup-schedules.create-form');
                Route::post('/create', [BackupScheduleController::class, 'create'])->name('manager.settings.backup-schedules.create');
                Route::get('/{id}/edit', [BackupScheduleController::class, 'editForm'])->name('manager.settings.backup-schedules.edit-form');
                Route::put('/{id}', [BackupScheduleController::class, 'update'])->name('manager.settings.backup-schedules.update');
                Route::delete('/{id}', [BackupScheduleController::class, 'delete'])->name('manager.settings.backup-schedules.delete');
                Route::post('/{id}/toggle', [BackupScheduleController::class, 'toggle'])->name('manager.settings.backup-schedules.toggle');
                Route::get('/{id}/details', [BackupScheduleController::class, 'getScheduleDetails'])->name('manager.settings.backup-schedules.details');
            });
        });

        Route::group(['prefix' => 'system'], function () {
            // Main System Settings with Queue and WebSockets tabs
            Route::get('/', [SystemSettingsController::class, 'index'])->name('manager.settings.system.index');

            // Queue Settings
            Route::put('/queue/update', [SystemSettingsController::class, 'updateQueue'])->name('manager.settings.system.queue.update');
            Route::post('/queue/test', [SystemSettingsController::class, 'testQueue'])->name('manager.settings.system.queue.test');
            Route::post('/queue/restart', [SystemSettingsController::class, 'restartQueue'])->name('manager.settings.system.queue.restart');

            // WebSockets Settings
            Route::put('/websockets/update', [SystemSettingsController::class, 'updateWebsockets'])->name('manager.settings.system.websockets.update');

            // System Information
            Route::group(['prefix' => 'info'], function () {
                Route::get('/', [SystemInfoController::class, 'index'])->name('manager.settings.system.info.index');
                Route::get('/api', [SystemInfoController::class, 'api'])->name('manager.settings.system.info.api');
            });

            Route::group(['prefix' => 'cache'], function () {
                Route::get('/', [SystemCacheController::class, 'index'])->name('manager.settings.system.cache.index');
                Route::get('/debug', [SystemCacheController::class, 'debug'])->name('manager.settings.system.cache.debug');
                Route::post('/clear-cache', [SystemCacheController::class, 'clearCache'])->name('manager.settings.system.cache.clear-cache');
                Route::post('/clear-config-cache', [SystemCacheController::class, 'clearConfigCache'])->name('manager.settings.system.cache.clear-config-cache');
                Route::post('/cache-config', [SystemCacheController::class, 'cacheConfig'])->name('manager.settings.system.cache.cache-config');
                Route::post('/clear-route-cache', [SystemCacheController::class, 'clearRouteCache'])->name('manager.settings.system.cache.clear-route-cache');
                Route::post('/clear-view-cache', [SystemCacheController::class, 'clearViewCache'])->name('manager.settings.system.cache.clear-view-cache');
                Route::post('/clear-optimization', [SystemCacheController::class, 'clearOptimization'])->name('manager.settings.system.cache.clear-optimization');
                Route::post('/composer-dump-autoload', [SystemCacheController::class, 'composerDumpAutoload'])->name('manager.settings.system.cache.composer-dump-autoload');
                Route::post('/execute-all', [SystemCacheController::class, 'executeAll'])->name('manager.settings.system.cache.execute-all');
            });

            // Server Access & Logs
            Route::group(['prefix' => 'access'], function () {
                Route::get('/', [ServerAccessController::class, 'index'])->name('manager.settings.system.access.index');
                Route::get('/stats', [ServerAccessController::class, 'stats'])->name('manager.settings.system.access.stats');
                Route::post('/clear', [ServerAccessController::class, 'clearLogs'])->name('manager.settings.system.access.clear');
                Route::get('/download', [ServerAccessController::class, 'downloadLogs'])->name('manager.settings.system.access.download');
            });
        });

        // Search Settings
        Route::group(['prefix' => 'search'], function () {
            Route::get('/', [SearchSettingsController::class, 'index'])->name('manager.settings.search.index');
            Route::put('/update', [SearchSettingsController::class, 'update'])->name('manager.settings.search.update');
        });

        // Localization Settings
        Route::group(['prefix' => 'localization'], function () {
            Route::get('/', [LocalizationSettingsController::class, 'index'])->name('manager.settings.localization.index');
            Route::put('/update', [LocalizationSettingsController::class, 'update'])->name('manager.settings.localization.update');
        });

        // Uploading Settings
        Route::group(['prefix' => 'uploading'], function () {
            Route::get('/', [UploadingSettingsController::class, 'index'])->name('manager.settings.uploading.index');
            Route::put('/update', [UploadingSettingsController::class, 'update'])->name('manager.settings.uploading.update');
        });

        Route::group(['prefix' => 'supervisor'], function () {
            // Non-parameterized routes first
            Route::get('/', [SupervisorController::class, 'index'])->name('manager.settings.supervisor.index');
            Route::post('/reload', [SupervisorController::class, 'reload'])->name('manager.settings.supervisor.reload');
            Route::post('/restart', [SupervisorController::class, 'restartSupervisor'])->name('manager.settings.supervisor.restart-service');
            Route::get('/status/ajax', [SupervisorController::class, 'getStatusAjax'])->name('manager.settings.supervisor.status-ajax');

            // Scheduled Jobs & Artisan Commands
            Route::get('/api/scheduled-jobs', [SupervisorController::class, 'getScheduledJobs'])->name('manager.settings.supervisor.scheduled-jobs');
            Route::post('/api/run-scheduler', [SupervisorController::class, 'runScheduler'])->name('manager.settings.supervisor.run-scheduler');
            Route::post('/api/run-command', [SupervisorController::class, 'runCommand'])->name('manager.settings.supervisor.run-command');
            Route::get('/api/list-commands', [SupervisorController::class, 'listCommands'])->name('manager.settings.supervisor.list-commands');

            // Backup & Restore Routes
            Route::get('/backups/list', [SupervisorController::class, 'listBackups'])->name('manager.settings.supervisor.backups-list');
            Route::post('/backups/create', [SupervisorController::class, 'createBackup'])->name('manager.settings.supervisor.backup-create');
            Route::post('/backups/{backupId}/restore', [SupervisorController::class, 'restoreBackup'])->name('manager.settings.supervisor.backup-restore');
            Route::delete('/backups/{backupId}/delete', [SupervisorController::class, 'deleteBackup'])->name('manager.settings.supervisor.backup-delete');
            Route::get('/backups/{backupId}/download', [SupervisorController::class, 'downloadBackup'])->name('manager.settings.supervisor.backup-download');

            // Configuration Routes
            Route::get('/config/files', [SupervisorController::class, 'listConfigFiles'])->name('manager.settings.supervisor.config-files');
            Route::get('/config/file', [SupervisorController::class, 'getConfigFile'])->name('manager.settings.supervisor.config-file');
            Route::post('/config/file/update', [SupervisorController::class, 'updateConfigFile'])->name('manager.settings.supervisor.config-update');

            // Parameterized routes last
            Route::get('/{processName}/show', [SupervisorController::class, 'show'])->name('manager.settings.supervisor.show');
            Route::post('/{processName}/start', [SupervisorController::class, 'start'])->name('manager.settings.supervisor.start');
            Route::post('/{processName}/stop', [SupervisorController::class, 'stop'])->name('manager.settings.supervisor.stop');
            Route::post('/{processName}/restart', [SupervisorController::class, 'restart'])->name('manager.settings.supervisor.restart');
            Route::get('/{processName}/logs', [SupervisorController::class, 'getLogs'])->name('manager.settings.supervisor.logs');
        });

        Route::group(['prefix' => 'roles'], function () {

            // CRUD de roles básico
            Route::get('/', [RoleController::class, 'index'])->name('manager.roles');
            Route::get('/create', [RoleController::class, 'create'])->name('manager.roles.create');
            Route::post('/store', [RoleController::class, 'store'])->name('manager.roles.store');
            Route::get('/{role}/show', [RoleController::class, 'show'])->name('manager.roles.show');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('manager.roles.edit');
            Route::post('/{role}/update', [RoleController::class, 'update'])->name('manager.roles.update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('manager.roles.destroy');

            // Gestión de permisos
            Route::get('/{role}/permissions', [RoleController::class, 'showPermissions'])->name('manager.roles.show.permissions');
            Route::post('/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('manager.roles.update.permissions');

            // Gestión de usuarios asignados a rol
            Route::get('/{role}/users', [RoleController::class, 'showUsers'])->name('manager.roles.show.users');
            Route::post('/{role}/users/assign', [RoleController::class, 'assignUsers'])->name('manager.roles.assign.users');
            Route::delete('/{role}/users/{user}', [RoleController::class, 'removeUser'])->name('manager.roles.remove.user');

            // Métodos avanzados
            Route::post('/{role}/duplicate', [RoleController::class, 'duplicate'])->name('manager.roles.duplicate');

        });

        Route::group(['prefix' => 'permissions'], function () {
            Route::get('/', [PermissionController::class, 'index'])->name('manager.permissions');
            Route::get('/create', [PermissionController::class, 'create'])->name('manager.permissions.create');
            Route::post('/store', [PermissionController::class, 'store'])->name('manager.permissions.store');
            Route::get('/edit/{id}', [PermissionController::class, 'edit'])->name('manager.permissions.edit');
            Route::post('/update', [PermissionController::class, 'update'])->name('manager.permissions.update');
            Route::get('/destroy/{id}', [PermissionController::class, 'destroy'])->name('manager.permissions.destroy');
        });

        Route::group(['prefix' => 'translations'], function () {
            Route::get('/', [TranslationController::class, 'index'])->name('manager.settings.translations.index');
            Route::get('/edit/{locale}/{file}', [TranslationController::class, 'edit'])->name('manager.settings.translations.edit');
            Route::patch('/update/{locale}/{file}', [TranslationController::class, 'update'])->name('manager.settings.translations.update');
        });

    });

    Route::group(['prefix' => 'faqs'], function () {

        Route::get('/', [FaqsController::class, 'index'])->name('manager.faqs');
        Route::get('/create', [FaqsController::class, 'create'])->name('manager.faqs.create');
        Route::post('/store', [FaqsController::class, 'store'])->name('manager.faqs.store');
        Route::post('/update', [FaqsController::class, 'update'])->name('manager.faqs.update');
        Route::get('/edit/{uid}', [FaqsController::class, 'edit'])->name('manager.faqs.edit');
        Route::get('/destroy/{uid}', [FaqsController::class, 'destroy'])->name('manager.faqs.destroy');

        Route::get('/categories', [FaqsCategoriesController::class, 'index'])->name('manager.faqs.categories');
        Route::get('/categories/create', [FaqsCategoriesController::class, 'create'])->name('manager.faqs.categories.create');
        Route::post('/categories/store', [FaqsCategoriesController::class, 'store'])->name('manager.faqs.categories.store');
        Route::post('/categories/update', [FaqsCategoriesController::class, 'update'])->name('manager.faqs.categories.update');
        Route::get('/categories/edit/{uid}', [FaqsCategoriesController::class, 'edit'])->name('manager.faqs.categories.edit');
        Route::get('/categories/destroy/{uid}', [FaqsCategoriesController::class, 'destroy'])->name('manager.faqs.categories.destroy');

    });

    Route::group(['prefix' => 'templates'], function () {

        Route::get('/', [TemplatesController::class, 'index'])->name('manager.templates');
        Route::get('/create', [TemplatesController::class, 'create'])->name('manager.templates.create');
        Route::get('/chat', [TemplatesController::class, 'chat'])->name('manager.templates.chat');
        Route::post('/store', [TemplatesController::class, 'store'])->name('manager.templates.store');
        Route::post('/upload', [TemplatesController::class, 'uploadTemplate'])->name('manager.templates.uploadTemplate');
        Route::post('/update', [TemplatesController::class, 'update'])->name('manager.templates.update');
        Route::get('/delete', [TemplatesController::class, 'delete'])->name('manager.templates.delete');
        Route::get('/edit/{uid}', [TemplatesController::class, 'edit'])->name('manager.templates.edit');
        Route::get('/view/{uid}', [TemplatesController::class, 'view'])->name('manager.templates.view');
        Route::get('/destroy/{uid}', [TemplatesController::class, 'destroy'])->name('manager.templates.destroy');
        Route::get('/preview/{uid}', [TemplatesController::class, 'preview'])->name('manager.templates.preview');
        Route::get('/edit/{uid}', [TemplatesController::class, 'edit'])->name('manager.templates.uid.edit');
        Route::post('/copy/{uid}', [TemplatesController::class, 'copy'])->name('manager.templates.copy.create');
        Route::get('/copy/{uid}', [TemplatesController::class, 'copy'])->name('manager.templates.copy.show');
        Route::post('/export/{uid}', [TemplatesController::class, 'export'])->name('manager.templates.export');
        Route::patch('/update/{uid}', [TemplatesController::class, 'update'])->name('manager.templates.uid.update');

        Route::get('/rss/parse', [TemplatesController::class, 'parseRss'])->name('manager.templates.parseRss');

        Route::get('/listing/{page?}', [TemplatesController::class, 'listing'])->name('manager.templates.listing');
        Route::get('/choosing/{campaign_uid}/{page?}', [TemplatesController::class, 'choosing'])->name('manager.templates.choosing');

        Route::match(['get', 'post'], '/builder/create', [TemplatesController::class, 'builderCreate'])->name('manager.templates.builder.create');
        Route::match(['get', 'post'], '/{uid}/change-name', [TemplatesController::class, 'changeName'])->name('manager.templates.changemame');
        Route::match(['get', 'post'], '/{uid}/categories', [TemplatesController::class, 'categories'])->name('manager.templates.categories');
        Route::match(['get', 'post'], '/{uid}/update-thumb-url', [TemplatesController::class, 'updateThumbUrl'])->name('manager.templates.update.thumburl');
        Route::match(['get', 'post'], '/{uid}/update-thumb', [TemplatesController::class, 'updateThumb'])->name('manager.templates.update.thumb');
        Route::match(['get', 'post'], '/{uid}/builder/edit', [TemplatesController::class, 'builderEdit'])->name('manager.templates.builder.edit');

        Route::get('/builder/templates/{category_uid?}', [TemplatesController::class, 'builderTemplates'])->name('manager.templates.builder.templates');
        Route::post('/{uid}/builder/edit/asset', [TemplatesController::class, 'uploadTemplateAssets'])->name('manager.templates.upload.template.assets');
        Route::get('/{uid}/builder/edit/content', [TemplatesController::class, 'builderEditContent'])->name('manager.templates.builder.edit.content');
        Route::get('/{uid}/builder/change-template/{change_uid}', [TemplatesController::class, 'builderChangeTemplate'])->name('manager.templates.builder.change.template');

    });

    Route::group(['prefix' => 'campaigns'], function () {

        Route::get('/', [CampaignsController::class, 'index'])->name('manager.campaigns');
        Route::get('/create', [CampaignsController::class, 'create'])->name('manager.campaigns.create');
        Route::post('/store', [CampaignsController::class, 'store'])->name('manager.campaigns.store');
        Route::get('/view/{uid}', [CampaignsController::class, 'view'])->name('manager.campaigns.view');
        Route::get('/destroy/{uid}', [CampaignsController::class, 'destroy'])->name('manager.campaigns.destroy');

        Route::post('/{uid}/preheader/remove', [CampaignsController::class, 'preheaderRemove'])->name('manager.campaigns.preheaderRemove');
        Route::match(['get', 'post'], '/{uid}/preheader/add', [CampaignsController::class, 'preheaderAdd'])->name('manager.campaigns.preheaderAdd');
        Route::get('/{uid}/preheader', [CampaignsController::class, 'preheader'])->name('manager.campaigns.preheader');

        Route::post('/webhooks/{webhook_uid}/test/{message_id}', [CampaignsController::class, 'webhooksTestMessage'])->name('manager.campaigns.webhooksTestMessage');
        Route::get('/{uid}/click-log/{message_id}/execute', [CampaignsController::class, 'clickLogExecute'])->name('manager.campaigns.clickLogExecute');
        Route::get('/{uid}/open-log/{message_id}/execute', [CampaignsController::class, 'openLogExecute'])->name('manager.campaigns.openLogExecute');
        Route::match(['get', 'post'], '/webhooks/{webhook_uid}/test', [CampaignsController::class, 'webhooksTest'])->name('manager.campaigns.webhooksTest');
        Route::get('/webhooks/{webhook_uid}/sample/request', [CampaignsController::class, 'webhooksSampleRequest'])->name('manager.campaigns.webhooksSampleRequest');
        Route::post('/webhooks/{webhook_uid}/delete', [CampaignsController::class, 'webhooksDelete'])->name('manager.campaigns.webhooksDelete');
        Route::match(['get', 'post'], '/webhooks/{webhook_uid}/edit', [CampaignsController::class, 'webhooksEdit'])->name('manager.campaigns.webhooksEdit');
        Route::get('/{uid}/webhooks/list', [CampaignsController::class, 'webhooksList'])->name('manager.campaigns.webhooksList');
        Route::get('/{uid}/webhooks/link-select', [CampaignsController::class, 'webhooksLinkSelect'])->name('manager.campaigns.webhooksLinkSelect');
        Route::match(['get', 'post'], '/{uid}/webhooks/add', [CampaignsController::class, 'webhooksAdd'])->name('manager.campaigns.webhooksAdd');
        Route::get('/{uid}/webhooks', [CampaignsController::class, 'webhooks'])->name('manager.campaigns.webhooks');

        Route::get('/{uid}/preview-as/list', [CampaignsController::class, 'previewAsList'])->name('manager.campaigns.previewAsList');
        Route::get('/{uid}/preview-as', [CampaignsController::class, 'previewAs'])->name('manager.campaigns.previewAs');

        Route::post('/{uid}/custom-plain/off', [CampaignsController::class, 'customPlainOff'])->name('manager.campaigns.customPlainOff');
        Route::post('/{uid}/custom-plain/on', [CampaignsController::class, 'customPlainOn'])->name('manager.campaigns.customPlainOn');
        Route::post('/{uid}/remove-attachment', [CampaignsController::class, 'removeAttachment'])->name('manager.campaigns.removeAttachment');
        Route::get('/{uid}/download-attachment', [CampaignsController::class, 'downloadAttachment'])->name('manager.campaigns.downloadAttachment');
        Route::post('/{uid}/upload-attachment', [CampaignsController::class, 'uploadAttachment'])->name('manager.campaigns.uploadAttachment');
        Route::get('/{uid}/template/builder-select', [CampaignsController::class, 'templateBuilderSelect'])->name('manager.campaigns.templateBuilderSelect');

        Route::match(['get', 'post'], '/{uid}/template/builder-plain', [CampaignsController::class, 'builderPlainEdit'])->name('manager.campaigns.builderPlainEdit');
        Route::match(['get', 'post'], '/{uid}/template/builder-classic', [CampaignsController::class, 'builderClassic'])->name('manager.campaigns.builderClassic');
        Route::match(['get', 'post'], '/{uid}/plain', [CampaignsController::class, 'plain'])->name('manager.campaigns.plain');
        Route::get('/{uid}/template/change/{template_uid}', [CampaignsController::class, 'templateChangeTemplate'])->name('manager.campaigns.templateChangeTemplate');

        Route::get('/{uid}/template/content', [CampaignsController::class, 'templateContent'])->name('manager.campaigns.templateContent');
        Route::match(['get', 'post'], '/{uid}/template/edit', [CampaignsController::class, 'templateEdit'])->name('manager.campaigns.templateEdit');
        Route::match(['get', 'post'], '/{uid}/template/upload', [CampaignsController::class, 'templateUpload'])->name('manager.campaigns.templateUpload');
        Route::get('/{uid}/template/layout/list', [CampaignsController::class, 'templateLayoutList'])->name('manager.campaigns.templateLayoutList');
        Route::match(['get', 'post'], '/{uid}/template/layout', [CampaignsController::class, 'templateLayout'])->name('manager.campaigns.templateLayout');
        Route::get('/{uid}/template/create', [CampaignsController::class, 'templateCreate'])->name('manager.campaigns.templateCreate');

        Route::get('/{uid}/spam-score', [CampaignsController::class, 'spamScore'])->name('manager.campaigns.spamScore');
        Route::get('/{from_uid}/copy-move-from/{action}', [CampaignsController::class, 'copyMoveForm'])->name('manager.campaigns.copyMoveForm');
        Route::match(['get', 'post'], '/{uid}/resend', [CampaignsController::class, 'resend'])->name('manager.campaigns.resend');
        Route::get('/{uid}/tracking-log/download', [CampaignsController::class, 'trackingLogDownload'])->name('manager.campaigns.trackingLogDownload');
        Route::get('/job/{uid}/progress', [CampaignsController::class, 'trackingLogExportProgress'])->name('manager.campaigns.trackingLogExportProgress');
        Route::get('/job/{uid}/download', [CampaignsController::class, 'download'])->name('manager.campaigns.download');

        Route::get('/{uid}/template/review-iframe', [CampaignsController::class, 'templateReviewIframe'])->name('manager.campaigns.templateReviewIframe');
        Route::get('/{uid}/template/review', [CampaignsController::class, 'templateReview'])->name('manager.campaigns.templateReview');
        Route::get('/select-type', [CampaignsController::class, 'selecttype'])->name('manager.campaigns.selecttype');
        Route::get('/{uid}/list-segment-form', [CampaignsController::class, 'listSegmentForm'])->name('manager.campaigns.list.segment.form');
        Route::get('/{uid}/preview/content/{subscriber_uid?}', [CampaignsController::class, 'previewContent'])->name('manager.campaigns.previewContent');
        Route::get('/{uid}/preview', [CampaignsController::class, 'preview'])->name('manager.campaigns.preview');
        Route::match(['get', 'post'], '/send-test-email', [CampaignsController::class, 'sendTestEmail'])->name('manager.campaigns.send.test');
        Route::get('/delete/confirm', [CampaignsController::class, 'deleteConfirm'])->name('manager.campaigns.deleteConfirm');
        Route::match(['get', 'post'], '/copy', [CampaignsController::class, 'copy'])->name('manager.campaigns.copy');

        Route::get('/{uid}/subscribers', [CampaignsController::class, 'subscribers'])->name('manager.campaigns.subscribers');
        Route::get('/{uid}/subscribers/listing', [CampaignsController::class, 'subscribersListing'])->name('manager.campaigns.subscribers.listing');
        Route::get('/{uid}/open-map', [CampaignsController::class, 'openMap'])->name('manager.campaigns.open.map');
        Route::get('/{uid}/tracking-log', [CampaignsController::class, 'trackingLog'])->name('manager.campaigns.tracking.log');
        Route::get('/{uid}/tracking-log/listing', [CampaignsController::class, 'trackingLogListing'])->name('manager.campaigns.tracking.log.listing');
        Route::get('/{uid}/bounce-log', [CampaignsController::class, 'bounceLog'])->name('manager.campaigns.bounceLog');
        Route::get('/{uid}/bounce-log/listing', [CampaignsController::class, 'bounceLogListing'])->name('manager.campaigns.bounce.log.listing');
        Route::get('/{uid}/feedback-log', [CampaignsController::class, 'feedbackLog'])->name('manager.campaigns.feedbackLog');
        Route::get('/{uid}/feedback-log/listing', [CampaignsController::class, 'feedbackLogListing'])->name('manager.campaigns.feedback.log.listing');
        Route::get('/{uid}/open-log', [CampaignsController::class, 'openLog'])->name('manager.campaigns.open.log');
        Route::get('/{uid}/open-log/listing', [CampaignsController::class, 'openLogListing'])->name('manager.campaigns.open.log.listing');
        Route::get('/{uid}/click-log', [CampaignsController::class, 'clickLog'])->name('manager.campaigns.click.log');
        Route::get('/{uid}/click-log/listing', [CampaignsController::class, 'clickLogListing'])->name('manager.campaigns.click.log.listing');
        Route::get('/{uid}/unsubscribe-log', [CampaignsController::class, 'unsubscribeLog'])->name('manager.campaigns.unsubscribe.log');
        Route::get('/{uid}/unsubscribe-log/listing', [CampaignsController::class, 'unsubscribeLogListing'])->name('manager.campaigns.unsubscribe.log.listing');

        Route::get('/quick-view', [CampaignsController::class, 'quickView'])->name('manager.campaigns.quickView');
        Route::get('/{uid}/chart24h', [CampaignsController::class, 'chart24h'])->name('manager.campaigns.chart24h');
        Route::get('/{uid}/chart', [CampaignsController::class, 'chart'])->name('manager.campaigns.chart');
        Route::get('/{uid}/chart/countries/open', [CampaignsController::class, 'chartCountry'])->name('manager.campaigns.chartCountry');
        Route::get('/{uid}/chart/countries/click', [CampaignsController::class, 'chartClickCountry'])->name('manager.campaigns.chartClickCountry');
        Route::get('/{uid}/overview', [CampaignsController::class, 'overview'])->name('manager.campaigns.overview');
        Route::get('/{uid}/links', [CampaignsController::class, 'links'])->name('manager.campaigns.links');

        Route::get('/listing/{page?}', [CampaignsController::class, 'listing'])->name('manager.campaigns.listing');

        Route::match(['get', 'post'], '/{uid}/setup', [CampaignsController::class, 'setup'])->name('manager.campaigns.setup');
        Route::match(['get', 'post'], '/{uid}/template', [CampaignsController::class, 'template'])->name('manager.campaigns.template');
        Route::match(['get', 'post'], '/{uid}/recipients', [CampaignsController::class, 'recipients'])->name('manager.campaigns.recipients');
        Route::match(['get', 'post'], '/{uid}/schedule', [CampaignsController::class, 'schedule'])->name('manager.campaigns.schedule');
        Route::match(['get', 'post'], '/{uid}/confirm', [CampaignsController::class, 'confirm'])->name('manager.campaigns.confirm');

        Route::get('/{uid}/template/select', [CampaignsController::class, 'templateSelect'])->name('manager.campaigns.templateSelect');
        Route::get('/{uid}/template/choose/{template_uid}', [CampaignsController::class, 'templateChoose'])->name('manager.campaigns.templateChoose');
        Route::get('/{uid}/template/preview', [CampaignsController::class, 'templatePreview'])->name('manager.campaigns.templatePreview');
        Route::get('/{uid}/template/iframe', [CampaignsController::class, 'templateIframe'])->name('manager.campaigns.templateIframe');
        Route::get('/{uid}/template/build/{style}', [CampaignsController::class, 'templateBuild'])->name('manager.campaigns.templateBuild');
        Route::get('/{uid}/template/rebuild', [CampaignsController::class, 'templateRebuild'])->name('manager.campaigns.templateRebuild');

        Route::post('/delete', [CampaignsController::class, 'delete'])->name('manager.campaigns.delete');
        Route::get('/select2', [CampaignsController::class, 'select2'])->name('manager.campaigns.select2');
        Route::post('/pause', [CampaignsController::class, 'pause'])->name('manager.campaigns.pause');
        Route::post('/restart', [CampaignsController::class, 'restart'])->name('manager.campaigns.restart');
        Route::get('/{uid}/edit', [CampaignsController::class, 'edit'])->name('manager.campaigns.edit');
        Route::patch('/{uid}/update', [CampaignsController::class, 'update'])->name('manager.campaigns.update');
        Route::get('/{uid}/run', [CampaignsController::class, 'run'])->name('manager.campaigns.run');
        Route::get('/{uid}/update-stats', [CampaignsController::class, 'updateStats'])->name('manager.campaigns.updateStats');

    });

    Route::group(['prefix' => 'segments'], function () {

        Route::get('/no-list', [SegmentController::class, 'noList'])->name('manager.segments.noList');
        Route::get('/condition-value-control', [SegmentController::class, 'conditionValueControl'])->name('manager.segments.conditionValueControl');
        Route::get('/select_box', [SegmentController::class, 'selectBox'])->name('manager.segments.selectBox');
        Route::get('/lists/{list_uid}/segments', [SegmentController::class, 'index'])->name('manager.segments.index');
        Route::get('/lists/{list_uid}/segments/{uid}/subscribers', [SegmentController::class, 'subscribers'])->name('manager.segments.subscribers.');
        Route::get('/lists/{list_uid}/segments/{uid}/listing_subscribers', [SegmentController::class, 'listing_subscribers'])->name('manager.segments.listing_subscribers');
        Route::get('/lists/{list_uid}/segments/create', [SegmentController::class, 'create'])->name('manager.segments.create');
        Route::get('/lists/{list_uid}/segments/listing', [SegmentController::class, 'listing'])->name('manager.segments.listing');
        Route::post('/lists/{list_uid}/segments/store', [SegmentController::class, 'store'])->name('manager.segments.store');
        Route::get('/lists/{list_uid}/segments/{uid}/edit', [SegmentController::class, 'edit'])->name('manager.segments.edit');
        Route::patch('/lists/{list_uid}/segments/{uid}/update', [SegmentController::class, 'update'])->name('manager.segments.update');
        Route::get('/lists/{list_uid}/segments/delete', [SegmentController::class, 'delete'])->name('manager.segments.delete');
        Route::get('/lists/{list_uid}/segments/sample_condition', [SegmentController::class, 'sample_condition'])->name('manager.segments.sample_condition');

    });

    Route::group(['prefix' => 'maillists'], function () {

        Route::get('/', [MaillistController::class, 'index'])->name('manager.maillists');
        Route::get('/create', [MaillistController::class, 'create'])->name('manager.maillists.create');
        Route::post('/store', [MaillistController::class, 'store'])->name('manager.maillists.store');
        Route::get('/edit/{uid}', [MaillistController::class, 'edit'])->name('manager.maillists.edit');
        Route::get('/view/{uid}', [MaillistController::class, 'view'])->name('manager.maillists.view');
        Route::get('/destroy/{uid}', [MaillistController::class, 'destroy'])->name('manager.maillists.destroy');

        Route::match(['get', 'post'], '/lists/select', [MaillistController::class, 'selectList'])->name('manager.maillists.selectList');
        Route::get('/lists/{uid}/email-verification/chart', [MaillistController::class, 'emailVerificationChart'])->name('manager.maillists.emailVerificationChart');
        Route::get('/lists/{uid}/clone-to-customers/choose', [MaillistController::class, 'cloneForCustomersChoose'])->name('manager.maillists.cloneForCustomersChoose');
        Route::post('/lists/{uid}/clone-to-customers', [MaillistController::class, 'cloneForCustomers'])->name('manager.maillists.cloneForCustomers');

        Route::get('/lists/{uid}/verification/{job_uid}/progress', [MaillistController::class, 'verificationProgress'])->name('manager.maillists.verificationProgress');
        Route::get('/lists/{uid}/verification', [MaillistController::class, 'verification'])->name('manager.maillists.verification');
        Route::post('/lists/{uid}/verification/start', [MaillistController::class, 'startVerification'])->name('manager.maillists.startVerification');
        Route::post('/lists/{uid}/verification/{job_uid}/stop', [MaillistController::class, 'stopVerification'])->name('manager.maillists.stopVerification');
        Route::post('/lists/{uid}/verification/reset', [MaillistController::class, 'resetVerification'])->name('manager.maillists.resetVerification');

        Route::match(['get', 'post'], '/lists/copy', [MaillistController::class, 'copy'])->name('manager.maillists.copy');
        Route::get('/lists/quick-view', [MaillistController::class, 'quickView'])->name('manager.maillists.quickView');
        Route::get('/lists/{uid}/list-growth', [MaillistController::class, 'listGrowthChart'])->name('manager.maillists.listGrowthChart');
        Route::get('/lists/{uid}/list-statistics-chart', [MaillistController::class, 'statisticsChart'])->name('manager.maillists.statisticsChart');
        Route::get('/lists/sort', [MaillistController::class, 'sort'])->name('manager.maillists.sort');
        Route::get('/lists/listing/{page?}', [MaillistController::class, 'listing'])->name('manager.maillists.listing');
        Route::post('/lists/delete', [MaillistController::class, 'delete'])->name('manager.maillists.delete');
        Route::get('/lists/delete/confirm', [MaillistController::class, 'deleteConfirm'])->name('manager.maillists.delete.confirm');

        Route::get('/lists/{uid}/overview', [MaillistController::class, 'overview'])->name('manager.maillists.overview');
        Route::get('/lists/{uid}/edit', [MaillistController::class, 'edit'])->name('manager.maillists.lists.edit');
        Route::post('/lists/{uid}/update', [MaillistController::class, 'update'])->name('manager.maillists.update');
        Route::match(['get', 'post'], '/lists/{uid}/embedded-form', [MaillistController::class, 'embeddedForm'])->name('manager.maillists.embeddedForm');
        Route::get('/lists/{uid}/embedded-form-frame', [MaillistController::class, 'embeddedFormFrame'])->name('manager.maillists.embeddedFormFrame');

        Route::post('/lists/{uid}/embedded-form-subscribe', [MaillistController::class, 'embeddedFormSubscribe'])->name('manager.maillists.embeddedFormSubscribe');
        Route::post('/lists/{uid}/embedded-form-subscribe-captcha', [MaillistController::class, 'embeddedFormSubscribe'])->name('manager.maillists.embeddedFormSubscribeCaptcha');

        Route::get('/lists/{uid}/check-email', [AutomationsController::class, 'checkEmail'])->name('manager.maillists.checkEmail');
    });

    Route::group(['prefix' => 'automations'], function () {

        // Automation2
        Route::post('/{uid}/trigger-all', [AutomationsController::class, 'triggerAll'])->name('manager.automations.triggerAll');

        Route::match(['get', 'post'], 'automation/{uid}/copy', [AutomationsController::class, 'copy'])->name('manager.automations.copy');
        Route::get('/{uid}/condition/remove', [AutomationsController::class, 'conditionRemove'])->name('manager.automations.conditionRemove');

        Route::post('/{uid}/template/{email_uid}/preheader/remove', [AutomationsController::class, 'emailPreheaderRemove'])->name('manager.automations.emailPreheaderRemove');
        Route::match(['get', 'post'], 'automation/{uid}/template/{email_uid}/preheader/add', [AutomationsController::class, 'emailPreheaderAdd'])->name('manager.automations.emailPreheaderAdd');
        Route::get('/{uid}/template/{email_uid}/preheader', [AutomationsController::class, 'emailPreheader'])->name('manager.automations.emailPreheader');

        Route::match(['get', 'post'], 'automation/condition/wait/custom', [AutomationsController::class, 'conditionWaitCustom'])->name('manager.automations.conditionWaitCustom');
        Route::match(['get', 'post'], 'automation/{email_uid}/send-test-email', [AutomationsController::class, 'sendTestEmail'])->name('manager.automations.send.test');
        Route::get('/{uid}/cart/items', [AutomationsController::class, 'cartItems'])->name('manager.automations.cartItems');
        Route::get('/{uid}/cart/list', [AutomationsController::class, 'cartList'])->name('manager.automations.cartList');
        Route::get('/{uid}/cart/stats', [AutomationsController::class, 'cartStats'])->name('manager.automations.cartStats');
        Route::match(['get', 'post'], 'automation/{uid}/cart/change-store', [AutomationsController::class, 'cartChangeStore'])->name('manager.automations.cartChangeStore');
        Route::match(['get', 'post'], 'automation/{uid}/cart/wait', [AutomationsController::class, 'cartWait'])->name('manager.automations.cartWait');
        Route::match(['get', 'post'], 'automation/{uid}/cart/change-list', [AutomationsController::class, 'cartChangeList'])->name('manager.automations.cartChangeList');

        Route::get('/{uid}/condition/setting', [AutomationsController::class, 'conditionSetting'])->name('manager.automations.conditionSetting');
        Route::get('/{uid}/operation/show', [AutomationsController::class, 'operationShow'])->name('manager.automations.operationShow');
        Route::match(['get', 'post'], 'automation/{uid}/operation/edit', [AutomationsController::class, 'operationEdit'])->name('manager.automations.operation.edit');
        Route::match(['get', 'post'], 'automation/{uid}/operation/create', [AutomationsController::class, 'operationCreate'])->name('manager.automations.operation.create');
        Route::get('/{uid}/operation/select', [AutomationsController::class, 'operationSelect'])->name('manager.automations.operation.select');

        Route::post('/{uid}/wait-time', [AutomationsController::class, 'waitTime'])->name('manager.automations.waitTime.update');
        Route::get('/{uid}/wait-time', [AutomationsController::class, 'waitTime'])->name('manager.automations.waitTime');
        Route::get('/{uid}/last-saved', [AutomationsController::class, 'lastSaved'])->name('manager.automations.lastSaved');

        Route::post('/{uid}/subscribers/{subscriber_uid}/restart', [AutomationsController::class, 'subscribersRestart'])->name('manager.automations.subscribers.restart');
        Route::post('/{uid}/subscribers/{subscriber_uid}/remove', [AutomationsController::class, 'subscribersRemove'])->name('manager.automations.subscribers.remove');
        Route::get('/{uid}/subscribers/{subscriber_uid}/show', [AutomationsController::class, 'subscribersShow'])->name('manager.automations.subscribers.Show');
        Route::get('/{uid}/subscribers/list', [AutomationsController::class, 'subscribersList'])->name('manager.automations.subscribers.List');
        Route::get('/{uid}/subscribers', [AutomationsController::class, 'subscribers'])->name('manager.automations.subscribers.');

        Route::get('/{uid}/insight', [AutomationsController::class, 'insight'])->name('manager.automations.insight');
        Route::post('/{uid}/data/save', [AutomationsController::class, 'saveData'])->name('manager.automations.saveData');
        Route::post('/{uid}/update', [AutomationsController::class, 'update'])->name('manager.automations.update');
        Route::get('/{uid}/settings', [AutomationsController::class, 'settings'])->name('manager.automations.settings');

        Route::match(['get', 'post'], 'automation/emails/webhooks/{webhook_uid}/test', [AutomationsController::class, 'webhooksTest'])->name('manager.automations.webhooksTest');
        Route::get('/emails/webhooks/{webhook_uid}/sample/request', [AutomationsController::class, 'webhooksSampleRequest'])->name('manager.automations.webhooksSampleRequest');
        Route::post('/emails/webhooks/{webhook_uid}/delete', [AutomationsController::class, 'webhooksDelete'])->name('manager.automations.webhooksDelete');
        Route::match(['get', 'post'], 'automation/emails/webhooks/{webhook_uid}/edit', [AutomationsController::class, 'webhooksEdit'])->name('manager.automations.webhooksEdit');
        Route::get('/emails/{email_uid}/webhooks/list', [AutomationsController::class, 'webhooksList'])->name('manager.automations.webhooksList');
        Route::get('/emails/{email_uid}/webhooks/link-select', [AutomationsController::class, 'webhooksLinkSelect'])->name('manager.automations.webhooksLinkSelect');
        Route::match(['get', 'post'], 'automation/emails/{email_uid}/webhooks/add', [AutomationsController::class, 'webhooksAdd'])->name('manager.automations.webhooksAdd');
        Route::get('/emails/{email_uid}/webhooks', [AutomationsController::class, 'webhooks'])->name('manager.automations.webhooks');

        Route::post('/disable', [AutomationsController::class, 'disable'])->name('manager.automations.disable');
        Route::post('/enable', [AutomationsController::class, 'enable'])->name('manager.automations.enable');
        Route::delete('/delete', [AutomationsController::class, 'delete'])->name('manager.automations.delete');
        Route::get('/listing', [AutomationsController::class, 'listing'])->name('manager.automations.listing');
        Route::get('/', [AutomationsController::class, 'index'])->name('manager.automations');
        Route::get('/{uid}/debug', [AutomationsController::class, 'debug'])->name('manager.automations.debug');
        Route::get('trigger/{id}', [AutomationsController::class, 'show'])->name('manager.automations.show');
        Route::get('/{automation}/{subscriber}/trigger', [AutomationsController::class, 'triggerNow'])->name('manager.automations.triggerNow');
        Route::get('/{automation}/run', [AutomationsController::class, 'run'])->name('manager.automations.run');

    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'index'])->name('manager.notifications');
        Route::get('/popup', [NotificationController::class, 'popup'])->name('manager.notifications.popup');
        Route::post('/delete', [NotificationController::class, 'delete'])->name('manager.notifications.delete');
        Route::post('/listing', [NotificationController::class, 'listing'])->name('manager.notifications.listing');
    });

    Route::group(['prefix' => 'layouts'], function () {
        Route::get('/', [LayoutController::class, 'index'])->name('manager.layouts');
        Route::get('/create', [LayoutController::class, 'create'])->name('manager.layouts.create');
        Route::post('/store', [LayoutController::class, 'store'])->name('manager.layouts.store');
        Route::patch('/update/{uid}', [LayoutController::class, 'update'])->name('manager.layouts.update');
        Route::get('/edit/{uid}', [LayoutController::class, 'edit'])->name('manager.layouts.edit');
        Route::get('/view/{uid}', [LayoutController::class, 'view'])->name('manager.layouts.view');
        Route::get('/destroy/{uid}', [LayoutController::class, 'destroy'])->name('manager.layouts.destroy');

        Route::get('/listing/{page?}', [LayoutController::class, 'listing'])->name('manager.layouts.listing');
        Route::get('/sort', [LayoutController::class, 'sort'])->name('manager.layouts.sort');
    });

    Route::group(['prefix' => 'warehouse'], function () {

        // Warehouse Map (Visual Floor Plan)
        Route::get('/map', [WarehouseMapController::class, 'map'])->name('map');
        Route::get('/api/layout-spec', [WarehouseMapController::class, 'getLayoutSpec'])->name('manager.warehouse.api.layout');
        Route::get('/api/config', [WarehouseMapController::class, 'getWarehouseConfig'])->name('manager.warehouse.api.config');
        Route::get('/api/slot/{uid}', [WarehouseMapController::class, 'getSlotDetails'])->name('manager.warehouse.api.slot');

        // Visual editing endpoints (Opción 2)
        Route::put('/location/{location_uid}/visual-config', [WarehouseMapController::class, 'updateVisualConfig'])->name('manager.warehouse.location.visual.update');
        Route::post('/location/{location_uid}/reset-visual', [WarehouseMapController::class, 'resetVisualConfig'])->name('manager.warehouse.location.visual.reset');

        // Floors Routes
        Route::group(['prefix' => 'floors'], function () {
            Route::get('/', [WarehouseFloorsController::class, 'index'])->name('floors');
            Route::get('/create', [WarehouseFloorsController::class, 'create'])->name('manager.warehouse.floors.create');
            Route::post('/store', [WarehouseFloorsController::class, 'store'])->name('manager.warehouse.floors.store');
            Route::post('/update', [WarehouseFloorsController::class, 'update'])->name('manager.warehouse.floors.update');
            Route::get('/edit/{uid}', [WarehouseFloorsController::class, 'edit'])->name('manager.warehouse.floors.edit');
            Route::get('/view/{uid}', [WarehouseFloorsController::class, 'view'])->name('manager.warehouse.floors.view');
            Route::get('/destroy/{uid}', [WarehouseFloorsController::class, 'destroy'])->name('manager.warehouse.floors.destroy');
        });

        // Stand Styles Routes
        Route::group(['prefix' => 'styles'], function () {
            Route::get('/', [WarehouseLocationStylesController::class, 'index'])->name('manager.warehouse.styles.index');
            Route::get('/create', [WarehouseLocationStylesController::class, 'create'])->name('manager.warehouse.styles.create');
            Route::post('/store', [WarehouseLocationStylesController::class, 'store'])->name('manager.warehouse.styles.store');
            Route::post('/update', [WarehouseLocationStylesController::class, 'update'])->name('manager.warehouse.styles.update');
            Route::get('/edit/{uid}', [WarehouseLocationStylesController::class, 'edit'])->name('manager.warehouse.styles.edit');
            Route::get('/view/{uid}', [WarehouseLocationStylesController::class, 'view'])->name('manager.warehouse.styles.view');
            Route::get('/destroy/{uid}', [WarehouseLocationStylesController::class, 'destroy'])->name('manager.warehouse.styles.destroy');
        });

        // Stands Routes (Consolidated with Locations)
        Route::group(['prefix' => 'stands'], function () {
            Route::get('/', [WarehouseLocationsController::class, 'index'])->name('stands');
            Route::get('/create', [WarehouseLocationsController::class, 'create'])->name('manager.warehouse.stands.create');
            Route::post('/store', [WarehouseLocationsController::class, 'store'])->name('manager.warehouse.stands.store');
            Route::post('/update', [WarehouseLocationsController::class, 'update'])->name('manager.warehouse.stands.update');
            Route::get('/edit/{uid}', [WarehouseLocationsController::class, 'edit'])->name('manager.warehouse.stands.edit');
            Route::get('/view/{uid}', [WarehouseLocationsController::class, 'view'])->name('manager.warehouse.stands.view');
            Route::get('/destroy/{uid}', [WarehouseLocationsController::class, 'destroy'])->name('manager.warehouse.stands.destroy');
        });

        // Inventory Slots Routes
        Route::group(['prefix' => 'slots'], function () {
            Route::get('/', [WarehouseInventorySlotsController::class, 'index'])->name('slots');
            Route::get('/create', [WarehouseInventorySlotsController::class, 'create'])->name('manager.warehouse.slots.create');
            Route::post('/store', [WarehouseInventorySlotsController::class, 'store'])->name('manager.warehouse.slots.store');
            Route::post('/update', [WarehouseInventorySlotsController::class, 'update'])->name('manager.warehouse.slots.update');
            Route::get('/edit/{uid}', [WarehouseInventorySlotsController::class, 'edit'])->name('manager.warehouse.slots.edit');
            Route::get('/view/{uid}', [WarehouseInventorySlotsController::class, 'view'])->name('manager.warehouse.slots.view');
            Route::get('/destroy/{uid}', [WarehouseInventorySlotsController::class, 'destroy'])->name('manager.warehouse.slots.destroy');

            // Inventory operations
            Route::post('/{uid}/add-quantity', [WarehouseInventorySlotsController::class, 'addQuantity'])->name('manager.warehouse.slots.add-quantity');
            Route::post('/{uid}/subtract-quantity', [WarehouseInventorySlotsController::class, 'subtractQuantity'])->name('manager.warehouse.slots.subtract-quantity');
            Route::post('/{uid}/add-weight', [WarehouseInventorySlotsController::class, 'addWeight'])->name('manager.warehouse.slots.add-weight');
            Route::post('/{uid}/clear', [WarehouseInventorySlotsController::class, 'clear'])->name('manager.warehouse.slots.clear');
        });
        // ===== WAREHOUSE CRUD =====
        Route::group(['prefix' => 'warehouses'], function () {

            Route::get('/', [WarehouseController::class, 'index'])->name('manager.warehouse.index');
            Route::get('/create', [WarehouseController::class, 'create'])->name('manager.warehouse.create');
            Route::post('/store', [WarehouseController::class, 'store'])->name('manager.warehouse.store');
            Route::get('/{uid}', [WarehouseController::class, 'view'])->name('manager.warehouse.view');
            Route::get('/{uid}/edit', [WarehouseController::class, 'edit'])->name('manager.warehouse.edit');
            Route::post('/{uid}/update', [WarehouseController::class, 'update'])->name('manager.warehouse.update');
            Route::get('/{uid}/destroy', [WarehouseController::class, 'destroy'])->name('manager.warehouse.destroy');
            Route::get('/{uid}/summary', [WarehouseController::class, 'getSummary'])->name('manager.warehouse.summary');

            // ===== WAREHOUSE RESOURCES (scoped to warehouse) =====
            Route::group(['prefix' => '{warehouse_uid}'], function () {

                // Dashboard del warehouse
                Route::get('/dashboard', [WarehouseDashboardController::class, 'dashboard'])->name('manager.warehouse.dashboard.index');
                Route::get('/api/floors', [WarehouseDashboardController::class, 'getFloors'])->name('manager.warehouse.api.floors');

                // Mapa del warehouse
                Route::get('/map', [WarehouseMapController::class, 'map'])->name('manager.warehouse.map');
                Route::get('/api/layout-spec', [WarehouseMapController::class, 'getLayoutSpec'])->name('manager.warehouse.api.layout');
                Route::get('/api/config', [WarehouseMapController::class, 'getWarehouseConfig'])->name('manager.warehouse.api.config');
                Route::get('/api/slot/{slot_uid}', [WarehouseMapController::class, 'getSlotDetails'])->name('manager.warehouse.api.slot');

                // Old map API endpoints (legacy support for warehouse map views)
                Route::get('/map/locations', [WarehouseLocationsController::class, 'getByWarehouse'])->name('manager.warehouse.map.locations');
                Route::post('/map/create', [WarehouseLocationsController::class, 'store'])->name('manager.warehouse.map.create');
                Route::post('/map/update', [WarehouseLocationsController::class, 'update'])->name('manager.warehouse.map.update');
                Route::delete('/map/delete/{location_uid}', [WarehouseLocationsController::class, 'destroy'])->name('manager.warehouse.map.delete');

                // Location sections routes (legacy support with alias)
                Route::get('/locations/{location_uid}/sections', [WarehouseLocationSectionsController::class, 'index'])->name('manager.warehouse.locations.sections.index');

                // Visual editing endpoints (Opción 2)
                Route::put('/location/{location_uid}/visual-config', [WarehouseMapController::class, 'updateVisualConfig'])->name('manager.warehouse.location.visual.update');
                Route::post('/location/{location_uid}/reset-visual', [WarehouseMapController::class, 'resetVisualConfig'])->name('manager.warehouse.location.visual.reset');

                // Historial del warehouse
                Route::group(['prefix' => 'history'], function () {
                    Route::get('/', [WarehouseHistoryController::class, 'index'])->name('manager.warehouse.history');
                    Route::get('/{uid}', [WarehouseHistoryController::class, 'view'])->name('manager.warehouse.history.view');
                    Route::get('/{uid}/edit', [WarehouseHistoryController::class, 'edit'])->name('manager.warehouse.history.edit');
                    Route::post('/update', [WarehouseHistoryController::class, 'update'])->name('manager.warehouse.history.update');
                    Route::get('/api/slot/{slot_uid}', [WarehouseHistoryController::class, 'getSlotHistory'])->name('manager.warehouse.history.api.slot');
                    Route::post('/api/filter', [WarehouseHistoryController::class, 'filterByDateRange'])->name('manager.warehouse.history.api.filter');
                    Route::get('/api/statistics', [WarehouseHistoryController::class, 'getStatistics'])->name('manager.warehouse.history.api.statistics');
                });

                // Reportes del warehouse
                Route::group(['prefix' => 'reports'], function () {
                    Route::get('/', [WarehouseReportsController::class, 'report'])->name('manager.warehouse.reports');
                    Route::post('/inventory', [WarehouseReportsController::class, 'generateInventory'])->name('manager.warehouse.reports.inventory');
                    Route::post('/movements', [WarehouseReportsController::class, 'generateMovements'])->name('manager.warehouse.reports.movements');
                    Route::post('/occupancy', [WarehouseReportsController::class, 'generateOccupancy'])->name('manager.warehouse.reports.occupancy');
                    Route::post('/capacity', [WarehouseReportsController::class, 'generateCapacity'])->name('manager.warehouse.reports.capacity');
                });

                // Pisos del warehouse
                Route::group(['prefix' => 'floors'], function () {

                    Route::get('/', [WarehouseFloorsController::class, 'index'])->name('manager.warehouse.floors');
                    Route::get('/create', [WarehouseFloorsController::class, 'create'])->name('manager.warehouse.floors.create');
                    Route::post('/store', [WarehouseFloorsController::class, 'store'])->name('manager.warehouse.floors.store');
                    Route::get('/{floor_uid}', [WarehouseFloorsController::class, 'view'])->name('manager.warehouse.floors.view');
                    Route::get('/{floor_uid}/edit', [WarehouseFloorsController::class, 'edit'])->name('manager.warehouse.floors.edit');
                    Route::post('/update', [WarehouseFloorsController::class, 'update'])->name('manager.warehouse.floors.update');
                    Route::get('/{floor_uid}/destroy', [WarehouseFloorsController::class, 'destroy'])->name('manager.warehouse.floors.destroy');

                    // Locaciones dentro del piso
                    Route::group(['prefix' => '{floor_uid}/locations'], function () {
                        Route::get('/', [WarehouseLocationsController::class, 'index'])->name('manager.warehouse.locations');
                        Route::get('/create', [WarehouseLocationsController::class, 'create'])->name('manager.warehouse.locations.create');
                        Route::post('/store', [WarehouseLocationsController::class, 'store'])->name('manager.warehouse.locations.store');
                        Route::get('/{location_uid}', [WarehouseLocationsController::class, 'view'])->name('manager.warehouse.locations.view');
                        Route::get('/{location_uid}/edit', [WarehouseLocationsController::class, 'edit'])->name('manager.warehouse.locations.edit');
                        Route::post('/update', [WarehouseLocationsController::class, 'update'])->name('manager.warehouse.locations.update');
                        Route::get('/{location_uid}/destroy', [WarehouseLocationsController::class, 'destroy'])->name('manager.warehouse.locations.destroy');
                        Route::get('/api/warehouse', [WarehouseLocationsController::class, 'getByWarehouse'])->name('manager.warehouse.locations.api.warehouse');
                        Route::get('/api/barcode/{barcode}', [WarehouseLocationsController::class, 'getByBarcode'])->name('manager.warehouse.locations.api.barcode');
                        Route::get('/{location_uid}/api/details', [WarehouseLocationsController::class, 'getLocationDetails'])->name('manager.warehouse.locations.api.details');
                        Route::get('/{location_uid}/sections/{section_uid}/api/details', [WarehouseLocationsController::class, 'getSectionDetails'])->name('manager.warehouse.sections.api.details');

                        // Print barcodes routes
                        Route::get('/print-all-barcodes', [WarehouseLocationsController::class, 'printAllBarcodes'])->name('manager.warehouse.locations.print-all');
                        Route::get('/{location_uid}/print-barcodes', [WarehouseLocationsController::class, 'printBarcodes'])->name('manager.warehouse.locations.print');

                        // Transferir múltiples ubicaciones (bulk)
                        Route::get('/transfer/bulk', [WarehouseLocationsController::class, 'transferBulkForm'])->name('manager.warehouse.locations.transfer.bulk');
                        Route::post('/transfer/bulk', [WarehouseLocationsController::class, 'transferBulkSubmit'])->name('manager.warehouse.locations.transfer.bulk.store');

                        // Transferir ubicación individual
                        Route::group(['prefix' => '{location_uid}/transfer'], function () {
                            Route::get('/', [WarehouseLocationsController::class, 'transfer'])->name('manager.warehouse.locations.transfer');
                            Route::post('/store', [WarehouseLocationsController::class, 'transferSubmit'])->name('manager.warehouse.locations.transfer.store');
                            Route::get('/api/available-floors', [WarehouseLocationsController::class, 'getAvailableFloorsForTransfer'])->name('manager.warehouse.locations.transfer.api.available-floors');
                        });

                        // Secciones dentro de la locación
                        Route::group(['prefix' => '{location_uid}/sections'], function () {
                            Route::get('/', [WarehouseLocationSectionsController::class, 'index'])->name('manager.warehouse.sections');
                            Route::get('/create', [WarehouseLocationSectionsController::class, 'create'])->name('manager.warehouse.sections.create');
                            Route::post('/store', [WarehouseLocationSectionsController::class, 'store'])->name('manager.warehouse.sections.store');
                            Route::get('/{section_uid}', [WarehouseLocationSectionsController::class, 'view'])->name('manager.warehouse.section.view');
                            Route::get('/{section_uid}/edit', [WarehouseLocationSectionsController::class, 'edit'])->name('manager.warehouse.section.edit');
                            Route::post('/update', [WarehouseLocationSectionsController::class, 'update'])->name('manager.warehouse.section.update');
                            Route::get('/{section_uid}/destroy', [WarehouseLocationSectionsController::class, 'destroy'])->name('manager.warehouse.section.destroy');
                            Route::get('/api/list/{location_id}', [WarehouseLocationSectionsController::class, 'getSectionsList'])->name('manager.warehouse.sections.api.list');
                            Route::post('/quick-create', [WarehouseLocationSectionsController::class, 'quickCreate'])->name('manager.warehouse.sections.quick-create');

                            // Inventory Slots dentro de la sección
                            Route::group(['prefix' => '{section_uid}/slots'], function () {
                                Route::get('/', [WarehouseInventorySlotsController::class, 'index'])->name('manager.warehouse.section.slots');
                                Route::get('/create', [WarehouseInventorySlotsController::class, 'create'])->name('manager.warehouse.section.slots.create');
                                Route::post('/store', [WarehouseInventorySlotsController::class, 'store'])->name('manager.warehouse.section.slots.store');
                                Route::get('/{slot_uid}', [WarehouseInventorySlotsController::class, 'view'])->name('manager.warehouse.section.slots.view');
                                Route::get('/{slot_uid}/edit', [WarehouseInventorySlotsController::class, 'edit'])->name('manager.warehouse.section.slots.edit');
                                Route::post('/update', [WarehouseInventorySlotsController::class, 'update'])->name('manager.warehouse.section.slots.update');
                                Route::get('/{slot_uid}/destroy', [WarehouseInventorySlotsController::class, 'destroy'])->name('manager.warehouse.section.slots.destroy');

                                // Inventory operations
                                Route::post('/{slot_uid}/add-quantity', [WarehouseInventorySlotsController::class, 'addQuantity'])->name('manager.warehouse.section.slots.add-quantity');
                                Route::post('/{slot_uid}/subtract-quantity', [WarehouseInventorySlotsController::class, 'subtractQuantity'])->name('manager.warehouse.section.slots.subtract-quantity');
                                Route::post('/{slot_uid}/clear', [WarehouseInventorySlotsController::class, 'clear'])->name('manager.warehouse.section.slots.clear');
                                Route::post('/{slot_uid}/move-to', [WarehouseInventorySlotsController::class, 'moveTo'])->name('manager.warehouse.section.slots.move-to');
                            });
                        });
                    });
                });
            });
        });

        // ===== WAREHOUSE LOCATION STYLES (GLOBAL, NOT WAREHOUSE-SPECIFIC) =====
        Route::group(['prefix' => 'styles'], function () {
            Route::get('/', [WarehouseLocationStylesController::class, 'index'])->name('manager.warehouse.styles');
            Route::get('/create', [WarehouseLocationStylesController::class, 'create'])->name('manager.warehouse.styles.create');
            Route::post('/store', [WarehouseLocationStylesController::class, 'store'])->name('manager.warehouse.styles.store');
            Route::get('/{style_uid}', [WarehouseLocationStylesController::class, 'view'])->name('manager.warehouse.styles.view');
            Route::get('/{style_uid}/edit', [WarehouseLocationStylesController::class, 'edit'])->name('manager.warehouse.styles.edit');
            Route::post('/update', [WarehouseLocationStylesController::class, 'update'])->name('manager.warehouse.styles.update');
            Route::get('/{style_uid}/destroy', [WarehouseLocationStylesController::class, 'destroy'])->name('manager.warehouse.styles.destroy');
        });

        // ===== WAREHOUSE API ROUTES =====
        Route::get('/api/styles', [WarehouseLocationStylesController::class, 'apiGetAllStyles'])->name('manager.warehouse.api.styles.all');
        Route::get('/api/styles/{style_id}', [WarehouseLocationsController::class, 'getStyleDetails'])->name('manager.warehouse.api.style.details');

    });

    Route::group(['prefix' => 'helpdesk'], function () {

        // Main Helpdesk Index
        Route::get('/', [HelpdeskCustomersController::class, 'index'])->name('manager.helpdesk');

        // Customers
        Route::get('/customers', [HelpdeskCustomersController::class, 'index'])->name('manager.helpdesk.customers.index');
        Route::get('/customers/create', [HelpdeskCustomersController::class, 'create'])->name('manager.helpdesk.customers.create');
        Route::post('/customers', [HelpdeskCustomersController::class, 'store'])->name('manager.helpdesk.customers.store');
        Route::get('/customers/{customer}', [HelpdeskCustomersController::class, 'show'])->name('manager.helpdesk.customers.show');
        Route::get('/customers/{customer}/edit', [HelpdeskCustomersController::class, 'edit'])->name('manager.helpdesk.customers.edit');
        Route::put('/customers/{customer}', [HelpdeskCustomersController::class, 'update'])->name('manager.helpdesk.customers.update');
        Route::delete('/customers/{customer}', [HelpdeskCustomersController::class, 'destroy'])->name('manager.helpdesk.customers.destroy');
        Route::post('/customers/{customer}/restore', [HelpdeskCustomersController::class, 'restore'])->name('manager.helpdesk.customers.restore');
        Route::delete('/customers/{customer}/force-delete', [HelpdeskCustomersController::class, 'forceDelete'])->name('manager.helpdesk.customers.forceDelete');
        Route::post('/customers/{customer}/ban', [HelpdeskCustomersController::class, 'ban'])->name('manager.helpdesk.customers.ban');
        Route::post('/customers/{customer}/unban', [HelpdeskCustomersController::class, 'unban'])->name('manager.helpdesk.customers.unban');

        // Conversations
        Route::get('/conversations', [HelpdeskConversationsController::class, 'index'])->name('manager.helpdesk.conversations.index');
        Route::get('/conversations/create', [HelpdeskConversationsController::class, 'create'])->name('manager.helpdesk.conversations.create');
        Route::post('/conversations', [HelpdeskConversationsController::class, 'store'])->name('manager.helpdesk.conversations.store');
        Route::get('/conversations/{conversation}', [HelpdeskConversationsController::class, 'show'])->name('manager.helpdesk.conversations.show');
        Route::get('/conversations/{conversation}/edit', [HelpdeskConversationsController::class, 'edit'])->name('manager.helpdesk.conversations.edit');
        Route::put('/conversations/{conversation}', [HelpdeskConversationsController::class, 'update'])->name('manager.helpdesk.conversations.update');
        Route::delete('/conversations/{conversation}', [HelpdeskConversationsController::class, 'destroy'])->name('manager.helpdesk.conversations.destroy');
        Route::post('/conversations/{conversation}/restore', [HelpdeskConversationsController::class, 'restore'])->name('manager.helpdesk.conversations.restore');
        Route::delete('/conversations/{conversation}/force-delete', [HelpdeskConversationsController::class, 'forceDelete'])->name('manager.helpdesk.conversations.forceDelete');
        Route::post('/conversations/{conversation}/close', [HelpdeskConversationsController::class, 'close'])->name('manager.helpdesk.conversations.close');
        Route::post('/conversations/{conversation}/reopen', [HelpdeskConversationsController::class, 'reopen'])->name('manager.helpdesk.conversations.reopen');
        Route::post('/conversations/{conversation}/archive', [HelpdeskConversationsController::class, 'archive'])->name('manager.helpdesk.conversations.archive');
        Route::post('/conversations/{conversation}/unarchive', [HelpdeskConversationsController::class, 'unarchive'])->name('manager.helpdesk.conversations.unarchive');
        Route::post('/conversations/{conversation}/messages', [HelpdeskConversationsController::class, 'storeMessage'])->name('manager.helpdesk.conversations.messages.store');

        // Tickets
        Route::get('/tickets', [HelpdeskTicketsController::class, 'index'])->name('manager.helpdesk.tickets.index');
        Route::get('/tickets/create', [HelpdeskTicketsController::class, 'create'])->name('manager.helpdesk.tickets.create');
        Route::post('/tickets', [HelpdeskTicketsController::class, 'store'])->name('manager.helpdesk.tickets.store');
        Route::get('/tickets/{ticket}', [HelpdeskTicketsController::class, 'show'])->name('manager.helpdesk.tickets.show');
        Route::get('/tickets/{ticket}/edit', [HelpdeskTicketsController::class, 'edit'])->name('manager.helpdesk.tickets.edit');
        Route::put('/tickets/{ticket}', [HelpdeskTicketsController::class, 'update'])->name('manager.helpdesk.tickets.update');
        Route::delete('/tickets/{ticket}', [HelpdeskTicketsController::class, 'destroy'])->name('manager.helpdesk.tickets.destroy');
        Route::post('/tickets/{ticket}/close', [HelpdeskTicketsController::class, 'close'])->name('manager.helpdesk.tickets.close');
        Route::post('/tickets/{ticket}/resolve', [HelpdeskTicketsController::class, 'resolve'])->name('manager.helpdesk.tickets.resolve');
        Route::post('/tickets/{ticket}/reopen', [HelpdeskTicketsController::class, 'reopen'])->name('manager.helpdesk.tickets.reopen');
        Route::post('/tickets/{ticket}/archive', [HelpdeskTicketsController::class, 'archive'])->name('manager.helpdesk.tickets.archive');
        Route::post('/tickets/{ticket}/messages', [HelpdeskTicketsController::class, 'storeMessage'])->name('manager.helpdesk.tickets.messages.store');

        // Ticket Comments
        Route::get('/tickets/{ticket}/comments', [TicketCommentsController::class, 'index'])->name('manager.helpdesk.tickets.comments.index');
        Route::post('/tickets/{ticket}/comments', [TicketCommentsController::class, 'store'])->name('manager.helpdesk.tickets.comments.store');
        Route::get('/tickets/{ticket}/comments/{comment}', [TicketCommentsController::class, 'show'])->name('manager.helpdesk.tickets.comments.show');
        Route::put('/tickets/{ticket}/comments/{comment}', [TicketCommentsController::class, 'update'])->name('manager.helpdesk.tickets.comments.update');
        Route::delete('/tickets/{ticket}/comments/{comment}', [TicketCommentsController::class, 'destroy'])->name('manager.helpdesk.tickets.comments.destroy');
        Route::post('/tickets/{ticket}/comments/{comment}/restore', [TicketCommentsController::class, 'restore'])->name('manager.helpdesk.tickets.comments.restore');

        // Ticket Notes
        Route::get('/tickets/{ticket}/notes', [TicketNotesController::class, 'index'])->name('manager.helpdesk.tickets.notes.index');
        Route::post('/tickets/{ticket}/notes', [TicketNotesController::class, 'store'])->name('manager.helpdesk.tickets.notes.store');
        Route::get('/tickets/{ticket}/notes/{note}', [TicketNotesController::class, 'show'])->name('manager.helpdesk.tickets.notes.show');
        Route::put('/tickets/{ticket}/notes/{note}', [TicketNotesController::class, 'update'])->name('manager.helpdesk.tickets.notes.update');
        Route::delete('/tickets/{ticket}/notes/{note}', [TicketNotesController::class, 'destroy'])->name('manager.helpdesk.tickets.notes.destroy');
        Route::post('/tickets/{ticket}/notes/{note}/pin', [TicketNotesController::class, 'pin'])->name('manager.helpdesk.tickets.notes.pin');
        Route::post('/tickets/{ticket}/notes/{note}/color', [TicketNotesController::class, 'changeColor'])->name('manager.helpdesk.tickets.notes.color');
        Route::post('/tickets/{ticket}/notes/{note}/restore', [TicketNotesController::class, 'restore'])->name('manager.helpdesk.tickets.notes.restore');

        // Campaigns
        Route::get('/campaigns', [HelpdeskCampaignsController::class, 'index'])->name('manager.helpdesk.campaigns.index');
        Route::get('/campaigns/templates', [HelpdeskCampaignsController::class, 'templates'])->name('manager.helpdesk.campaigns.templates');
        Route::get('/campaigns/create', [HelpdeskCampaignsController::class, 'create'])->name('manager.helpdesk.campaigns.create');
        Route::post('/campaigns', [HelpdeskCampaignsController::class, 'store'])->name('manager.helpdesk.campaigns.store');
        Route::get('/campaigns/{campaign}', [HelpdeskCampaignsController::class, 'show'])->name('manager.helpdesk.campaigns.show');
        Route::get('/campaigns/{campaign}/edit', [HelpdeskCampaignsController::class, 'edit'])->name('manager.helpdesk.campaigns.edit');
        Route::put('/campaigns/{campaign}', [HelpdeskCampaignsController::class, 'update'])->name('manager.helpdesk.campaigns.update');
        Route::delete('/campaigns/{campaign}', [HelpdeskCampaignsController::class, 'destroy'])->name('manager.helpdesk.campaigns.destroy');
        Route::post('/campaigns/{campaign}/publish', [HelpdeskCampaignsController::class, 'publish'])->name('manager.helpdesk.campaigns.publish');
        Route::post('/campaigns/{campaign}/pause', [HelpdeskCampaignsController::class, 'pause'])->name('manager.helpdesk.campaigns.pause');
        Route::post('/campaigns/{campaign}/resume', [HelpdeskCampaignsController::class, 'resume'])->name('manager.helpdesk.campaigns.resume');
        Route::post('/campaigns/{campaign}/end', [HelpdeskCampaignsController::class, 'end'])->name('manager.helpdesk.campaigns.end');
        Route::get('/campaigns/{campaign}/statistics', [HelpdeskCampaignsController::class, 'statistics'])->name('manager.helpdesk.campaigns.statistics');
        Route::post('/campaigns/{campaign}/duplicate', [HelpdeskCampaignsController::class, 'duplicate'])->name('manager.helpdesk.campaigns.duplicate');

        // AI Agent
        Route::prefix('ai')->group(function () {
            // Settings
            Route::get('settings', [AiAgentSettingsController::class, 'index'])->name('manager.helpdesk.ai.settings');
            Route::put('settings', [AiAgentSettingsController::class, 'update'])->name('manager.helpdesk.ai.settings.update');
            Route::post('settings/test-connection', [AiAgentSettingsController::class, 'testConnection'])->name('manager.helpdesk.ai.settings.test');
            Route::post('settings/get-models', [AiAgentSettingsController::class, 'getModels'])->name('manager.helpdesk.ai.settings.get-models');
            Route::get('settings/statistics', [AiAgentSettingsController::class, 'statistics'])->name('manager.helpdesk.ai.settings.statistics');

            // Tags
            Route::get('tags', [AiAgentSettingsController::class, 'tagsIndex'])->name('manager.helpdesk.ai.tags.index');
            Route::post('tags', [AiAgentSettingsController::class, 'tagsStore'])->name('manager.helpdesk.ai.tags.store');
            Route::put('tags/{tag}', [AiAgentSettingsController::class, 'tagsUpdate'])->name('manager.helpdesk.ai.tags.update');
            Route::delete('tags/{tag}', [AiAgentSettingsController::class, 'tagsDestroy'])->name('manager.helpdesk.ai.tags.destroy');
            Route::post('tags/{tag}/toggle', [AiAgentSettingsController::class, 'tagsToggle'])->name('manager.helpdesk.ai.tags.toggle');

            // Tools
            Route::get('tools', [AiAgentSettingsController::class, 'toolsIndex'])->name('manager.helpdesk.ai.tools.index');
            Route::post('tools', [AiAgentSettingsController::class, 'toolsStore'])->name('manager.helpdesk.ai.tools.store');
            Route::put('tools/{tool}', [AiAgentSettingsController::class, 'toolsUpdate'])->name('manager.helpdesk.ai.tools.update');
            Route::delete('tools/{tool}', [AiAgentSettingsController::class, 'toolsDestroy'])->name('manager.helpdesk.ai.tools.destroy');
            Route::post('tools/{tool}/toggle', [AiAgentSettingsController::class, 'toolsToggle'])->name('manager.helpdesk.ai.tools.toggle');

            // Knowledge Base
            Route::get('knowledge', [AiAgentSettingsController::class, 'knowledgeIndex'])->name('manager.helpdesk.ai.knowledge.index');
            Route::post('knowledge', [AiAgentSettingsController::class, 'knowledgeStore'])->name('manager.helpdesk.ai.knowledge.store');
            Route::put('knowledge/{knowledge}', [AiAgentSettingsController::class, 'knowledgeUpdate'])->name('manager.helpdesk.ai.knowledge.update');
            Route::delete('knowledge/{knowledge}', [AiAgentSettingsController::class, 'knowledgeDestroy'])->name('manager.helpdesk.ai.knowledge.destroy');
            Route::post('knowledge/{knowledge}/toggle', [AiAgentSettingsController::class, 'knowledgeToggle'])->name('manager.helpdesk.ai.knowledge.toggle');
            Route::post('knowledge/{knowledge}/generate-embedding', [AiAgentSettingsController::class, 'knowledgeGenerateEmbedding'])->name('manager.helpdesk.ai.knowledge.generate-embedding');

            // Flows
            Route::get('/', [AiAgentFlowsController::class, 'index'])->name('manager.helpdesk.ai.flows.index');
            Route::get('/create', [AiAgentFlowsController::class, 'create'])->name('manager.helpdesk.ai.flows.create');
            Route::post('/', [AiAgentFlowsController::class, 'store'])->name('manager.helpdesk.ai.flows.store');
            Route::get('/{flow}', [AiAgentFlowsController::class, 'show'])->name('manager.helpdesk.ai.flows.show');
            Route::get('/{flow}/edit', [AiAgentFlowsController::class, 'edit'])->name('manager.helpdesk.ai.flows.edit');
            Route::put('/{flow}', [AiAgentFlowsController::class, 'update'])->name('manager.helpdesk.ai.flows.update');
            Route::delete('/{flow}', [AiAgentFlowsController::class, 'destroy'])->name('manager.helpdesk.ai.flows.destroy');
            Route::post('flows/{flow}/publish', [AiAgentFlowsController::class, 'publish'])->name('manager.helpdesk.ai.flows.publish');
            Route::post('flows/{flow}/archive', [AiAgentFlowsController::class, 'archive'])->name('manager.helpdesk.ai.flows.archive');
            Route::post('flows/{flow}/duplicate', [AiAgentFlowsController::class, 'duplicate'])->name('manager.helpdesk.ai.flows.duplicate');

            // Flow Nodes
            Route::post('flows/{flow}/nodes', [AiAgentFlowsController::class, 'storeNode'])->name('manager.helpdesk.ai.flows.nodes.store');
            Route::put('flows/{flow}/nodes/{node}', [AiAgentFlowsController::class, 'updateNode'])->name('manager.helpdesk.ai.flows.nodes.update');
            Route::delete('flows/{flow}/nodes/{node}', [AiAgentFlowsController::class, 'deleteNode'])->name('manager.helpdesk.ai.flows.nodes.delete');

            // Flow Structure
            Route::put('flows/{flow}/structure', [AiAgentFlowsController::class, 'updateStructure'])->name('manager.helpdesk.ai.flows.structure');
        });

        // Settings
        Route::prefix('settings')->name('manager.helpdesk.settings.')->group(function () {
            // Tickets Settings
            Route::get('tickets', [\App\Http\Controllers\Managers\Helpdesk\Settings\SettingsController::class, 'ticketsIndex'])->name('tickets');
            Route::put('tickets', [\App\Http\Controllers\Managers\Helpdesk\Settings\SettingsController::class, 'ticketsUpdate'])->name('tickets.update');

            // LiveChat Settings (New Helpdesk Widget)
            Route::get('livechat', [\App\Http\Controllers\Managers\Helpdesk\Settings\SettingsController::class, 'livechatIndex'])->name('livechat');
            Route::put('livechat', [\App\Http\Controllers\Managers\Helpdesk\Settings\SettingsController::class, 'livechatUpdate'])->name('livechat.update');

            // AI Settings
            Route::get('ai', [\App\Http\Controllers\Managers\Helpdesk\Settings\SettingsController::class, 'aiIndex'])->name('ai');
            Route::put('ai', [\App\Http\Controllers\Managers\Helpdesk\Settings\SettingsController::class, 'aiUpdate'])->name('ai.update');

            // Uploading Settings
            Route::get('uploading', [\App\Http\Controllers\Managers\Helpdesk\Settings\SettingsController::class, 'uploadingIndex'])->name('uploading');
            Route::put('uploading', [\App\Http\Controllers\Managers\Helpdesk\Settings\SettingsController::class, 'uploadingUpdate'])->name('uploading.update');

            // Customers Settings (link to existing customers routes)
            Route::get('customers', [HelpdeskCustomersController::class, 'index'])->name('customers');

            // Tickets Settings
            Route::prefix('tickets')->name('tickets.')->group(function () {
                // Categories
                Route::prefix('categories')->name('categories.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCategoriesController::class, 'index'])->name('index');
                    Route::get('create', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCategoriesController::class, 'create'])->name('create');
                    Route::post('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCategoriesController::class, 'store'])->name('store');
                    Route::get('{category}/edit', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCategoriesController::class, 'edit'])->name('edit');
                    Route::put('{category}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCategoriesController::class, 'update'])->name('update');
                    Route::patch('{category}/toggle', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCategoriesController::class, 'toggle'])->name('toggle');
                    Route::delete('{category}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCategoriesController::class, 'destroy'])->name('destroy');
                    Route::post('reorder', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCategoriesController::class, 'reorder'])->name('reorder');
                });

                // Groups
                Route::prefix('groups')->name('groups.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketGroupsController::class, 'index'])->name('index');
                    Route::get('create', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketGroupsController::class, 'create'])->name('create');
                    Route::post('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketGroupsController::class, 'store'])->name('store');
                    Route::get('{group}/edit', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketGroupsController::class, 'edit'])->name('edit');
                    Route::put('{group}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketGroupsController::class, 'update'])->name('update');
                    Route::patch('{group}/toggle', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketGroupsController::class, 'toggle'])->name('toggle');
                    Route::delete('{group}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketGroupsController::class, 'destroy'])->name('destroy');
                    Route::post('reorder', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketGroupsController::class, 'reorder'])->name('reorder');
                });

                // Canned Replies
                Route::prefix('canned-replies')->name('canned-replies.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCannedRepliesController::class, 'index'])->name('index');
                    Route::get('create', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCannedRepliesController::class, 'create'])->name('create');
                    Route::post('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCannedRepliesController::class, 'store'])->name('store');
                    Route::get('{reply}/edit', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCannedRepliesController::class, 'edit'])->name('edit');
                    Route::put('{reply}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCannedRepliesController::class, 'update'])->name('update');
                    Route::delete('{reply}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketCannedRepliesController::class, 'destroy'])->name('destroy');
                });

                // Statuses
                Route::prefix('statuses')->name('statuses.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketStatusesController::class, 'index'])->name('index');
                    Route::get('create', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketStatusesController::class, 'create'])->name('create');
                    Route::post('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketStatusesController::class, 'store'])->name('store');
                    Route::get('{status}/edit', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketStatusesController::class, 'edit'])->name('edit');
                    Route::put('{status}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketStatusesController::class, 'update'])->name('update');
                    Route::delete('{status}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketStatusesController::class, 'destroy'])->name('destroy');
                    Route::post('reorder', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketStatusesController::class, 'reorder'])->name('reorder');
                });

                // SLA Policies
                Route::prefix('sla-policies')->name('sla-policies.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketSlaPoliciesController::class, 'index'])->name('index');
                    Route::get('create', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketSlaPoliciesController::class, 'create'])->name('create');
                    Route::post('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketSlaPoliciesController::class, 'store'])->name('store');
                    Route::get('{policy}/edit', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketSlaPoliciesController::class, 'edit'])->name('edit');
                    Route::put('{policy}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketSlaPoliciesController::class, 'update'])->name('update');
                    Route::patch('{policy}/toggle', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketSlaPoliciesController::class, 'toggle'])->name('toggle');
                    Route::delete('{policy}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketSlaPoliciesController::class, 'destroy'])->name('destroy');
                });

                // Views
                Route::prefix('views')->name('views.')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketViewsController::class, 'index'])->name('index');
                    Route::get('create', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketViewsController::class, 'create'])->name('create');
                    Route::post('/', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketViewsController::class, 'store'])->name('store');
                    Route::get('{view}/edit', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketViewsController::class, 'edit'])->name('edit');
                    Route::put('{view}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketViewsController::class, 'update'])->name('update');
                    Route::delete('{view}', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketViewsController::class, 'destroy'])->name('destroy');
                    Route::post('reorder', [\App\Http\Controllers\Managers\Helpdesk\Settings\TicketViewsController::class, 'reorder'])->name('reorder');
                });

                // Team Settings
                Route::prefix('team')->name('team.')->group(function () {
                    // Members
                    Route::get('members', [TeamController::class, 'membersIndex'])->name('members');
                    Route::get('members/{id}/edit', [TeamController::class, 'memberEdit'])->name('member.edit');
                    Route::put('members/{id}', [TeamController::class, 'memberUpdate'])->name('member.update');

                    // Groups
                    Route::get('groups', [TeamController::class, 'groupsIndex'])->name('groups');
                    Route::get('groups/create', [TeamController::class, 'groupCreate'])->name('group.create');
                    Route::post('groups', [TeamController::class, 'groupStore'])->name('group.store');
                    Route::get('groups/{id}/edit', [TeamController::class, 'groupEdit'])->name('group.edit');
                    Route::put('groups/{id}', [TeamController::class, 'groupUpdate'])->name('group.update');
                    Route::delete('groups/{id}', [TeamController::class, 'groupDestroy'])->name('group.destroy');
                });

                // Attributes Settings
                Route::prefix('attributes')->name('attributes.')->group(function () {
                    Route::get('/', [AttributesController::class, 'index'])->name('index');
                    Route::get('create', [AttributesController::class, 'create'])->name('create');
                    Route::post('/', [AttributesController::class, 'store'])->name('store');
                    Route::get('{id}/edit', [AttributesController::class, 'edit'])->name('edit');
                    Route::put('{id}', [AttributesController::class, 'update'])->name('update');
                    Route::delete('{id}', [AttributesController::class, 'destroy'])->name('destroy');
                    Route::patch('{id}/toggle', [AttributesController::class, 'toggleActive'])->name('toggle');
                });

                // Tags Settings
                Route::prefix('tags')->name('tags.')->group(function () {
                    Route::get('/', [TagsController::class, 'index'])->name('index');
                    Route::get('create', [TagsController::class, 'create'])->name('create');
                    Route::post('/', [TagsController::class, 'store'])->name('store');
                    Route::get('{tag}/edit', [TagsController::class, 'edit'])->name('edit');
                    Route::put('{tag}', [TagsController::class, 'update'])->name('update');
                    Route::delete('{tag}', [TagsController::class, 'destroy'])->name('destroy');
                });

                // Conversation Statuses Settings
                Route::prefix('statuses')->name('statuses.')->group(function () {
                    Route::get('/', [StatusesController::class, 'index'])->name('index');
                    Route::get('create', [StatusesController::class, 'create'])->name('create');
                    Route::post('/', [StatusesController::class, 'store'])->name('store');
                    Route::get('{status}/edit', [StatusesController::class, 'edit'])->name('edit');
                    Route::put('{status}', [StatusesController::class, 'update'])->name('update');
                    Route::delete('{status}', [StatusesController::class, 'destroy'])->name('destroy');
                    Route::post('{status}/toggle', [StatusesController::class, 'toggle'])->name('toggle');
                    Route::post('reorder', [StatusesController::class, 'reorder'])->name('reorder');
                });
            });
        });

        // Help Center Manager
        Route::prefix('helpcenter')->group(function () {
            // Main Index
            Route::get('/', [HelpCenterController::class, 'index'])->name('manager.helpdesk.helpcenter.index');

            // Categories
            Route::get('/categories', [HelpCenterController::class, 'index'])->name('manager.helpdesk.helpcenter.categories');
            Route::get('/categories/create', [HelpCenterController::class, 'create'])->name('manager.helpdesk.helpcenter.categories.create');
            Route::post('/categories/store', [HelpCenterController::class, 'store'])->name('manager.helpdesk.helpcenter.categories.store');
            Route::get('/categories/{id}', [HelpCenterController::class, 'showCategory'])->name('manager.helpdesk.helpcenter.categories.show');
            Route::get('/categories/edit/{id}', [HelpCenterController::class, 'edit'])->name('manager.helpdesk.helpcenter.categories.edit');
            Route::post('/categories/update', [HelpCenterController::class, 'update'])->name('manager.helpdesk.helpcenter.categories.update');
            Route::get('/categories/destroy/{id}', [HelpCenterController::class, 'destroy'])->name('manager.helpdesk.helpcenter.categories.destroy');

            // Sections
            Route::get('/sections/create', [HelpCenterController::class, 'createSection'])->name('manager.helpdesk.helpcenter.sections.create');
            Route::post('/sections/store', [HelpCenterController::class, 'storeSection'])->name('manager.helpdesk.helpcenter.sections.store');
            Route::get('/sections/{id}', [HelpCenterController::class, 'showSection'])->name('manager.helpdesk.helpcenter.sections.show');
            Route::get('/sections/{id}/edit', [HelpCenterController::class, 'editSection'])->name('manager.helpdesk.helpcenter.sections.edit');
            Route::post('/sections/update', [HelpCenterController::class, 'updateSection'])->name('manager.helpdesk.helpcenter.sections.update');
            Route::get('/sections/{id}/destroy', [HelpCenterController::class, 'destroySection'])->name('manager.helpdesk.helpcenter.sections.destroy');
            Route::get('/sections/{id}/articles/create', [HelpCenterController::class, 'createArticleInSection'])->name('manager.helpdesk.helpcenter.sections.articles.create');

            // Articles
            Route::get('/articles', [HelpCenterController::class, 'articlesIndex'])->name('manager.helpdesk.helpcenter.articles');
            Route::get('/articles/create', [HelpCenterController::class, 'createArticle'])->name('manager.helpdesk.helpcenter.articles.create');
            Route::post('/articles/store', [HelpCenterController::class, 'storeArticle'])->name('manager.helpdesk.helpcenter.articles.store');
            Route::get('/articles/edit/{id}', [HelpCenterController::class, 'editArticle'])->name('manager.helpdesk.helpcenter.articles.edit');
            Route::post('/articles/update', [HelpCenterController::class, 'updateArticle'])->name('manager.helpdesk.helpcenter.articles.update');
            Route::get('/articles/destroy/{id}', [HelpCenterController::class, 'destroyArticle'])->name('manager.helpdesk.helpcenter.articles.destroy');
        });
    });

    // Media Manager
    Route::prefix('media')->name('manager.media.')->group(function () {
        Route::get('/', [MediaManagerController::class, 'index'])->name('index');
        Route::get('/list', [MediaManagerController::class, 'getList'])->name('list');
        Route::post('/upload', [MediaManagerController::class, 'uploadFile'])->name('upload');
        Route::post('/upload-url', [MediaManagerController::class, 'uploadFromUrl'])->name('upload-url');
        Route::post('/folder/create', [MediaManagerController::class, 'createFolder'])->name('folder.create');
        Route::put('/file/{file}/rename', [MediaManagerController::class, 'renameFile'])->name('file.rename');
        Route::put('/folder/{folder}/rename', [MediaManagerController::class, 'renameFolder'])->name('folder.rename');
        Route::post('/file/{file}/copy', [MediaManagerController::class, 'copyFile'])->name('file.copy');
        Route::delete('/file/{file}', [MediaManagerController::class, 'deleteFile'])->name('file.delete');
        Route::delete('/folder/{folder}', [MediaManagerController::class, 'deleteFolder'])->name('folder.delete');
        Route::post('/file/{file}/restore', [MediaManagerController::class, 'restoreFile'])->name('file.restore');
        Route::post('/folder/{folder}/restore', [MediaManagerController::class, 'restoreFolder'])->name('folder.restore');
        Route::put('/file/{file}/move', [MediaManagerController::class, 'moveFile'])->name('file.move');
        Route::put('/folder/{folder}/move', [MediaManagerController::class, 'moveFolder'])->name('folder.move');
        Route::post('/file/{file}/toggle-favorite', [MediaManagerController::class, 'toggleFavorite'])->name('file.toggle-favorite');
        Route::delete('/trash/empty', [MediaManagerController::class, 'emptyTrash'])->name('trash.empty');
    });

});
