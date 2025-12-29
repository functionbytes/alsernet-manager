<?php

use Illuminate\Support\Facades\Route;
use Modules\Campaign\Http\Controllers\CampaignsController;
use Modules\Campaign\Http\Controllers\TemplatesController;
use Modules\Campaign\Http\Controllers\SegmentController;
use Modules\Campaign\Http\Controllers\MaillistController;
use Modules\Campaign\Http\Controllers\AutomationsController;
use Modules\Campaign\Http\Controllers\SubscribersController;
use Modules\Campaign\Http\Controllers\SubscribersListsController;
use Modules\Campaign\Http\Controllers\SubscribersConditionsController;
use Modules\Campaign\Http\Controllers\SubscribersReportController;
use Modules\Campaign\Http\Controllers\LayoutController;

/*
|--------------------------------------------------------------------------
| Campaign Module Routes - Manager Area
|--------------------------------------------------------------------------
|
| All routes for the Campaign module management interface.
| Routes are protected by 'web' and 'auth' middleware and prefixed with 'manager.'
| This file is loaded via the Campaign\Providers\RouteServiceProvider.
|
| Feature Groups:
| - Subscribers (Individual & List Management) - 25+ routes
| - Templates (Email Template Management) - 30+ routes
| - Campaigns (Email Campaign Management) - 60+ routes
| - Segments (Audience Segmentation) - 15+ routes
| - Maillists (Subscriber List Management) - 40+ routes
| - Automations (Automated Email Workflows) - 35+ routes
| - Layouts (Email Layout Templates) - 9 routes
|
| Total Routes: 171+
|
*/

// ===================================================================
// SUBSCRIBERS ROUTES - Individual subscriber management
// ===================================================================

Route::group(['prefix' => 'subscribers'], function () {

    // Individual subscriber CRUD operations
    Route::get('/', [SubscribersController::class, 'index'])->name('subscribers');
    Route::get('/create', [SubscribersController::class, 'create'])->name('subscribers.create');
    Route::post('/update', [SubscribersController::class, 'update'])->name('subscribers.update');
    Route::get('/edit/{uid}', [SubscribersController::class, 'edit'])->name('subscribers.edit');
    Route::get('/view/{uid}', [SubscribersController::class, 'view'])->name('subscribers.view');
    Route::get('/destroy/{uid}', [SubscribersController::class, 'destroy'])->name('subscribers.destroy');
    Route::get('/logs/{slack}', [SubscribersController::class, 'logs'])->name('subscribers.logs');

    // Subscriber imports management
    Route::get('/imports/create', [SubscribersController::class, 'createImport'])->name('subscribers.imports.create');
    Route::get('/imports/{import_uid}', [SubscribersController::class, 'createImports'])->name('subscribers.import');
    Route::post('/imports/{import_uid}/dispatch', [SubscribersController::class, 'dispatchImportListsJobs'])->name('subscribers.import.dispatch');
    Route::get('/imports/{job_uid}/progress', [SubscribersController::class, 'importListsProgress'])->name('subscribers.import.progress');
    Route::get('/imports/{job_uid}/log/download', [SubscribersController::class, 'downloadImportListsLog'])->name('subscribers.import.log.download');
    Route::post('/imports/{job_uid}/cancel', [SubscribersController::class, 'cancelImportLists'])->name('subscribers.import.cancel');

    // Subscriber lists CRUD operations
    Route::get('/lists', [SubscribersListsController::class, 'index'])->name('subscribers.lists');
    Route::get('/list/{uid}', [SubscribersListsController::class, 'list'])->name('subscribers.list');
    Route::get('/lists/report', [SubscribersListsController::class, 'report'])->name('subscribers.lists.report');
    Route::get('/lists/create', [SubscribersListsController::class, 'create'])->name('subscribers.lists.create');
    Route::post('/lists/update', [SubscribersListsController::class, 'update'])->name('subscribers.lists.update');
    Route::post('/lists/store', [SubscribersListsController::class, 'store'])->name('subscribers.lists.store');
    Route::get('/lists/reports', [SubscribersReportController::class, 'report'])->name('subscribers.lists.reports');
    Route::get('/lists/details/{uid}', [SubscribersListsController::class, 'details'])->name('subscribers.lists.details');
    Route::get('/lists/edit/{uid}', [SubscribersListsController::class, 'edit'])->name('subscribers.lists.edit');
    Route::get('/lists/view/{uid}', [SubscribersListsController::class, 'view'])->name('subscribers.lists.view');
    Route::get('/lists/destroy/{uid}', [SubscribersListsController::class, 'destroy'])->name('subscribers.lists.destroy');

    // Subscriber lists categories and includes management
    Route::get('/lists/categories/{uid}', [SubscribersListsController::class, 'categories'])->name('subscribers.lists.categories');
    Route::get('/lists/includes/{uid}', [SubscribersListsController::class, 'includes'])->name('subscribers.lists.includes');
    Route::post('/lists/includes/update', [SubscribersListsController::class, 'updateIncludes'])->name('subscribers.lists.includes.update');
    Route::post('/lists/categories/update', [SubscribersListsController::class, 'updateCategories'])->name('subscribers.lists.categories.update');

    // Subscriber list reports
    Route::get('/lists/report/generate', [SubscribersReportController::class, 'generate'])->name('subscribers.lists.reports.generate');

    // Subscriber conditions CRUD operations
    Route::get('/conditions', [SubscribersConditionsController::class, 'index'])->name('subscribers.conditions');
    Route::get('/conditions/create', [SubscribersConditionsController::class, 'create'])->name('subscribers.conditions.create');
    Route::post('/conditions/store', [SubscribersConditionsController::class, 'store'])->name('subscribers.conditions.store');
    Route::post('/conditions/update', [SubscribersConditionsController::class, 'update'])->name('subscribers.conditions.update');
    Route::get('/conditions/edit/{uid}', [SubscribersConditionsController::class, 'edit'])->name('subscribers.conditions.edit');
    Route::get('/conditions/view/{uid}', [SubscribersConditionsController::class, 'view'])->name('subscribers.conditions.view');
    Route::get('/conditions/destroy/{uid}', [SubscribersConditionsController::class, 'destroy'])->name('subscribers.conditions.destroy');

});

