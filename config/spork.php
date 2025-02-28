<?php

declare (strict_types=1);
use App\Features;
return [
    'prefix' => '',
    'filesystem' => [
        'default' => env('SPORK_DEFAULT_FILESYSTEM', 'ftp'),
    ],
    'code' => [
        'enabled' => true,
        'settings' => [

            // These vendors dont always match 100% with the versions or available interfaces, likely due to missing dev dependencies.

            'blacklist' => [
                'nesbot',
                'lesstif',
                'doctrine',
                'google',
                'psy',
                'cboden',
                'symfony',
                'phpunit',
                'mockery',
                'zendframework',
                'nativephp',
                'laravel',
                'nette',
            ],
            'whitelist' => [],
        ],
    ],
    'features' => [
        Features\Automatic\Crud::class => true,
        Features\Automatic\GeneratedPages::class => true,
        Features\Automatic\ServerLinking::class => true,
        Features\Communication\Email::class => true,
        Features\Communication\Messaging::class => true,
        Features\Banking::class => true,
        Features\InfrastructureManagement::class => true,
        Features\Domains::class => true,
        Features\Feeds::class => true,
        Features\FileManager::class => true,
        Features\LinkShortening::class => true,
        Features\PetoskeyToday::class => true,
        Features\Projects::class => true,
        Features\SporkApp::class => true,
        Features\Websockets::class => true,
    ],
    'spork' => [
        'filesystem' => [
            'default' => 'local',
        ],
    ],
];
