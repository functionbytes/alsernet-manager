<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Warehouse Default Settings
    |--------------------------------------------------------------------------
    */
    'default_warehouse_width_m' => env('WAREHOUSE_DEFAULT_WIDTH_M', 42.23),
    'default_warehouse_height_m' => env('WAREHOUSE_DEFAULT_HEIGHT_M', 30.26),

    /*
    |--------------------------------------------------------------------------
    | Permission Configuration
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'manage' => 'warehouse.manage',
        'inventory' => 'warehouse.inventory',
        'transfer' => 'warehouse.transfer',
        'reports' => 'warehouse.reports',
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation Configuration
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        'sidebar' => [
            'insert_after' => 'shops',
            'section' => [
                'title' => 'Almacenes',
                'permission' => 'warehouse.manage',
                'items' => [
                    [
                        'label' => 'Almacenes',
                        'route' => 'settings.warehouse.index',
                        'permission' => 'warehouse.manage',
                        'icon' => 'fa-duotone fas fa-warehouse',
                    ],
                    [
                        'label' => 'Pisos',
                        'route' => 'settings.warehouse.floors',
                        'permission' => 'warehouse.manage',
                        'icon' => 'fa-duotone fas fa-layer-group',
                    ],
                    [
                        'label' => 'Estilos de ubicación',
                        'route' => 'settings.warehouse.styles.index',
                        'permission' => 'warehouse.manage',
                        'icon' => 'fa-duotone fas fa-paint-brush',
                    ],
                    [
                        'label' => 'Historial de movimientos',
                        'route' => 'settings.warehouse.history',
                        'permission' => 'warehouse.reports',
                        'icon' => 'fa-duotone fas fa-history',
                    ],
                    [
                        'label' => 'Reportes',
                        'route' => 'settings.warehouse.reports',
                        'permission' => 'warehouse.reports',
                        'icon' => 'fa-duotone fas fa-chart-bar',
                    ],
                ],
            ],
        ],
        'warehouse_worker_sidebar' => [
            'section' => [
                'title' => 'Almacén',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'route' => 'warehouse.dashboard',
                        'icon' => 'fa-duotone fas fa-tachometer-alt',
                    ],
                    [
                        'label' => 'Almacenes',
                        'route' => 'warehouse.warehouses.index',
                        'icon' => 'fa-duotone fas fa-boxes',
                    ],
                    [
                        'label' => 'Transferencias',
                        'route' => 'warehouse.transfer.index',
                        'icon' => 'fa-duotone fas fa-exchange-alt',
                    ],
                    [
                        'label' => 'Ubicaciones',
                        'route' => 'warehouse.locations.index',
                        'icon' => 'fa-duotone fas fa-map-marked',
                    ],
                ],
            ],
        ],
    ],
];
