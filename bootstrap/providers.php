<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\BootMailConfigurationProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    Modules\Documents\Providers\DocumentsServiceProvider::class,
    Modules\Mail\Providers\MailServiceProvider::class,
];
