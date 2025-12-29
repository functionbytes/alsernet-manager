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
| Campaign Module Routes
|--------------------------------------------------------------------------
|
| This file contains all routes for the Campaign module, including:
| - Campaigns (email campaign management)
| - Templates (email templates)
| - Segments (audience segmentation)
| - Maillists (subscriber list management)
| - Automations (automated email workflows)
| - Subscribers (subscriber management and lists)
|
| All routes are protected by 'web' and 'auth' middleware via RouteServiceProvider
| All routes are prefixed with 'manager.' in route names
|
*/

Route::group(['prefix' => 'subscribers'], function () {

    Route::get('/', [SubscribersController::class, 'index'])->name('subscribers');
    Route::get('/create', [SubscribersController::class, 'create'])->name('subscribers.create');
    Route::post('/update', [SubscribersController::class, 'update'])->name('subscribers.update');
    Route::get('/edit/{uid}', [SubscribersController::class, 'edit'])->name('subscribers.edit');
    Route::get('/view/{uid}', [SubscribersController::class, 'view'])->name('subscribers.view');
    Route::get('/destroy/{uid}', [SubscribersController::class, 'destroy'])->name('subscribers.destroy');
    Route::get('/logs/{slack}', [SubscribersController::class, 'logs'])->name('subscribers.logs');

    Route::get('/imports/create', [SubscribersController::class, 'createImport'])->name('subscribers.imports.create');
    Route::get('/imports/{import_uid}', [SubscribersController::class, 'createImports'])->name('subscribers.import');
    Route::post('/imports/{import_uid}/dispatch', [SubscribersController::class, 'dispatchImportListsJobs'])->name('subscribers.import.dispatch');
    Route::get('/imports/{job_uid}/progress', [SubscribersController::class, 'importListsProgress'])->name('subscribers.import.progress');
    Route::get('/imports/{job_uid}/log/download', [SubscribersController::class, 'downloadImportListsLog'])->name('subscribers.import.log.download');
    Route::post('/imports/{job_uid}/cancel', [SubscribersController::class, 'cancelImportLists'])->name('subscribers.import.cancel');

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
    Route::get('/lists/categories/{uid}', [SubscribersListsController::class, 'categories'])->name('subscribers.lists.categories');
    Route::get('/lists/destroy/{uid}', [SubscribersListsController::class, 'destroy'])->name('subscribers.lists.destroy');
    Route::get('/lists/includes/{uid}', [SubscribersListsController::class, 'includes'])->name('subscribers.lists.includes');
    Route::post('/lists/includes/update', [SubscribersListsController::class, 'updateIncludes'])->name('subscribers.lists.includes.update');
    Route::post('/lists/categories/update', [SubscribersListsController::class, 'updateCategories'])->name('subscribers.lists.categories.update');

    Route::get('/lists/report/generate', [SubscribersReportController::class, 'generate'])->name('subscribers.lists.reports.generate');

    Route::get('/conditions', [SubscribersConditionsController::class, 'index'])->name('subscribers.conditions');
    Route::get('/conditions/create', [SubscribersConditionsController::class, 'create'])->name('subscribers.conditions.create');
    Route::post('/conditions/store', [SubscribersConditionsController::class, 'store'])->name('subscribers.conditions.store');
    Route::post('/conditions/update', [SubscribersConditionsController::class, 'update'])->name('subscribers.conditions.update');
    Route::get('/conditions/edit/{uid}', [SubscribersConditionsController::class, 'edit'])->name('subscribers.conditions.edit');
    Route::get('/conditions/view/{uid}', [SubscribersConditionsController::class, 'view'])->name('subscribers.conditions.view');
    Route::get('/conditions/destroy/{uid}', [SubscribersConditionsController::class, 'destroy'])->name('subscribers.conditions.destroy');

});

