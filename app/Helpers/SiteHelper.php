<?php

// Compatibility layer - routes to module implementation
use Modules\Core\Helpers\SiteHelper as CoreSiteHelper;

if (! function_exists('getSiteName')) {
    function getSiteName(): string
    {
        return CoreSiteHelper::getSiteName();
    }
}

if (! function_exists('getSiteKeyword')) {
    function getSiteKeyword(): string
    {
        return CoreSiteHelper::getSiteKeyword();
    }
}

if (! function_exists('getSiteTitle')) {
    function getSiteTitle(): string
    {
        return CoreSiteHelper::getSiteTitle();
    }
}
