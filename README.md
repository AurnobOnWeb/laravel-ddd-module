# Laravel DDD Modular

Opinionated modular architecture for Laravel applications that want a clean `Modules/` workflow without turning the package into a thin, messy wrapper around another module system.

The package is standalone first. It follows a DDD-oriented module structure, supports module discovery and scaffolding, and can work alongside `nwidart/laravel-modules` by sharing the same broad module convention.

## Features

- DDD-style module layout
- Automatic module discovery from `Modules/*/module.json`
- Module registry with enabled/disabled filtering
- Runtime module namespace autoloading
- Resource loading for routes, views, translations, migrations, and config
- `php artisan modular:make Blog` scaffolding command
- Reusable stub system
- Optional integrations for:
  - `spatie/laravel-data`
  - `spatie/laravel-view-models`
  - `astrotomic/laravel-translatable`
  - `mcamara/laravel-localization`

## Installation

```bash
composer require aurnob/laravel-ddd-modular
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=modular-config
```

Publish the stubs if you want to customize the generated files:

```bash
php artisan vendor:publish --tag=modular-stubs
```

Laravel package auto-discovery is already configured in `composer.json` through `extra.laravel.providers`.

## Compatibility

- PHP: `^8.2|^8.3|^8.4|^8.5`
- Laravel components: `^10.0|^11.0|^12.0|^13.0`
- Primary runtime target: PHP 8.5

The package favors broad compatibility and uses stable framework APIs so the latest Laravel version remains the first-class target without dropping 10/11/12 support.

## Creating a Module

```bash
php artisan modular:make Blog
```

Generated structure:

```text
Modules/
└── Blog/
    ├── module.json
    ├── Domain/
    │   ├── Models/
    │   └── QueryBuilders/
    ├── Application/
    │   ├── Actions/
    │   └── Data/
    ├── Infrastructure/
    │   ├── Migrations/
    │   ├── Providers/
    │   └── Repositories/
    ├── Presentation/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   ├── Data/
    │   │   ├── Requests/
    │   │   └── Resources/
    │   ├── ViewModels/
    │   └── Views/
    ├── config/
    ├── lang/
    └── routes/
```

The command creates example files for:

- `module.json`
- Module service provider
- Controller
- Model
- Action
- Data object
- Request
- Resource
- View model
- Migration
- Route file
- View
- Module config

If Astrotomic Translatable support is active, the generator switches to:

- A translatable main model
- A translation model
- Main table migration
- Translation table migration

## Module Discovery

Each module is discovered from `module.json`.

Example:

```json
{
    "name": "Blog",
    "slug": "blog",
    "enabled": true,
    "namespace": "Modules\\Blog",
    "providers": [
        "Modules\\Blog\\Infrastructure\\Providers\\BlogServiceProvider"
    ],
    "paths": {
        "routes": "routes",
        "views": "Presentation/Views",
        "migrations": "Infrastructure/Migrations",
        "translations": "lang",
        "config": "config"
    }
}
```

Enabled modules are:

- Registered in the container
- Merged into config under `modular.modules.<slug>.<file>`
- Loaded for routes, views, translations, and migrations

## Example Usage

Generated module controllers follow the package conventions:

- Input goes through a request class
- Data is mapped into a DTO
- Actions work with DTOs instead of raw arrays
- Views are returned through a view model

Typical generated flow:

```php
$action->execute(BlogData::from($request->validated()));
```

## Integrations

### Spatie Data

If `spatie/laravel-data` is installed and enabled, generated DTOs extend `Spatie\LaravelData\Data`.

If it is not installed, the generator falls back to a native readonly DTO with `from()` and `toArray()` so the architectural convention still holds.

### Spatie View Models

If `spatie/laravel-view-models` is installed and enabled, generated view models extend `Spatie\ViewModels\ViewModel`.

If it is not installed, the generator falls back to an `Arrayable` view model so controllers can still pass the object directly into `view()`.

### Astrotomic Translatable

If `astrotomic/laravel-translatable` is installed and enabled, the module generator creates:

- A `Translatable` aggregate model
- A translation model
- Split migrations for main and translation tables

### Laravel Localization

If `mcamara/laravel-localization` is installed, enabled, and route localization is turned on in config, discovered module route files are wrapped in a localized route group.

## Configuration

Main configuration keys:

```php
return [
    'modules' => [
        'path' => base_path('Modules'),
        'namespace' => 'Modules',
        'manifest' => 'module.json',
        'default_folders' => [
            // ...
        ],
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
    ],
];
```

## Extensibility

Integrations are isolated behind dedicated integration classes and coordinated through `IntegrationManager`.

That makes it straightforward to add future package features such as:

- `module-api`
- `module-permissions`
- `module-media`
- `module-events`

## Testing

The package includes tests for:

- Module discovery
- Module registry lookups
- Enabled vs disabled modules
- Config merge and view namespace behavior
- Module generation command output