// ===================================================================
// TEMPLATES ROUTES - Email template management
// ===================================================================

Route::group(['prefix' => 'templates'], function () {

    // Template CRUD operations
    Route::get('/', [TemplatesController::class, 'index'])->name('templates');
    Route::get('/create', [TemplatesController::class, 'create'])->name('templates.create');
    Route::post('/store', [TemplatesController::class, 'store'])->name('templates.store');
    Route::post('/update', [TemplatesController::class, 'update'])->name('templates.update');
    Route::get('/edit/{uid}', [TemplatesController::class, 'edit'])->name('templates.edit');
    Route::get('/edit/{uid}', [TemplatesController::class, 'edit'])->name('templates.uid.edit');
    Route::get('/view/{uid}', [TemplatesController::class, 'view'])->name('templates.view');
    Route::get('/destroy/{uid}', [TemplatesController::class, 'destroy'])->name('templates.destroy');
    Route::patch('/update/{uid}', [TemplatesController::class, 'update'])->name('templates.uid.update');

    // Template management
    Route::get('/delete', [TemplatesController::class, 'delete'])->name('templates.delete');
    Route::post('/upload', [TemplatesController::class, 'uploadTemplate'])->name('templates.uploadTemplate');
    Route::get('/preview/{uid}', [TemplatesController::class, 'preview'])->name('templates.preview');
    Route::post('/copy/{uid}', [TemplatesController::class, 'copy'])->name('templates.copy.create');
    Route::get('/copy/{uid}', [TemplatesController::class, 'copy'])->name('templates.copy.show');
    Route::post('/export/{uid}', [TemplatesController::class, 'export'])->name('templates.export');

    // Template builder operations
    Route::match(['get', 'post'], '/builder/create', [TemplatesController::class, 'builderCreate'])->name('templates.builder.create');
    Route::match(['get', 'post'], '/{uid}/builder/edit', [TemplatesController::class, 'builderEdit'])->name('templates.builder.edit');
    Route::get('/builder/templates/{category_uid?}', [TemplatesController::class, 'builderTemplates'])->name('templates.builder.templates');
    Route::post('/{uid}/builder/edit/asset', [TemplatesController::class, 'uploadTemplateAssets'])->name('templates.upload.template.assets');
    Route::get('/{uid}/builder/edit/content', [TemplatesController::class, 'builderEditContent'])->name('templates.builder.edit.content');
    Route::get('/{uid}/builder/change-template/{change_uid}', [TemplatesController::class, 'builderChangeTemplate'])->name('templates.builder.change.template');

    // Template metadata management
    Route::match(['get', 'post'], '/{uid}/change-name', [TemplatesController::class, 'changeName'])->name('templates.changemame');
    Route::match(['get', 'post'], '/{uid}/categories', [TemplatesController::class, 'categories'])->name('templates.categories');
    Route::match(['get', 'post'], '/{uid}/update-thumb', [TemplatesController::class, 'updateThumb'])->name('templates.update.thumb');
    Route::match(['get', 'post'], '/{uid}/update-thumb-url', [TemplatesController::class, 'updateThumbUrl'])->name('templates.update.thumburl');

    // Template RSS and listings
    Route::get('/rss/parse', [TemplatesController::class, 'parseRss'])->name('templates.parseRss');
    Route::get('/listing/{page?}', [TemplatesController::class, 'listing'])->name('templates.listing');
    Route::get('/choosing/{campaign_uid}/{page?}', [TemplatesController::class, 'choosing'])->name('templates.choosing');

    // AI Chat integration
    Route::get('/chat', [TemplatesController::class, 'chat'])->name('templates.chat');

});

