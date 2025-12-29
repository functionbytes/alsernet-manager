<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Category Hierarchy Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for hierarchical category management and PrestaShop sync
    |
    */

    /**
     * Maximum depth level for category tree
     * PrestaShop typically supports up to 8 levels
     */
    'max_depth' => env('CATEGORY_MAX_DEPTH', 8),

    /**
     * Enable automatic synchronization to PrestaShop on category save
     */
    'auto_sync' => env('CATEGORY_AUTO_SYNC', false),

    /**
     * Default conflict resolution strategy
     * Options: laravel_wins, prestashop_wins, merge, manual, skip
     */
    'default_conflict_resolution' => env('CATEGORY_CONFLICT_RESOLUTION', 'manual'),

    /**
     * Sync interval in minutes (for scheduled command)
     */
    'sync_interval' => env('CATEGORY_SYNC_INTERVAL', 15),

    /**
     * Enable category tree caching
     */
    'cache_enabled' => env('CATEGORY_CACHE_ENABLED', true),

    /**
     * Cache TTL in seconds (1 hour by default)
     */
    'cache_ttl' => env('CATEGORY_CACHE_TTL', 3600),

    /**
     * Cache key prefix
     */
    'cache_prefix' => 'categories',

    /**
     * Maximum retry attempts for failed syncs
     */
    'max_retry_attempts' => 3,

    /**
     * Retry delay in seconds
     */
    'retry_delay' => 5,

    /**
     * Enable activity logging for category operations
     */
    'log_activity' => true,

    /**
     * Default visibility for new categories
     */
    'default_visibility' => 'public',

    /**
     * Default active status for new categories
     */
    'default_active' => true,

    /**
     * Root category ID in PrestaShop (usually 2)
     */
    'prestashop_root_id' => 2,

    /**
     * Allowed image formats for category images
     */
    'allowed_image_formats' => ['jpeg', 'jpg', 'png', 'gif', 'svg'],

    /**
     * Maximum image size in kilobytes
     */
    'max_image_size' => 2048,

    /**
     * Image storage disk
     */
    'image_disk' => 'public',

    /**
     * Image storage path
     */
    'image_path' => 'categories',
];
