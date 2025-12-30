<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\BootMailConfigurationProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    Modules\Campaign\Providers\CampaignServiceProvider::class,
    Modules\Documents\Providers\DocumentsServiceProvider::class,
    Modules\Returns\Providers\ReturnsServiceProvider::class,
    Modules\Subscriber\Providers\SubscriberServiceProvider::class,
    Modules\Webhook\Providers\WebhookServiceProvider::class,
    Modules\Supplier\Providers\SupplierServiceProvider::class,
    Modules\Helpdesk\Providers\HelpdeskServiceProvider::class,
    Modules\Role\Providers\RoleServiceProvider::class,
    Modules\Media\Providers\MediaServiceProvider::class,
    Modules\Event\Providers\EventServiceProvider::class,
    Modules\Mail\Providers\MailServiceProvider::class,
    Modules\Faq\Providers\FaqServiceProvider::class,
    Modules\Users\Providers\UsersServiceProvider::class,
    Modules\Notification\Providers\NotificationServiceProvider::class,
    Modules\Backup\Providers\BackupServiceProvider::class,
];