// ===================================================================
// CAMPAIGNS ROUTES - Email campaign management (60+ routes)
// ===================================================================

Route::group(['prefix' => 'campaigns'], function () {

    // Campaign CRUD operations
    Route::get('/', [CampaignsController::class, 'index'])->name('campaigns');
    Route::get('/create', [CampaignsController::class, 'create'])->name('campaigns.create');
    Route::post('/store', [CampaignsController::class, 'store'])->name('campaigns.store');
    Route::get('/view/{uid}', [CampaignsController::class, 'view'])->name('campaigns.view');
    Route::get('/edit', [CampaignsController::class, 'edit'])->name('campaigns.edit');
    Route::get('/{uid}/edit', [CampaignsController::class, 'edit'])->name('campaigns.edit');
    Route::patch('/{uid}/update', [CampaignsController::class, 'update'])->name('campaigns.update');
    Route::get('/destroy/{uid}', [CampaignsController::class, 'destroy'])->name('campaigns.destroy');
    Route::post('/delete', [CampaignsController::class, 'delete'])->name('campaigns.delete');

    // Campaign state management
    Route::post('/pause', [CampaignsController::class, 'pause'])->name('campaigns.pause');
    Route::post('/restart', [CampaignsController::class, 'restart'])->name('campaigns.restart');
    Route::get('/{uid}/run', [CampaignsController::class, 'run'])->name('campaigns.run');
    Route::match(['get', 'post'], '/{uid}/resend', [CampaignsController::class, 'resend'])->name('campaigns.resend');

    // Campaign setup and configuration
    Route::match(['get', 'post'], '/{uid}/setup', [CampaignsController::class, 'setup'])->name('campaigns.setup');
    Route::match(['get', 'post'], '/{uid}/template', [CampaignsController::class, 'template'])->name('campaigns.template');
    Route::match(['get', 'post'], '/{uid}/recipients', [CampaignsController::class, 'recipients'])->name('campaigns.recipients');
    Route::match(['get', 'post'], '/{uid}/schedule', [CampaignsController::class, 'schedule'])->name('campaigns.schedule');
    Route::match(['get', 'post'], '/{uid}/confirm', [CampaignsController::class, 'confirm'])->name('campaigns.confirm');
    Route::get('/{uid}/list-segment-form', [CampaignsController::class, 'listSegmentForm'])->name('campaigns.list.segment.form');
    Route::get('/select-type', [CampaignsController::class, 'selecttype'])->name('campaigns.selecttype');

    // Campaign template management
    Route::get('/{uid}/template/select', [CampaignsController::class, 'templateSelect'])->name('campaigns.templateSelect');
    Route::get('/{uid}/template/choose/{template_uid}', [CampaignsController::class, 'templateChoose'])->name('campaigns.templateChoose');
    Route::get('/{uid}/template/create', [CampaignsController::class, 'templateCreate'])->name('campaigns.templateCreate');
    Route::match(['get', 'post'], '/{uid}/template/edit', [CampaignsController::class, 'templateEdit'])->name('campaigns.templateEdit');
    Route::match(['get', 'post'], '/{uid}/template/upload', [CampaignsController::class, 'templateUpload'])->name('campaigns.templateUpload');
    Route::get('/{uid}/template/change/{template_uid}', [CampaignsController::class, 'templateChangeTemplate'])->name('campaigns.templateChangeTemplate');
    Route::get('/{uid}/template/content', [CampaignsController::class, 'templateContent'])->name('campaigns.templateContent');
    Route::get('/{uid}/template/preview', [CampaignsController::class, 'templatePreview'])->name('campaigns.templatePreview');
    Route::get('/{uid}/template/iframe', [CampaignsController::class, 'templateIframe'])->name('campaigns.templateIframe');
    Route::get('/{uid}/template/review', [CampaignsController::class, 'templateReview'])->name('campaigns.templateReview');
    Route::get('/{uid}/template/review-iframe', [CampaignsController::class, 'templateReviewIframe'])->name('campaigns.templateReviewIframe');
    Route::get('/{uid}/template/builder-select', [CampaignsController::class, 'templateBuilderSelect'])->name('campaigns.templateBuilderSelect');
    Route::get('/{uid}/template/build/{style}', [CampaignsController::class, 'templateBuild'])->name('campaigns.templateBuild');
    Route::get('/{uid}/template/rebuild', [CampaignsController::class, 'templateRebuild'])->name('campaigns.templateRebuild');
    Route::get('/{uid}/template/layout/list', [CampaignsController::class, 'templateLayoutList'])->name('campaigns.templateLayoutList');
    Route::match(['get', 'post'], '/{uid}/template/layout', [CampaignsController::class, 'templateLayout'])->name('campaigns.templateLayout');

    // Campaign template builder operations
    Route::match(['get', 'post'], '/{uid}/template/builder-classic', [CampaignsController::class, 'builderClassic'])->name('campaigns.builderClassic');
    Route::match(['get', 'post'], '/{uid}/template/builder-plain', [CampaignsController::class, 'builderPlainEdit'])->name('campaigns.builderPlainEdit');
    Route::match(['get', 'post'], '/{uid}/plain', [CampaignsController::class, 'plain'])->name('campaigns.plain');

    // Campaign preheader management
    Route::get('/{uid}/preheader', [CampaignsController::class, 'preheader'])->name('campaigns.preheader');
    Route::match(['get', 'post'], '/{uid}/preheader/add', [CampaignsController::class, 'preheaderAdd'])->name('campaigns.preheaderAdd');
    Route::post('/{uid}/preheader/remove', [CampaignsController::class, 'preheaderRemove'])->name('campaigns.preheaderRemove');

    // Campaign custom content
    Route::post('/{uid}/custom-plain/on', [CampaignsController::class, 'customPlainOn'])->name('campaigns.customPlainOn');
    Route::post('/{uid}/custom-plain/off', [CampaignsController::class, 'customPlainOff'])->name('campaigns.customPlainOff');

    // Campaign attachments
    Route::post('/{uid}/upload-attachment', [CampaignsController::class, 'uploadAttachment'])->name('campaigns.uploadAttachment');
    Route::post('/{uid}/remove-attachment', [CampaignsController::class, 'removeAttachment'])->name('campaigns.removeAttachment');
    Route::get('/{uid}/download-attachment', [CampaignsController::class, 'downloadAttachment'])->name('campaigns.downloadAttachment');

    // Campaign preview and testing
    Route::get('/{uid}/preview', [CampaignsController::class, 'preview'])->name('campaigns.preview');
    Route::get('/{uid}/preview/content/{subscriber_uid?}', [CampaignsController::class, 'previewContent'])->name('campaigns.previewContent');
    Route::get('/{uid}/preview-as', [CampaignsController::class, 'previewAs'])->name('campaigns.previewAs');
    Route::get('/{uid}/preview-as/list', [CampaignsController::class, 'previewAsList'])->name('campaigns.previewAsList');
    Route::match(['get', 'post'], '/send-test-email', [CampaignsController::class, 'sendTestEmail'])->name('campaigns.send.test');

    // Campaign webhooks management
    Route::get('/{uid}/webhooks', [CampaignsController::class, 'webhooks'])->name('campaigns.webhooks');
    Route::match(['get', 'post'], '/{uid}/webhooks/add', [CampaignsController::class, 'webhooksAdd'])->name('campaigns.webhooksAdd');
    Route::get('/{uid}/webhooks/list', [CampaignsController::class, 'webhooksList'])->name('campaigns.webhooksList');
    Route::get('/{uid}/webhooks/link-select', [CampaignsController::class, 'webhooksLinkSelect'])->name('campaigns.webhooksLinkSelect');
    Route::match(['get', 'post'], '/webhooks/{webhook_uid}/edit', [CampaignsController::class, 'webhooksEdit'])->name('campaigns.webhooksEdit');
    Route::match(['get', 'post'], '/webhooks/{webhook_uid}/test', [CampaignsController::class, 'webhooksTest'])->name('campaigns.webhooksTest');
    Route::post('/webhooks/{webhook_uid}/test/{message_id}', [CampaignsController::class, 'webhooksTestMessage'])->name('campaigns.webhooksTestMessage');
    Route::post('/webhooks/{webhook_uid}/delete', [CampaignsController::class, 'webhooksDelete'])->name('campaigns.webhooksDelete');
    Route::get('/webhooks/{webhook_uid}/sample/request', [CampaignsController::class, 'webhooksSampleRequest'])->name('campaigns.webhooksSampleRequest');

    // Campaign tracking and analytics - Subscriber
    Route::get('/{uid}/subscribers', [CampaignsController::class, 'subscribers'])->name('campaigns.subscribers');
    Route::get('/{uid}/subscribers/listing', [CampaignsController::class, 'subscribersListing'])->name('campaigns.subscribers.listing');

    // Campaign tracking and analytics - Logs
    Route::get('/{uid}/tracking-log', [CampaignsController::class, 'trackingLog'])->name('campaigns.tracking.log');
    Route::get('/{uid}/tracking-log/listing', [CampaignsController::class, 'trackingLogListing'])->name('campaigns.tracking.log.listing');
    Route::get('/{uid}/tracking-log/download', [CampaignsController::class, 'trackingLogDownload'])->name('campaigns.trackingLogDownload');
    Route::get('/{uid}/open-log', [CampaignsController::class, 'openLog'])->name('campaigns.open.log');
    Route::get('/{uid}/open-log/listing', [CampaignsController::class, 'openLogListing'])->name('campaigns.open.log.listing');
    Route::get('/{uid}/open-log/{message_id}/execute', [CampaignsController::class, 'openLogExecute'])->name('campaigns.openLogExecute');
    Route::get('/{uid}/open-map', [CampaignsController::class, 'openMap'])->name('campaigns.open.map');
    Route::get('/{uid}/click-log', [CampaignsController::class, 'clickLog'])->name('campaigns.click.log');
    Route::get('/{uid}/click-log/listing', [CampaignsController::class, 'clickLogListing'])->name('campaigns.click.log.listing');
    Route::get('/{uid}/click-log/{message_id}/execute', [CampaignsController::class, 'clickLogExecute'])->name('campaigns.clickLogExecute');
    Route::get('/{uid}/bounce-log', [CampaignsController::class, 'bounceLog'])->name('campaigns.bounceLog');
    Route::get('/{uid}/bounce-log/listing', [CampaignsController::class, 'bounceLogListing'])->name('campaigns.bounce.log.listing');
    Route::get('/{uid}/feedback-log', [CampaignsController::class, 'feedbackLog'])->name('campaigns.feedbackLog');
    Route::get('/{uid}/feedback-log/listing', [CampaignsController::class, 'feedbackLogListing'])->name('campaigns.feedback.log.listing');
    Route::get('/{uid}/unsubscribe-log', [CampaignsController::class, 'unsubscribeLog'])->name('campaigns.unsubscribe.log');
    Route::get('/{uid}/unsubscribe-log/listing', [CampaignsController::class, 'unsubscribeLogListing'])->name('campaigns.unsubscribe.log.listing');

    // Campaign analytics and charts
    Route::get('/{uid}/overview', [CampaignsController::class, 'overview'])->name('campaigns.overview');
    Route::get('/{uid}/chart', [CampaignsController::class, 'chart'])->name('campaigns.chart');
    Route::get('/{uid}/chart24h', [CampaignsController::class, 'chart24h'])->name('campaigns.chart24h');
    Route::get('/{uid}/chart/countries/open', [CampaignsController::class, 'chartCountry'])->name('campaigns.chartCountry');
    Route::get('/{uid}/chart/countries/click', [CampaignsController::class, 'chartClickCountry'])->name('campaigns.chartClickCountry');
    Route::get('/{uid}/links', [CampaignsController::class, 'links'])->name('campaigns.links');
    Route::get('/{uid}/spam-score', [CampaignsController::class, 'spamScore'])->name('campaigns.spamScore');
    Route::get('/{uid}/update-stats', [CampaignsController::class, 'updateStats'])->name('campaigns.updateStats');

    // Campaign copy and export
    Route::match(['get', 'post'], '/copy', [CampaignsController::class, 'copy'])->name('campaigns.copy');
    Route::get('/{from_uid}/copy-move-from/{action}', [CampaignsController::class, 'copyMoveForm'])->name('campaigns.copyMoveForm');

    // Campaign deletion
    Route::get('/delete/confirm', [CampaignsController::class, 'deleteConfirm'])->name('campaigns.deleteConfirm');

    // Campaign data exports
    Route::get('/job/{uid}/progress', [CampaignsController::class, 'trackingLogExportProgress'])->name('campaigns.trackingLogExportProgress');
    Route::get('/job/{uid}/download', [CampaignsController::class, 'download'])->name('campaigns.download');

    // Campaign listing and utilities
    Route::get('/listing/{page?}', [CampaignsController::class, 'listing'])->name('campaigns.listing');
    Route::get('/quick-view', [CampaignsController::class, 'quickView'])->name('campaigns.quickView');
    Route::get('/select2', [CampaignsController::class, 'select2'])->name('campaigns.select2');

});

