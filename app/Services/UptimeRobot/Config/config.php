<?php

return [

    'name' => 'UptimeRobot Module',
    'icon' => 'https://uptimerobot.com/assets/images/uptimerobot-logo.svg',
    'author' => 'HCTestDash',
    'version' => '1.0.0',
    'wemx_version' => '1.4.0',

    'service' => \App\Services\UptimeRobot\Service::class,
    'controller' => \App\Services\UptimeRobot\Http\Controllers\UptimeRobotController::class,

    'handlers' => [
        'new_order' => \App\Services\UptimeRobot\Handlers\NewOrder::class,
        'renewal' => \App\Services\UptimeRobot\Handlers\Renewal::class,
        'cancel' => \App\Services\UptimeRobot\Handlers\Cancel::class,
    ],

];