Route::group(['prefix' => 'templates'], function () {

    Route::get('/', [TemplatesController::class, 'index'])->name('templates');
    Route::get('/create', [TemplatesController::class, 'create'])->name('templates.create');
    Route::get('/chat', [TemplatesController::class, 'chat'])->name('templates.chat');
    Route::post('/store', [TemplatesController::class, 'store'])->name('templates.store');
    Route::post('/upload', [TemplatesController::class, 'uploadTemplate'])->name('templates.uploadTemplate');
    Route::post('/update', [TemplatesController::class, 'update'])->name('templates.update');
    Route::get('/delete', [TemplatesController::class, 'delete'])->name('templates.delete');
    Route::get('/edit/{uid}', [TemplatesController::class, 'edit'])->name('templates.edit');
    Route::get('/view/{uid}', [TemplatesController::class, 'view'])->name('templates.view');
    Route::get('/destroy/{uid}', [TemplatesController::class, 'destroy'])->name('templates.destroy');
    Route::get('/preview/{uid}', [TemplatesController::class, 'preview'])->name('templates.preview');
    Route::get('/edit/{uid}', [TemplatesController::class, 'edit'])->name('templates.uid.edit');
    Route::post('/copy/{uid}', [TemplatesController::class, 'copy'])->name('templates.copy.create');
    Route::get('/copy/{uid}', [TemplatesController::class, 'copy'])->name('templates.copy.show');
    Route::post('/export/{uid}', [TemplatesController::class, 'export'])->name('templates.export');
    Route::patch('/update/{uid}', [TemplatesController::class, 'update'])->name('templates.uid.update');

    Route::get('/rss/parse', [TemplatesController::class, 'parseRss'])->name('templates.parseRss');

    Route::get('/listing/{page?}', [TemplatesController::class, 'listing'])->name('templates.listing');
    Route::get('/choosing/{campaign_uid}/{page?}', [TemplatesController::class, 'choosing'])->name('templates.choosing');

    Route::match(['get', 'post'], '/builder/create', [TemplatesController::class, 'builderCreate'])->name('templates.builder.create');
    Route::match(['get', 'post'], '/{uid}/change-name', [TemplatesController::class, 'changeName'])->name('templates.changemame');
    Route::match(['get', 'post'], '/{uid}/categories', [TemplatesController::class, 'categories'])->name('templates.categories');
    Route::match(['get', 'post'], '/{uid}/update-thumb-url', [TemplatesController::class, 'updateThumbUrl'])->name('templates.update.thumburl');
    Route::match(['get', 'post'], '/{uid}/update-thumb', [TemplatesController::class, 'updateThumb'])->name('templates.update.thumb');
    Route::match(['get', 'post'], '/{uid}/builder/edit', [TemplatesController::class, 'builderEdit'])->name('templates.builder.edit');

    Route::get('/builder/templates/{category_uid?}', [TemplatesController::class, 'builderTemplates'])->name('templates.builder.templates');
    Route::post('/{uid}/builder/edit/asset', [TemplatesController::class, 'uploadTemplateAssets'])->name('templates.upload.template.assets');
    Route::get('/{uid}/builder/edit/content', [TemplatesController::class, 'builderEditContent'])->name('templates.builder.edit.content');
    Route::get('/{uid}/builder/change-template/{change_uid}', [TemplatesController::class, 'builderChangeTemplate'])->name('templates.builder.change.template');

});

