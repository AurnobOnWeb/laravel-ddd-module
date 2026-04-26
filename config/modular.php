<?php

declare(strict_types=1);

return [
    'modules' => [
        'path' => base_path('Modules'),
        'namespace' => 'Modules',
        'manifest' => 'module.json',
        'default_folders' => [
            'Domain/Models',
            'Domain/QueryBuilders',
            'Application/Actions',
            'Application/Data',
            'Infrastructure/Migrations',
            'Infrastructure/Repositories',
            'Infrastructure/Providers',
            'Presentation/Http/Controllers',
            'Presentation/Http/Requests',
            'Presentation/Http/Data',
            'Presentation/Http/Resources',
            'Presentation/ViewModels',
            'Presentation/Views',
            'routes',
            'config',
            'lang',
        ],
    ],

    'discovery' => [
        'scan_depth' => 1,
        'require_manifest' => true,
    ],

    'routes' => [
        'enabled' => true,
        'path' => 'routes',
        'loading' => 'directory',
        'localized' => false,
    ],

    'migrations' => [
        'enabled' => true,
        'path' => 'Infrastructure/Migrations',
        'loading' => 'directory',
    ],

    'views' => [
        'enabled' => true,
        'path' => 'Presentation/Views',
        'namespace_strategy' => 'slug',
        'namespace_prefix' => null,
    ],

    'translations' => [
        'enabled' => true,
        'path' => 'lang',
    ],

    'config_loading' => [
        'enabled' => true,
        'path' => 'config',
        'key_prefix' => 'modular.modules',
    ],

    'stubs' => [
        'path' => null,
    ],

    'integrations' => [
        'spatie_data' => true,
        'spatie_view_models' => true,
        'astrotomic_translatable' => false,
        'laravel_localization' => false,
        'laravel_localization_middleware' => [
            'localize',
            'localizationRedirect',
            'localeViewPath',
        ],
    ],
];
