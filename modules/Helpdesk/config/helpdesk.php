<?php

return [
    'navigation' => [
        'manager_settings' => [
            'icon' => 'fas fa-headset',
            'title' => 'Helpdesk',
            'route' => 'manager.backups.helpdesk.index',
            'permission' => 'manage_helpdesk_settings',
        ],
        'agent_menu' => [
            'icon' => 'fas fa-ticket-alt',
            'title' => 'Helpdesk',
            'route' => 'helpdesk.dashboard',
            'permission' => 'access_helpdesk',
        ],
    ],

    'ticket_number_format' => 'TKT-{year}-{sequence}',
    'ticket_number_padding' => 5,

    'auto_assignment' => [
        'enabled' => false,
        'strategy' => 'round_robin',
    ],

    'sla' => [
        'business_hours' => [
            'enabled' => true,
            'timezone' => 'America/New_York',
            'days' => [1, 2, 3, 4, 5],
            'start' => '09:00',
            'end' => '17:00',
        ],
        'warning_threshold_percent' => 80,
    ],

    'attachments' => [
        'max_size' => 10240,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'txt', 'zip'],
        'disk' => 'local',
        'path' => 'helpdesk/attachments',
    ],

    'cleanup' => [
        'enabled' => true,
        'closed_tickets_after_days' => 90,
    ],

    'notifications' => [
        'customer_on_create' => true,
        'customer_on_close' => true,
        'agent_on_assign' => true,
        'agent_on_sla_warning' => true,
    ],
];