Route::group(['prefix' => 'campaigns'], function () {

    Route::get('/', [CampaignsController::class, 'index'])->name('campaigns');
    Route::get('/create', [CampaignsController::class, 'create'])->name('campaigns.create');
    Route::post('/store', [CampaignsController::class, 'store'])->name('campaigns.store');
    Route::get('/view/{uid}', [CampaignsController::class, 'view'])->name('campaigns.view');
    Route::get('/destroy/{uid}', [CampaignsController::class, 'destroy'])->name('campaigns.destroy');

    Route::post('/{uid}/preheader/remove', [CampaignsController::class, 'preheaderRemove'])->name('campaigns.preheaderRemove');
    Route::match(['get', 'post'], '/{uid}/preheader/add', [CampaignsController::class, 'preheaderAdd'])->name('campaigns.preheaderAdd');
    Route::get('/{uid}/preheader', [CampaignsController::class, 'preheader'])->name('campaigns.preheader');

    Route::post('/webhooks/{webhook_uid}/test/{message_id}', [CampaignsController::class, 'webhooksTestMessage'])->name('campaigns.webhooksTestMessage');
    Route::get('/{uid}/click-log/{message_id}/execute', [CampaignsController::class, 'clickLogExecute'])->name('campaigns.clickLogExecute');
    Route::get('/{uid}/open-log/{message_id}/execute', [CampaignsController::class, 'openLogExecute'])->name('campaigns.openLogExecute');
    Route::match(['get', 'post'], '/webhooks/{webhook_uid}/test', [CampaignsController::class, 'webhooksTest'])->name('campaigns.webhooksTest');
    Route::get('/webhooks/{webhook_uid}/sample/request', [CampaignsController::class, 'webhooksSampleRequest'])->name('campaigns.webhooksSampleRequest');
    Route::post('/webhooks/{webhook_uid}/delete', [CampaignsController::class, 'webhooksDelete'])->name('campaigns.webhooksDelete');
    Route::match(['get', 'post'], '/webhooks/{webhook_uid}/edit', [CampaignsController::class, 'webhooksEdit'])->name('campaigns.webhooksEdit');
    Route::get('/{uid}/webhooks/list', [CampaignsController::class, 'webhooksList'])->name('campaigns.webhooksList');
    Route::get('/{uid}/webhooks/link-select', [CampaignsController::class, 'webhooksLinkSelect'])->name('campaigns.webhooksLinkSelect');
    Route::match(['get', 'post'], '/{uid}/webhooks/add', [CampaignsController::class, 'webhooksAdd'])->name('campaigns.webhooksAdd');
    Route::get('/{uid}/webhooks', [CampaignsController::class, 'webhooks'])->name('campaigns.webhooks');

    Route::get('/{uid}/preview-as/list', [CampaignsController::class, 'previewAsList'])->name('campaigns.previewAsList');
    Route::get('/{uid}/preview-as', [CampaignsController::class, 'previewAs'])->name('campaigns.previewAs');

    Route::post('/{uid}/custom-plain/off', [CampaignsController::class, 'customPlainOff'])->name('campaigns.customPlainOff');
    Route::post('/{uid}/custom-plain/on', [CampaignsController::class, 'customPlainOn'])->name('campaigns.customPlainOn');
    Route::post('/{uid}/remove-attachment', [CampaignsController::class, 'removeAttachment'])->name('campaigns.removeAttachment');
    Route::get('/{uid}/download-attachment', [CampaignsController::class, 'downloadAttachment'])->name('campaigns.downloadAttachment');
    Route::post('/{uid}/upload-attachment', [CampaignsController::class, 'uploadAttachment'])->name('campaigns.uploadAttachment');
    Route::get('/{uid}/template/builder-select', [CampaignsController::class, 'templateBuilderSelect'])->name('campaigns.templateBuilderSelect');

    Route::match(['get', 'post'], '/{uid}/template/builder-plain', [CampaignsController::class, 'builderPlainEdit'])->name('campaigns.builderPlainEdit');
    Route::match(['get', 'post'], '/{uid}/template/builder-classic', [CampaignsController::class, 'builderClassic'])->name('campaigns.builderClassic');
    Route::match(['get', 'post'], '/{uid}/plain', [CampaignsController::class, 'plain'])->name('campaigns.plain');
    Route::get('/{uid}/template/change/{template_uid}', [CampaignsController::class, 'templateChangeTemplate'])->name('campaigns.templateChangeTemplate');

    Route::get('/{uid}/template/content', [CampaignsController::class, 'templateContent'])->name('campaigns.templateContent');
    Route::match(['get', 'post'], '/{uid}/template/edit', [CampaignsController::class, 'templateEdit'])->name('campaigns.templateEdit');
    Route::match(['get', 'post'], '/{uid}/template/upload', [CampaignsController::class, 'templateUpload'])->name('campaigns.templateUpload');
    Route::get('/{uid}/template/layout/list', [CampaignsController::class, 'templateLayoutList'])->name('campaigns.templateLayoutList');
    Route::match(['get', 'post'], '/{uid}/template/layout', [CampaignsController::class, 'templateLayout'])->name('campaigns.templateLayout');
    Route::get('/{uid}/template/create', [CampaignsController::class, 'templateCreate'])->name('campaigns.templateCreate');

    Route::get('/{uid}/spam-score', [CampaignsController::class, 'spamScore'])->name('campaigns.spamScore');
    Route::get('/{from_uid}/copy-move-from/{action}', [CampaignsController::class, 'copyMoveForm'])->name('campaigns.copyMoveForm');
    Route::match(['get', 'post'], '/{uid}/resend', [CampaignsController::class, 'resend'])->name('campaigns.resend');
    Route::get('/{uid}/tracking-log/download', [CampaignsController::class, 'trackingLogDownload'])->name('campaigns.trackingLogDownload');
    Route::get('/job/{uid}/progress', [CampaignsController::class, 'trackingLogExportProgress'])->name('campaigns.trackingLogExportProgress');
    Route::get('/job/{uid}/download', [CampaignsController::class, 'download'])->name('campaigns.download');

    Route::get('/{uid}/template/review-iframe', [CampaignsController::class, 'templateReviewIframe'])->name('campaigns.templateReviewIframe');
    Route::get('/{uid}/template/review', [CampaignsController::class, 'templateReview'])->name('campaigns.templateReview');
    Route::get('/select-type', [CampaignsController::class, 'selecttype'])->name('campaigns.selecttype');
    Route::get('/{uid}/list-segment-form', [CampaignsController::class, 'listSegmentForm'])->name('campaigns.list.segment.form');
    Route::get('/{uid}/preview/content/{subscriber_uid?}', [CampaignsController::class, 'previewContent'])->name('campaigns.previewContent');
    Route::get('/{uid}/preview', [CampaignsController::class, 'preview'])->name('campaigns.preview');
    Route::match(['get', 'post'], '/send-test-email', [CampaignsController::class, 'sendTestEmail'])->name('campaigns.send.test');
    Route::get('/delete/confirm', [CampaignsController::class, 'deleteConfirm'])->name('campaigns.deleteConfirm');
    Route::match(['get', 'post'], '/copy', [CampaignsController::class, 'copy'])->name('campaigns.copy');

    Route::get('/{uid}/subscribers', [CampaignsController::class, 'subscribers'])->name('campaigns.subscribers');
    Route::get('/{uid}/subscribers/listing', [CampaignsController::class, 'subscribersListing'])->name('campaigns.subscribers.listing');
    Route::get('/{uid}/open-map', [CampaignsController::class, 'openMap'])->name('campaigns.open.map');
    Route::get('/{uid}/tracking-log', [CampaignsController::class, 'trackingLog'])->name('campaigns.tracking.log');
    Route::get('/{uid}/tracking-log/listing', [CampaignsController::class, 'trackingLogListing'])->name('campaigns.tracking.log.listing');
    Route::get('/{uid}/bounce-log', [CampaignsController::class, 'bounceLog'])->name('campaigns.bounceLog');
    Route::get('/{uid}/bounce-log/listing', [CampaignsController::class, 'bounceLogListing'])->name('campaigns.bounce.log.listing');
    Route::get('/{uid}/feedback-log', [CampaignsController::class, 'feedbackLog'])->name('campaigns.feedbackLog');
    Route::get('/{uid}/feedback-log/listing', [CampaignsController::class, 'feedbackLogListing'])->name('campaigns.feedback.log.listing');
    Route::get('/{uid}/open-log', [CampaignsController::class, 'openLog'])->name('campaigns.open.log');
    Route::get('/{uid}/open-log/listing', [CampaignsController::class, 'openLogListing'])->name('campaigns.open.log.listing');
    Route::get('/{uid}/click-log', [CampaignsController::class, 'clickLog'])->name('campaigns.click.log');
    Route::get('/{uid}/click-log/listing', [CampaignsController::class, 'clickLogListing'])->name('campaigns.click.log.listing');
    Route::get('/{uid}/unsubscribe-log', [CampaignsController::class, 'unsubscribeLog'])->name('campaigns.unsubscribe.log');
    Route::get('/{uid}/unsubscribe-log/listing', [CampaignsController::class, 'unsubscribeLogListing'])->name('campaigns.unsubscribe.log.listing');

    Route::get('/quick-view', [CampaignsController::class, 'quickView'])->name('campaigns.quickView');
    Route::get('/{uid}/chart24h', [CampaignsController::class, 'chart24h'])->name('campaigns.chart24h');
    Route::get('/{uid}/chart', [CampaignsController::class, 'chart'])->name('campaigns.chart');
    Route::get('/{uid}/chart/countries/open', [CampaignsController::class, 'chartCountry'])->name('campaigns.chartCountry');
    Route::get('/{uid}/chart/countries/click', [CampaignsController::class, 'chartClickCountry'])->name('campaigns.chartClickCountry');
    Route::get('/{uid}/overview', [CampaignsController::class, 'overview'])->name('campaigns.overview');
    Route::get('/{uid}/links', [CampaignsController::class, 'links'])->name('campaigns.links');

    Route::get('/listing/{page?}', [CampaignsController::class, 'listing'])->name('campaigns.listing');

    Route::match(['get', 'post'], '/{uid}/setup', [CampaignsController::class, 'setup'])->name('campaigns.setup');
    Route::match(['get', 'post'], '/{uid}/template', [CampaignsController::class, 'template'])->name('campaigns.template');
    Route::match(['get', 'post'], '/{uid}/recipients', [CampaignsController::class, 'recipients'])->name('campaigns.recipients');
    Route::match(['get', 'post'], '/{uid}/schedule', [CampaignsController::class, 'schedule'])->name('campaigns.schedule');
    Route::match(['get', 'post'], '/{uid}/confirm', [CampaignsController::class, 'confirm'])->name('campaigns.confirm');

    Route::get('/{uid}/template/select', [CampaignsController::class, 'templateSelect'])->name('campaigns.templateSelect');
    Route::get('/{uid}/template/choose/{template_uid}', [CampaignsController::class, 'templateChoose'])->name('campaigns.templateChoose');
    Route::get('/{uid}/template/preview', [CampaignsController::class, 'templatePreview'])->name('campaigns.templatePreview');
    Route::get('/{uid}/template/iframe', [CampaignsController::class, 'templateIframe'])->name('campaigns.templateIframe');
    Route::get('/{uid}/template/build/{style}', [CampaignsController::class, 'templateBuild'])->name('campaigns.templateBuild');
    Route::get('/{uid}/template/rebuild', [CampaignsController::class, 'templateRebuild'])->name('campaigns.templateRebuild');

    Route::post('/delete', [CampaignsController::class, 'delete'])->name('campaigns.delete');
    Route::get('/select2', [CampaignsController::class, 'select2'])->name('campaigns.select2');
    Route::post('/pause', [CampaignsController::class, 'pause'])->name('campaigns.pause');
    Route::post('/restart', [CampaignsController::class, 'restart'])->name('campaigns.restart');
    Route::get('/{uid}/edit', [CampaignsController::class, 'edit'])->name('campaigns.edit');
    Route::patch('/{uid}/update', [CampaignsController::class, 'update'])->name('campaigns.update');
    Route::get('/{uid}/run', [CampaignsController::class, 'run'])->name('campaigns.run');
    Route::get('/{uid}/update-stats', [CampaignsController::class, 'updateStats'])->name('campaigns.updateStats');

});