// ===================================================================
// SEGMENTS ROUTES - Audience segmentation
// ===================================================================

Route::group(['prefix' => 'segments'], function () {

    // Segment CRUD operations
    Route::get('/lists/{list_uid}/segments', [SegmentController::class, 'index'])->name('segments.index');
    Route::get('/lists/{list_uid}/segments/create', [SegmentController::class, 'create'])->name('segments.create');
    Route::post('/lists/{list_uid}/segments/store', [SegmentController::class, 'store'])->name('segments.store');
    Route::get('/lists/{list_uid}/segments/{uid}/edit', [SegmentController::class, 'edit'])->name('segments.edit');
    Route::patch('/lists/{list_uid}/segments/{uid}/update', [SegmentController::class, 'update'])->name('segments.update');
    Route::get('/lists/{list_uid}/segments/delete', [SegmentController::class, 'delete'])->name('segments.delete');

    // Segment listing and subscribers
    Route::get('/lists/{list_uid}/segments/listing', [SegmentController::class, 'listing'])->name('segments.listing');
    Route::get('/lists/{list_uid}/segments/{uid}/subscribers', [SegmentController::class, 'subscribers'])->name('segments.subscribers.');
    Route::get('/lists/{list_uid}/segments/{uid}/listing_subscribers', [SegmentController::class, 'listing_subscribers'])->name('segments.listing_subscribers');

    // Segment conditions and utilities
    Route::get('/lists/{list_uid}/segments/sample_condition', [SegmentController::class, 'sample_condition'])->name('segments.sample_condition');
    Route::get('/condition-value-control', [SegmentController::class, 'conditionValueControl'])->name('segments.conditionValueControl');
    Route::get('/select_box', [SegmentController::class, 'selectBox'])->name('segments.selectBox');
    Route::get('/no-list', [SegmentController::class, 'noList'])->name('segments.noList');

});

