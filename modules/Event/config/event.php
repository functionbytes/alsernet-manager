<?php

return [
    'navigation' => [
        'sidebar' => [
            'insert_after' => 'campaigns',
            'section' => [
                'title' => 'Eventos',
                'icon' => 'fa-duotone fas fa-calendar',
                'permission' => 'events.view',
                'items' => [
                    [
                        'label' => 'Lista de eventos',
                        'route' => 'manager.events.index',
                        'icon' => 'fa-duotone fas fa-list',
                        'permission' => 'events.view',
                    ],
                    [
                        'label' => 'Calendario',
                        'route' => 'manager.events.calendar',
                        'icon' => 'fa-duotone fas fa-calendar-alt',
                        'permission' => 'events.view',
                    ],
                    [
                        'label' => 'Crear evento',
                        'route' => 'manager.events.create',
                        'icon' => 'fa-duotone fas fa-plus-circle',
                        'permission' => 'events.create',
                    ],
                ],
            ],
        ],
    ],

    'permissions' => [
        'events.view',
        'events.create',
        'events.edit',
        'events.delete',
    ],
];