Route::group(['prefix' => 'segments'], function () {

    Route::get('/no-list', [SegmentController::class, 'noList'])->name('segments.noList');
    Route::get('/condition-value-control', [SegmentController::class, 'conditionValueControl'])->name('segments.conditionValueControl');
    Route::get('/select_box', [SegmentController::class, 'selectBox'])->name('segments.selectBox');
    Route::get('/lists/{list_uid}/segments', [SegmentController::class, 'index'])->name('segments.index');
    Route::get('/lists/{list_uid}/segments/{uid}/subscribers', [SegmentController::class, 'subscribers'])->name('segments.subscribers.');
    Route::get('/lists/{list_uid}/segments/{uid}/listing_subscribers', [SegmentController::class, 'listing_subscribers'])->name('segments.listing_subscribers');
    Route::get('/lists/{list_uid}/segments/create', [SegmentController::class, 'create'])->name('segments.create');
    Route::get('/lists/{list_uid}/segments/listing', [SegmentController::class, 'listing'])->name('segments.listing');
    Route::post('/lists/{list_uid}/segments/store', [SegmentController::class, 'store'])->name('segments.store');
    Route::get('/lists/{list_uid}/segments/{uid}/edit', [SegmentController::class, 'edit'])->name('segments.edit');
    Route::patch('/lists/{list_uid}/segments/{uid}/update', [SegmentController::class, 'update'])->name('segments.update');
    Route::get('/lists/{list_uid}/segments/delete', [SegmentController::class, 'delete'])->name('segments.delete');
    Route::get('/lists/{list_uid}/segments/sample_condition', [SegmentController::class, 'sample_condition'])->name('segments.sample_condition');

});

