<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\BootMailConfigurationProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    Modules\Documents\Providers\DocumentsServiceProvider::class,
    Modules\Warehouse\Providers\WarehouseServiceProvider::class,
    Modules\Returns\Providers\ReturnsServiceProvider::class,
    Modules\Mail\Providers\MailServiceProvider::class,
];
