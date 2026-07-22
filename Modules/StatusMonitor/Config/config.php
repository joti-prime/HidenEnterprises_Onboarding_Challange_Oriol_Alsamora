<?php

return [

    'name' => 'Status Monitor',
    'icon' => '',
    'author' => 'HCTestDash',
    'version' => '1.0.0',
    'wemx_version' => '1.0.0',

    'elements' => [

        // Link shown to logged in users
        'main_menu' => [
            [
                'name' => 'Status',
                'icon' => '<i class="fas fa-signal"></i>',
                'href' => '/statusmonitor/status',
                'style' => '',
            ],
        ],

        'user_dropdown' => [],

        // Link shown to admins
        'admin_menu' => [
            [
                'name' => 'Status Monitor',
                'icon' => '<i class="fas fa-heart-pulse"></i>',
                'href' => '/admin/statusmonitor',
                'style' => '',
            ],
        ],

    ],

];