Route::group(['prefix' => 'maillists'], function () {

    Route::get('/', [MaillistController::class, 'index'])->name('maillists');
    Route::get('/create', [MaillistController::class, 'create'])->name('maillists.create');
    Route::post('/store', [MaillistController::class, 'store'])->name('maillists.store');
    Route::get('/edit/{uid}', [MaillistController::class, 'edit'])->name('maillists.edit');
    Route::get('/view/{uid}', [MaillistController::class, 'view'])->name('maillists.view');
    Route::get('/destroy/{uid}', [MaillistController::class, 'destroy'])->name('maillists.destroy');

    Route::match(['get', 'post'], '/lists/select', [MaillistController::class, 'selectList'])->name('maillists.selectList');
    Route::get('/lists/{uid}/email-verification/chart', [MaillistController::class, 'emailVerificationChart'])->name('maillists.emailVerificationChart');
    Route::get('/lists/{uid}/clone-to-customers/choose', [MaillistController::class, 'cloneForCustomersChoose'])->name('maillists.cloneForCustomersChoose');
    Route::post('/lists/{uid}/clone-to-customers', [MaillistController::class, 'cloneForCustomers'])->name('maillists.cloneForCustomers');

    Route::get('/lists/{uid}/verification/{job_uid}/progress', [MaillistController::class, 'verificationProgress'])->name('maillists.verificationProgress');
    Route::get('/lists/{uid}/verification', [MaillistController::class, 'verification'])->name('maillists.verification');
    Route::post('/lists/{uid}/verification/start', [MaillistController::class, 'startVerification'])->name('maillists.startVerification');
    Route::post('/lists/{uid}/verification/{job_uid}/stop', [MaillistController::class, 'stopVerification'])->name('maillists.stopVerification');
    Route::post('/lists/{uid}/verification/reset', [MaillistController::class, 'resetVerification'])->name('maillists.resetVerification');

    Route::match(['get', 'post'], '/lists/copy', [MaillistController::class, 'copy'])->name('maillists.copy');
    Route::get('/lists/quick-view', [MaillistController::class, 'quickView'])->name('maillists.quickView');
    Route::get('/lists/{uid}/list-growth', [MaillistController::class, 'listGrowthChart'])->name('maillists.listGrowthChart');
    Route::get('/lists/{uid}/list-statistics-chart', [MaillistController::class, 'statisticsChart'])->name('maillists.statisticsChart');
    Route::get('/lists/sort', [MaillistController::class, 'sort'])->name('maillists.sort');
    Route::get('/lists/listing/{page?}', [MaillistController::class, 'listing'])->name('maillists.listing');
    Route::post('/lists/delete', [MaillistController::class, 'delete'])->name('maillists.delete');
    Route::get('/lists/delete/confirm', [MaillistController::class, 'deleteConfirm'])->name('maillists.delete.confirm');

    Route::get('/lists/{uid}/overview', [MaillistController::class, 'overview'])->name('maillists.overview');
    Route::get('/lists/{uid}/edit', [MaillistController::class, 'edit'])->name('maillists.lists.edit');
    Route::post('/lists/{uid}/update', [MaillistController::class, 'update'])->name('maillists.update');
    Route::match(['get', 'post'], '/lists/{uid}/embedded-form', [MaillistController::class, 'embeddedForm'])->name('maillists.embeddedForm');
    Route::get('/lists/{uid}/embedded-form-frame', [MaillistController::class, 'embeddedFormFrame'])->name('maillists.embeddedFormFrame');

    Route::post('/lists/{uid}/embedded-form-subscribe', [MaillistController::class, 'embeddedFormSubscribe'])->name('maillists.embeddedFormSubscribe');
    Route::post('/lists/{uid}/embedded-form-subscribe-captcha', [MaillistController::class, 'embeddedFormSubscribe'])->name('maillists.embeddedFormSubscribeCaptcha');

    Route::get('/lists/{uid}/check-email', [AutomationsController::class, 'checkEmail'])->name('maillists.checkEmail');

});

