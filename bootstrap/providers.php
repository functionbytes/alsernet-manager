<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\MenuServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    // modules\Campaign\Providers\CampaignServiceProvider::class, // DISABLED
    Modules\Document\Providers\DocumentsServiceProvider::class,
    // modules\Returns\Providers\ReturnsServiceProvider::class,
    // modules\Subscriber\Providers\SubscriberServiceProvider::class,
    // modules\Webhook\Providers\WebhookServiceProvider::class,
    // modules\Supplier\Providers\SupplierServiceProvider::class,
    // modules\Helpdesk\Providers\HelpdeskServiceProvider::class,
    Modules\Modules\Providers\ModulesServiceProvider::class,
    Modules\Warehouse\Providers\WarehouseServiceProvider::class,
    Modules\Role\Providers\RoleServiceProvider::class,
    Modules\Media\Providers\MediaServiceProvider::class,
    // modules\Event\Providers\EventServiceProvider::class,
    Modules\Mailer\Providers\MailerServiceProvider::class,
    Modules\MailsSettings\Providers\MailsSettingsServiceProvider::class,
    // modules\Faq\Providers\FaqServiceProvider::class,
    Modules\User\Providers\UserServiceProvider::class,
    Modules\Notification\Providers\NotificationServiceProvider::class,
    // modules\Backup\Providers\BackupServiceProvider::class,
];
