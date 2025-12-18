<?php

namespace App\Facades;

use App\Services\Integrations\ErpService;
use Illuminate\Support\Facades\Facade;

class Erp extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ErpService::class;
    }

}