Route::group(['prefix' => 'automations'], function () {

    // Automation operations
    Route::post('/{uid}/trigger-all', [AutomationsController::class, 'triggerAll'])->name('automations.triggerAll');

    Route::match(['get', 'post'], 'automation/{uid}/copy', [AutomationsController::class, 'copy'])->name('automations.copy');
    Route::get('/{uid}/condition/remove', [AutomationsController::class, 'conditionRemove'])->name('automations.conditionRemove');

    // Email preheader management
    Route::post('/{uid}/template/{email_uid}/preheader/remove', [AutomationsController::class, 'emailPreheaderRemove'])->name('automations.emailPreheaderRemove');
    Route::match(['get', 'post'], 'automation/{uid}/template/{email_uid}/preheader/add', [AutomationsController::class, 'emailPreheaderAdd'])->name('automations.emailPreheaderAdd');
    Route::get('/{uid}/template/{email_uid}/preheader', [AutomationsController::class, 'emailPreheader'])->name('automations.emailPreheader');

    // Condition management
    Route::match(['get', 'post'], 'automation/condition/wait/custom', [AutomationsController::class, 'conditionWaitCustom'])->name('automations.conditionWaitCustom');
    Route::match(['get', 'post'], 'automation/{email_uid}/send-test-email', [AutomationsController::class, 'sendTestEmail'])->name('automations.send.test');

    // Cart management
    Route::get('/{uid}/cart/items', [AutomationsController::class, 'cartItems'])->name('automations.cartItems');
    Route::get('/{uid}/cart/list', [AutomationsController::class, 'cartList'])->name('automations.cartList');
    Route::get('/{uid}/cart/stats', [AutomationsController::class, 'cartStats'])->name('automations.cartStats');
    Route::match(['get', 'post'], 'automation/{uid}/cart/change-store', [AutomationsController::class, 'cartChangeStore'])->name('automations.cartChangeStore');
    Route::match(['get', 'post'], 'automation/{uid}/cart/wait', [AutomationsController::class, 'cartWait'])->name('automations.cartWait');
    Route::match(['get', 'post'], 'automation/{uid}/cart/change-list', [AutomationsController::class, 'cartChangeList'])->name('automations.cartChangeList');

    // Condition and operation management
    Route::get('/{uid}/condition/setting', [AutomationsController::class, 'conditionSetting'])->name('automations.conditionSetting');
    Route::get('/{uid}/operation/show', [AutomationsController::class, 'operationShow'])->name('automations.operationShow');
    Route::match(['get', 'post'], 'automation/{uid}/operation/edit', [AutomationsController::class, 'operationEdit'])->name('automations.operation.edit');
    Route::match(['get', 'post'], 'automation/{uid}/operation/create', [AutomationsController::class, 'operationCreate'])->name('automations.operation.create');
    Route::get('/{uid}/operation/select', [AutomationsController::class, 'operationSelect'])->name('automations.operation.select');

    // Wait time management
    Route::post('/{uid}/wait-time', [AutomationsController::class, 'waitTime'])->name('automations.waitTime.update');
    Route::get('/{uid}/wait-time', [AutomationsController::class, 'waitTime'])->name('automations.waitTime');
    Route::get('/{uid}/last-saved', [AutomationsController::class, 'lastSaved'])->name('automations.lastSaved');

    // Subscriber management
    Route::post('/{uid}/subscribers/{subscriber_uid}/restart', [AutomationsController::class, 'subscribersRestart'])->name('automations.subscribers.restart');
    Route::post('/{uid}/subscribers/{subscriber_uid}/remove', [AutomationsController::class, 'subscribersRemove'])->name('automations.subscribers.remove');
    Route::get('/{uid}/subscribers/{subscriber_uid}/show', [AutomationsController::class, 'subscribersShow'])->name('automations.subscribers.Show');
    Route::get('/{uid}/subscribers/list', [AutomationsController::class, 'subscribersList'])->name('automations.subscribers.List');
    Route::get('/{uid}/subscribers', [AutomationsController::class, 'subscribers'])->name('automations.subscribers.');

    // Automation data and settings
    Route::get('/{uid}/insight', [AutomationsController::class, 'insight'])->name('automations.insight');
    Route::post('/{uid}/data/save', [AutomationsController::class, 'saveData'])->name('automations.saveData');
    Route::post('/{uid}/update', [AutomationsController::class, 'update'])->name('automations.update');
    Route::get('/{uid}/settings', [AutomationsController::class, 'settings'])->name('automations.settings');

    // Email webhook management
    Route::match(['get', 'post'], 'automation/emails/webhooks/{webhook_uid}/test', [AutomationsController::class, 'webhooksTest'])->name('automations.webhooksTest');
    Route::get('/emails/webhooks/{webhook_uid}/sample/request', [AutomationsController::class, 'webhooksSampleRequest'])->name('automations.webhooksSampleRequest');
    Route::post('/emails/webhooks/{webhook_uid}/delete', [AutomationsController::class, 'webhooksDelete'])->name('automations.webhooksDelete');
    Route::match(['get', 'post'], 'automation/emails/webhooks/{webhook_uid}/edit', [AutomationsController::class, 'webhooksEdit'])->name('automations.webhooksEdit');
    Route::get('/emails/{email_uid}/webhooks/list', [AutomationsController::class, 'webhooksList'])->name('automations.webhooksList');
    Route::get('/emails/{email_uid}/webhooks/link-select', [AutomationsController::class, 'webhooksLinkSelect'])->name('automations.webhooksLinkSelect');
    Route::match(['get', 'post'], 'automation/emails/{email_uid}/webhooks/add', [AutomationsController::class, 'webhooksAdd'])->name('automations.webhooksAdd');
    Route::get('/emails/{email_uid}/webhooks', [AutomationsController::class, 'webhooks'])->name('automations.webhooks');

    // Automation state management
    Route::post('/disable', [AutomationsController::class, 'disable'])->name('automations.disable');
    Route::post('/enable', [AutomationsController::class, 'enable'])->name('automations.enable');
    Route::delete('/delete', [AutomationsController::class, 'delete'])->name('automations.delete');
    Route::get('/listing', [AutomationsController::class, 'listing'])->name('automations.listing');
    Route::get('/', [AutomationsController::class, 'index'])->name('automations');
    Route::get('/{uid}/debug', [AutomationsController::class, 'debug'])->name('automations.debug');
    Route::get('trigger/{id}', [AutomationsController::class, 'show'])->name('automations.show');
    Route::get('/{automation}/{subscriber}/trigger', [AutomationsController::class, 'triggerNow'])->name('automations.triggerNow');
    Route::get('/{automation}/run', [AutomationsController::class, 'run'])->name('automations.run');

});

Route::group(['prefix' => 'layouts'], function () {

    Route::get('/', [LayoutController::class, 'index'])->name('layouts');
    Route::get('/create', [LayoutController::class, 'create'])->name('layouts.create');
    Route::post('/store', [LayoutController::class, 'store'])->name('layouts.store');
    Route::patch('/update/{uid}', [LayoutController::class, 'update'])->name('layouts.update');
    Route::get('/edit/{uid}', [LayoutController::class, 'edit'])->name('layouts.edit');
    Route::get('/view/{uid}', [LayoutController::class, 'view'])->name('layouts.view');
    Route::get('/destroy/{uid}', [LayoutController::class, 'destroy'])->name('layouts.destroy');

    Route::get('/listing/{page?}', [LayoutController::class, 'listing'])->name('layouts.listing');
    Route::get('/sort', [LayoutController::class, 'sort'])->name('layouts.sort');

});
