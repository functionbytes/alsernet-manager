<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Portal público para cargar documentación
    |--------------------------------------------------------------------------
    |
    | URL base o plantilla donde los clientes pueden cargar la documentación.
    | Puedes incluir el token {uid} para que se reemplace automáticamente con
    | el UID del documento en los correos enviados.
    |
    */
    'upload_portal_url' => env('DOCUMENTS_UPLOAD_PORTAL_URL', env('APP_URL').'/documents/{uid}'),

    /*
    |--------------------------------------------------------------------------
    | Estados pagados de Prestashop
    |--------------------------------------------------------------------------
    |
    | Lista de IDs de estados en Prestashop que se consideran "pagados".
    | Se utiliza para determinar cuándo enviar recordatorios de documentación.
    |
    */
    'paid_statuses' => array_values(array_map(
        'intval',
        array_filter(
            array_map('trim', explode(',', env('DOCUMENTS_PRESTASHOP_PAID_STATUS_IDS', '2'))),
            fn ($value) => $value !== ''
        )
    )),

    /*
    |--------------------------------------------------------------------------
    | Configuración de Navegación
    |--------------------------------------------------------------------------
    |
    | Elementos de navegación para el menú lateral del módulo de documentos.
    |
    */
    'navigation' => [
        'sidebar' => [
            'insert_after' => 'helpdesk',
            'section' => [
                'title' => 'Documentos',
                'permission' => 'documents.manage',
                'items' => [
                    [
                        'label' => 'Configuraciones',
                        'route' => 'manager.backups.documents.configurations',
                        'permission' => 'documents.manage',
                    ],
                    [
                        'label' => 'Almacenamiento',
                        'route' => 'manager.backups.documents.configurations.storage',
                        'permission' => 'documents.manage',
                    ],
                    [
                        'label' => 'Grupos de validación',
                        'route' => 'manager.backups.documents.groups.index',
                        'permission' => 'documents.manage',
                    ],
                    [
                        'label' => 'Tipos de documento',
                        'route' => 'manager.backups.documents.types',
                        'permission' => 'documents.manage',
                    ],
                    [
                        'label' => 'Condiciones de validación',
                        'route' => 'manager.backups.documents.conditions',
                        'permission' => 'documents.manage',
                    ],
                    [
                        'label' => 'Políticas SLA',
                        'route' => 'manager.backups.documents.sla-policies.index',
                        'permission' => 'documents.manage',
                    ],
                    [
                        'label' => 'Bloqueos de producto',
                        'route' => 'manager.backups.documents.blockades.index',
                        'permission' => 'documents.manage',
                    ],
                ],
            ],
        ],
    ],
];
