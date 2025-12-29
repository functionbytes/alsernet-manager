<?php

namespace Modules\Campaign\Library\Facades;

use app\Library\HookManager;
use Illuminate\Support\Facades\Facade;

class Hook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return HookManager::class;
    }
}
