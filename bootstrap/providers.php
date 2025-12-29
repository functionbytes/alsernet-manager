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
];
