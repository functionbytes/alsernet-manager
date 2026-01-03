<?php

return [
    // Core Application Providers
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\MenuServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,

    // Module Providers (Essential)
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Document\Providers\DocumentsServiceProvider::class,
    Modules\Webhook\Providers\WebhookServiceProvider::class,
    Modules\Modules\Providers\ModulesServiceProvider::class,
    Modules\Warehouse\Providers\WarehouseServiceProvider::class,
    Modules\Role\Providers\RoleServiceProvider::class,
    Modules\Media\Providers\MediaServiceProvider::class,
    Modules\Mailer\Providers\MailerServiceProvider::class,
    Modules\MailsSettings\Providers\MailsSettingsServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
    Modules\Notification\Providers\NotificationServiceProvider::class,
    Modules\Pulse\Providers\PulseServiceProvider::class,
    Modules\Database\Providers\DatabaseServiceProvider::class,
    Modules\Backup\Providers\BackupServiceProvider::class,
    Modules\Event\Providers\EventServiceProvider::class,
    Modules\Health\Providers\HealthServiceProvider::class,
    Modules\Horizon\Providers\HorizonServiceProvider::class,
    Modules\System\Providers\SystemServiceProvider::class,
    Modules\Telescope\Providers\TelescopeServiceProvider::class,

    // Disabled modules - comment out to prevent loading issues
    // Modules\Analytics\Providers\AnalyticsServiceProvider::class,
    // Modules\Prestashop\Providers\PrestashopServiceProvider::class,
    // Modules\Erp\Providers\ErpServiceProvider::class,
    // Modules\Subscriber\Providers\SubscriberServiceProvider::class,
    // Modules\Supplier\Providers\SupplierServiceProvider::class,
    // Modules\Campaign\Providers\CampaignServiceProvider::class,
    // Modules\Returns\Providers\ReturnsServiceProvider::class,
    // Modules\Helpdesk\Providers\HelpdeskServiceProvider::class,
    // Modules\Faq\Providers\FaqServiceProvider::class,
];