// ===================================================================
// MAILLISTS ROUTES - Subscriber list management (40+ routes)
// ===================================================================

Route::group(['prefix' => 'maillists'], function () {

    // Maillist CRUD operations
    Route::get('/', [MaillistController::class, 'index'])->name('maillists');
    Route::get('/create', [MaillistController::class, 'create'])->name('maillists.create');
    Route::post('/store', [MaillistController::class, 'store'])->name('maillists.store');
    Route::get('/edit/{uid}', [MaillistController::class, 'edit'])->name('maillists.edit');
    Route::get('/view/{uid}', [MaillistController::class, 'view'])->name('maillists.view');
    Route::get('/destroy/{uid}', [MaillistController::class, 'destroy'])->name('maillists.destroy');

    // Maillist update and management
    Route::get('/lists/{uid}/overview', [MaillistController::class, 'overview'])->name('maillists.overview');
    Route::get('/lists/{uid}/edit', [MaillistController::class, 'edit'])->name('maillists.lists.edit');
    Route::post('/lists/{uid}/update', [MaillistController::class, 'update'])->name('maillists.update');
    Route::match(['get', 'post'], '/lists/copy', [MaillistController::class, 'copy'])->name('maillists.copy');
    Route::match(['get', 'post'], '/lists/select', [MaillistController::class, 'selectList'])->name('maillists.selectList');

    // Maillist verification management
    Route::get('/lists/{uid}/verification', [MaillistController::class, 'verification'])->name('maillists.verification');
    Route::post('/lists/{uid}/verification/start', [MaillistController::class, 'startVerification'])->name('maillists.startVerification');
    Route::post('/lists/{uid}/verification/{job_uid}/stop', [MaillistController::class, 'stopVerification'])->name('maillists.stopVerification');
    Route::post('/lists/{uid}/verification/reset', [MaillistController::class, 'resetVerification'])->name('maillists.resetVerification');
    Route::get('/lists/{uid}/verification/{job_uid}/progress', [MaillistController::class, 'verificationProgress'])->name('maillists.verificationProgress');

    // Maillist embedded forms
    Route::match(['get', 'post'], '/lists/{uid}/embedded-form', [MaillistController::class, 'embeddedForm'])->name('maillists.embeddedForm');
    Route::get('/lists/{uid}/embedded-form-frame', [MaillistController::class, 'embeddedFormFrame'])->name('maillists.embeddedFormFrame');
    Route::post('/lists/{uid}/embedded-form-subscribe', [MaillistController::class, 'embeddedFormSubscribe'])->name('maillists.embeddedFormSubscribe');
    Route::post('/lists/{uid}/embedded-form-subscribe-captcha', [MaillistController::class, 'embeddedFormSubscribe'])->name('maillists.embeddedFormSubscribeCaptcha');

    // Maillist customer cloning
    Route::get('/lists/{uid}/clone-to-customers/choose', [MaillistController::class, 'cloneForCustomersChoose'])->name('maillists.cloneForCustomersChoose');
    Route::post('/lists/{uid}/clone-to-customers', [MaillistController::class, 'cloneForCustomers'])->name('maillists.cloneForCustomers');

    // Maillist analytics and charts
    Route::get('/lists/{uid}/email-verification/chart', [MaillistController::class, 'emailVerificationChart'])->name('maillists.emailVerificationChart');
    Route::get('/lists/{uid}/list-growth', [MaillistController::class, 'listGrowthChart'])->name('maillists.listGrowthChart');
    Route::get('/lists/{uid}/list-statistics-chart', [MaillistController::class, 'statisticsChart'])->name('maillists.statisticsChart');

    // Maillist listing and deletion
    Route::get('/lists/listing/{page?}', [MaillistController::class, 'listing'])->name('maillists.listing');
    Route::get('/lists/sort', [MaillistController::class, 'sort'])->name('maillists.sort');
    Route::get('/lists/quick-view', [MaillistController::class, 'quickView'])->name('maillists.quickView');
    Route::post('/lists/delete', [MaillistController::class, 'delete'])->name('maillists.delete');
    Route::get('/lists/delete/confirm', [MaillistController::class, 'deleteConfirm'])->name('maillists.delete.confirm');

    // Email utilities
    Route::get('/lists/{uid}/check-email', [AutomationsController::class, 'checkEmail'])->name('maillists.checkEmail');

});

