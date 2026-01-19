<?php

use Modules\Theme\Helpers\ThemeAssetHelper;

if (!function_exists('themeAsset')) {
    /**
     * Get URL to a theme asset
     *
     * @param string $path Path relative to modules/Theme/public/theme/
     * @return string Full URL to the asset
     *
     * @example themeAsset('libs/select2/dist/css/select2.min.css')
     *          themeAsset('css/style.css')
     *          themeAsset('js/theme/app.min.js')
     */
    function themeAsset(string $path): string
    {
        return ThemeAssetHelper::url($path);
    }
}

if (!function_exists('themeAssets')) {
    /**
     * Get multiple theme asset URLs
     *
     * @param array $paths Array of paths relative to modules/Theme/public/theme/
     * @return array Array of URLs
     *
     * @example themeAssets([
     *     'js/theme/app.init.js',
     *     'js/theme/app.min.js',
     *     'css/style.css'
     * ])
     */
    function themeAssets(array $paths): array
    {
        return ThemeAssetHelper::urls($paths);
    }
}

if (!function_exists('themeAssetPath')) {
    /**
     * Get file system path to a theme asset (for file operations)
     *
     * @param string $path Path relative to modules/Theme/public/theme/
     * @return string Full file system path
     */
    function themeAssetPath(string $path): string
    {
        return ThemeAssetHelper::path($path);
    }
}

if (!function_exists('themeAssetExists')) {
    /**
     * Check if a theme asset exists
     *
     * @param string $path Path relative to modules/Theme/public/theme/
     * @return bool
     */
    function themeAssetExists(string $path): bool
    {
        return ThemeAssetHelper::exists($path);
    }
}
