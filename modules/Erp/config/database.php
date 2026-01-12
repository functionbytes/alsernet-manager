<?php

/**
 * ERP Module - Oracle Database Configuration
 *
 * This configuration file defines the Oracle database connection
 * for the ERP module integration with GESTCENT system.
 *
 * Connection details are loaded from environment variables.
 */

return [
    'connections' => [
        'oracle' => [
            'driver' => 'oracle',
            'host' => env('ORACLE_HOST', '223.1.1.8'),
            'port' => env('ORACLE_PORT', '1521'),
            'database' => env('ORACLE_DATABASE', 'GESTCENT'),
            'service_name' => env('ORACLE_SERVICE_NAME', 'GESTCENT'),
            'username' => env('ORACLE_USERNAME', ''),
            'password' => env('ORACLE_PASSWORD', ''),
            'charset' => env('ORACLE_CHARSET', 'AL32UTF8'),
            'prefix' => '',
            'prefix_schema' => env('ORACLE_SCHEMA', 'DEVELOPER'),
            'server_version' => env('ORACLE_SERVER_VERSION', '11g'),
            'load_balance' => env('ORACLE_LOAD_BALANCE', 'yes'),
            'pooled' => true,
            'options' => [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            ],
        ],
    ],
];