// ===================================================================
// AUTOMATIONS ROUTES - Automated email workflows (35+ routes)
// ===================================================================

Route::group(['prefix' => 'automations'], function () {

    // Automation CRUD and state management
    Route::get('/', [AutomationsController::class, 'index'])->name('automations');
    Route::post('/enable', [AutomationsController::class, 'enable'])->name('automations.enable');
    Route::post('/disable', [AutomationsController::class, 'disable'])->name('automations.disable');
    Route::delete('/delete', [AutomationsController::class, 'delete'])->name('automations.delete');
    Route::match(['get', 'post'], 'automation/{uid}/copy', [AutomationsController::class, 'copy'])->name('automations.copy');

    // Automation data and settings
    Route::get('/{uid}/settings', [AutomationsController::class, 'settings'])->name('automations.settings');
    Route::post('/{uid}/update', [AutomationsController::class, 'update'])->name('automations.update');
    Route::post('/{uid}/data/save', [AutomationsController::class, 'saveData'])->name('automations.saveData');
    Route::get('/{uid}/insight', [AutomationsController::class, 'insight'])->name('automations.insight');
    Route::get('/{uid}/last-saved', [AutomationsController::class, 'lastSaved'])->name('automations.lastSaved');

    // Automation trigger and execution
    Route::get('trigger/{id}', [AutomationsController::class, 'show'])->name('automations.show');
    Route::get('/{automation}/{subscriber}/trigger', [AutomationsController::class, 'triggerNow'])->name('automations.triggerNow');
    Route::get('/{automation}/run', [AutomationsController::class, 'run'])->name('automations.run');
    Route::post('/{uid}/trigger-all', [AutomationsController::class, 'triggerAll'])->name('automations.triggerAll');
    Route::get('/{uid}/debug', [AutomationsController::class, 'debug'])->name('automations.debug');

    // Automation email preheader management
    Route::get('/{uid}/template/{email_uid}/preheader', [AutomationsController::class, 'emailPreheader'])->name('automations.emailPreheader');
    Route::match(['get', 'post'], 'automation/{uid}/template/{email_uid}/preheader/add', [AutomationsController::class, 'emailPreheaderAdd'])->name('automations.emailPreheaderAdd');
    Route::post('/{uid}/template/{email_uid}/preheader/remove', [AutomationsController::class, 'emailPreheaderRemove'])->name('automations.emailPreheaderRemove');

    // Automation email testing
    Route::match(['get', 'post'], 'automation/{email_uid}/send-test-email', [AutomationsController::class, 'sendTestEmail'])->name('automations.send.test');

    // Automation conditions management
    Route::get('/{uid}/condition/remove', [AutomationsController::class, 'conditionRemove'])->name('automations.conditionRemove');
    Route::get('/{uid}/condition/setting', [AutomationsController::class, 'conditionSetting'])->name('automations.conditionSetting');
    Route::match(['get', 'post'], 'automation/condition/wait/custom', [AutomationsController::class, 'conditionWaitCustom'])->name('automations.conditionWaitCustom');

    // Automation wait time management
    Route::get('/{uid}/wait-time', [AutomationsController::class, 'waitTime'])->name('automations.waitTime');
    Route::post('/{uid}/wait-time', [AutomationsController::class, 'waitTime'])->name('automations.waitTime.update');

    // Automation operations management
    Route::get('/{uid}/operation/select', [AutomationsController::class, 'operationSelect'])->name('automations.operation.select');
    Route::get('/{uid}/operation/show', [AutomationsController::class, 'operationShow'])->name('automations.operationShow');
    Route::match(['get', 'post'], 'automation/{uid}/operation/create', [AutomationsController::class, 'operationCreate'])->name('automations.operation.create');
    Route::match(['get', 'post'], 'automation/{uid}/operation/edit', [AutomationsController::class, 'operationEdit'])->name('automations.operation.edit');

    // Automation cart management
    Route::get('/{uid}/cart/items', [AutomationsController::class, 'cartItems'])->name('automations.cartItems');
    Route::get('/{uid}/cart/list', [AutomationsController::class, 'cartList'])->name('automations.cartList');
    Route::get('/{uid}/cart/stats', [AutomationsController::class, 'cartStats'])->name('automations.cartStats');
    Route::match(['get', 'post'], 'automation/{uid}/cart/wait', [AutomationsController::class, 'cartWait'])->name('automations.cartWait');
    Route::match(['get', 'post'], 'automation/{uid}/cart/change-store', [AutomationsController::class, 'cartChangeStore'])->name('automations.cartChangeStore');
    Route::match(['get', 'post'], 'automation/{uid}/cart/change-list', [AutomationsController::class, 'cartChangeList'])->name('automations.cartChangeList');

    // Automation subscribers management
    Route::get('/{uid}/subscribers', [AutomationsController::class, 'subscribers'])->name('automations.subscribers.');
    Route::get('/{uid}/subscribers/list', [AutomationsController::class, 'subscribersList'])->name('automations.subscribers.List');
    Route::get('/{uid}/subscribers/{subscriber_uid}/show', [AutomationsController::class, 'subscribersShow'])->name('automations.subscribers.Show');
    Route::post('/{uid}/subscribers/{subscriber_uid}/restart', [AutomationsController::class, 'subscribersRestart'])->name('automations.subscribers.restart');
    Route::post('/{uid}/subscribers/{subscriber_uid}/remove', [AutomationsController::class, 'subscribersRemove'])->name('automations.subscribers.remove');

    // Automation email webhooks management
    Route::get('/emails/{email_uid}/webhooks', [AutomationsController::class, 'webhooks'])->name('automations.webhooks');
    Route::match(['get', 'post'], 'automation/emails/{email_uid}/webhooks/add', [AutomationsController::class, 'webhooksAdd'])->name('automations.webhooksAdd');
    Route::get('/emails/{email_uid}/webhooks/list', [AutomationsController::class, 'webhooksList'])->name('automations.webhooksList');
    Route::get('/emails/{email_uid}/webhooks/link-select', [AutomationsController::class, 'webhooksLinkSelect'])->name('automations.webhooksLinkSelect');
    Route::match(['get', 'post'], 'automation/emails/webhooks/{webhook_uid}/edit', [AutomationsController::class, 'webhooksEdit'])->name('automations.webhooksEdit');
    Route::match(['get', 'post'], 'automation/emails/webhooks/{webhook_uid}/test', [AutomationsController::class, 'webhooksTest'])->name('automations.webhooksTest');
    Route::post('/emails/webhooks/{webhook_uid}/delete', [AutomationsController::class, 'webhooksDelete'])->name('automations.webhooksDelete');
    Route::get('/emails/webhooks/{webhook_uid}/sample/request', [AutomationsController::class, 'webhooksSampleRequest'])->name('automations.webhooksSampleRequest');

    // Automation listing
    Route::get('/listing', [AutomationsController::class, 'listing'])->name('automations.listing');

});

// ===================================================================
// LAYOUTS ROUTES - Email layout templates
// ===================================================================

Route::group(['prefix' => 'layouts'], function () {

    // Layout CRUD operations
    Route::get('/', [LayoutController::class, 'index'])->name('layouts');
    Route::get('/create', [LayoutController::class, 'create'])->name('layouts.create');
    Route::post('/store', [LayoutController::class, 'store'])->name('layouts.store');
    Route::get('/edit/{uid}', [LayoutController::class, 'edit'])->name('layouts.edit');
    Route::patch('/update/{uid}', [LayoutController::class, 'update'])->name('layouts.update');
    Route::get('/view/{uid}', [LayoutController::class, 'view'])->name('layouts.view');
    Route::get('/destroy/{uid}', [LayoutController::class, 'destroy'])->name('layouts.destroy');

    // Layout listing and sorting
    Route::get('/listing/{page?}', [LayoutController::class, 'listing'])->name('layouts.listing');
    Route::get('/sort', [LayoutController::class, 'sort'])->name('layouts.sort');

});

// ===================================================================
// END - Campaign Module Routes (171+ total routes)
// ===================================================================
