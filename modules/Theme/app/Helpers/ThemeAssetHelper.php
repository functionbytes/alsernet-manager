<?php

namespace Modules\Theme\Helpers;

use Illuminate\Support\Facades\File;

class ThemeAssetHelper
{
    /**
     * Base path to theme assets in the module
     */
    private static string $basePath = '/modules/Theme/public/theme/';

    /**
     * Get URL to a single theme asset
     *
     * @param string $path Path relative to modules/Theme/public/theme/
     * @return string Full URL to the asset
     *
     * @example
     *   themeAsset('js/theme/app.min.js')
     *   themeAsset('css/style.css')
     */
    public static function url(string $path): string
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');

        // Check if asset exists in module public directory
        $modulePath = base_path('modules/Theme/public/theme/' . $path);

        // If exists in module, serve from there
        if (File::exists($modulePath)) {
            return asset(self::$basePath . $path);
        }

        // Fallback to published assets in /public/theme
        return asset('theme/' . $path);
    }

    /**
     * Get URLs for multiple theme assets
     *
     * @param array $paths Array of paths relative to modules/Theme/public/theme/
     * @return array Array of full URLs
     *
     * @example
     *   themeAssets([
     *       'js/theme/app.init.js',
     *       'js/theme/app.min.js',
     *       'js/theme/sidebarmenu.js'
     *   ])
     */
    public static function urls(array $paths): array
    {
        return array_map(fn($path) => self::url($path), $paths);
    }

    /**
     * Get theme asset path (for file operations, not URLs)
     *
     * @param string $path Path relative to modules/Theme/public/theme/
     * @return string Full file system path
     */
    public static function path(string $path): string
    {
        return base_path('modules/Theme/public/theme/' . ltrim($path, '/'));
    }

    /**
     * Check if a theme asset exists
     *
     * @param string $path Path relative to modules/Theme/public/theme/
     * @return bool
     */
    public static function exists(string $path): bool
    {
        return File::exists(self::path($path));
    }
}
