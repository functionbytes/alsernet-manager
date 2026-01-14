<?php

return [
    /*
     * The URL where a cookie policy can be found.
     */
    'policy_url' => env('COOKIE_POLICY_URL', '/privacy'),

    /*
     * Cookie name for storing consent preference.
     */
    'cookie_name' => env('COOKIE_CONSENT_NAME', 'cookie_consent'),

    /*
     * Cookie lifetime in days.
     */
    'cookie_lifetime' => env('COOKIE_CONSENT_LIFETIME', 365),

    /*
     * Enable cookie consent banner.
     */
    'enabled' => env('COOKIE_CONSENT_ENABLED', true),

    /*
     * Category backups for cookie types.
     */
    'categories' => [
        'essential' => [
            'required' => true,
            'description' => 'Essential cookies are required for basic functionality and security.',
        ],
        'analytics' => [
            'required' => false,
            'description' => 'Analytics cookies help us understand how visitors use our site.',
        ],
        'marketing' => [
            'required' => false,
            'description' => 'Marketing cookies are used for advertising purposes.',
        ],
    ],
];
