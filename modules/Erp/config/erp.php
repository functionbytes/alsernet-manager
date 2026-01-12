<?php

return [
    'url_erp' => env('ERP_URL'),
    // Constantes para bonos
    'bono_origen_web' => 'web',
    'bono_origen_gestion' => 'gestion',
    'marcar_bono_anular' => 0,
    'marcar_bono_recargar' => 1,
    'marcar_bono_consumir' => 2,

    // Métodos de pago
    'payment_cashondelivery' => 1,
    'payment_wire' => 3,
    'payment_creditcard' => 7,
    'payment_redsys' => 22,
    'payment_bizum' => 8,
    'payment_google' => 26,
    'payment_apple' => 27,
    'payment_paypal' => 10,
    'payment_finance' => 11,
    'payment_sequra' => 100000101,
    'payment_Alsernetfinance' => 5,
    'payment_transferencia_online' => '25',
    'payment_ban_lendismart' => 28,

    'payment_bizum_tpv' => 2,
    'payment_google_tpv' => 3,
    'payment_apple_tpv' => 2,

    // =========================================
    // V2 - INTEGRACIÓN CONFIG (Nueva)
    // =========================================
    'v2' => [
        'oracle' => [
            'enabled' => env('ERP_V2_ORACLE_ENABLED', false),
            'connection' => 'oracle',
        ],

        'prestashop' => [
            'enabled' => env('ERP_V2_PRESTASHOP_ENABLED', false),
            'connection' => 'prestashop',
        ],

        'price_validation' => [
            'queue' => 'default',
            'timeout' => 300,
            'retries' => 3,
        ],

        'country_mapping' => [
            6 => 1,   // España
            15 => 2,  // Portugal
            8 => 3,   // Francia
            1 => 4,   // Alemania
            10 => 5,  // Italia
            2 => 6,   // Austria
        ],
    ],
];
