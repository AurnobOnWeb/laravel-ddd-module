# Laravel DDD Modular

`aurnob/laravel-ddd-modular` is an opinionated modular architecture package for Laravel applications that want clear boundaries, DDD-style structure, predictable scaffolding, and optional ecosystem integrations without turning the package into a thin wrapper around another package.

It is standalone first, but it can live alongside `nwidart/laravel-modules` because it uses the same broad `Modules/` convention and does not require a hard dependency on that package.

## Table of Contents

- [Why This Package Exists](#why-this-package-exists)
- [Package Goals](#package-goals)
- [Requirements and Compatibility](#requirements-and-compatibility)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Generated Module Structure](#generated-module-structure)
- [How Module Discovery Works](#how-module-discovery-works)
- [The module.json Manifest](#the-modulejson-manifest)
- [Artisan Command](#artisan-command)
- [Module Features](#module-features)
- [Optional Integrations](#optional-integrations)
- [Configuration](#configuration)
- [Runtime Behavior](#runtime-behavior)
- [Extending the Package](#extending-the-package)
- [Working Alongside nwidart/laravel-modules](#working-alongside-nwidartlaravel-modules)
- [Testing the Package](#testing-the-package)

## Why This Package Exists

Most Laravel module packages solve discovery and folder generation, but they usually stop at generic folders. Real applications often need stronger conventions:

- domain models and query builders
- application actions and DTOs
- presentation layer controllers, requests, resources, and view models
- clear infrastructure folders
- optional API-first scaffolding
- predictable support for common Laravel ecosystem packages

This package provides those conventions directly.

## Package Goals

- Keep the core small and readable
- Make modules feel first-class, not bolted on
- Prefer composition and contracts over inheritance-heavy design
- Support Laravel 10 through 13 with broad PHP compatibility
- Support API-first teams, especially mobile and SPA backends using Sanctum cookie-based authentication
- Make future package features pluggable instead of hard-coded

## Requirements and Compatibility

### PHP

- `^8.2|^8.3|^8.4|^8.5`

Primary runtime target:

- PHP 8.5

### Laravel Components

- `illuminate/support: ^10.0|^11.0|^12.0|^13.0`
- `illuminate/console: ^10.0|^11.0|^12.0|^13.0`
- `illuminate/filesystem: ^10.0|^11.0|^12.0|^13.0`

### Official Laravel Documentation Verified Before Choosing Constraints

- Laravel package development and auto-discovery: `laravel.com/docs/13.x/packages`
- Laravel 13 upgrade path requiring `laravel/framework` `^13.0`: `laravel.com/docs/13.x/upgrade`
- Release compatibility matrix across Laravel 10, 11, 12, and 13: `laravel.com/docs/12.x/releases`

The package keeps compatibility broad unless a narrower constraint becomes technically necessary.

## Installation

Install the package:

```bash
composer require aurnob/laravel-ddd-modular
```

Publish the configuration:

```bash
php artisan vendor:publish --tag=modular-config
```

Publish the stubs if you want to customize generated files:

```bash
php artisan vendor:publish --tag=modular-stubs
```

Laravel package auto-discovery is already configured through:

```json
{
    "extra": {
        "laravel": {
            "providers": [
                "Aurnob\\LaravelDddModular\\LaravelDddModularServiceProvider"
            ]
        }
    }
}
```

## Quick Start

Generate a basic module:

```bash
php artisan modular:make Blog
```

Generate an API-focused module with testing, policies, observers, and events:

```bash
php artisan modular:make Catalog \
    --feature=api \
    --feature=testing \
    --feature=policies \
    --feature=observers \
    --feature=events
```

Generate a module while disabling a configured default feature:

```bash
php artisan modular:make Billing --without-feature=testing
```

## Generated Module Structure

The default generated structure is:

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

The default example files include:

- `module.json`
- `Infrastructure/Providers/<Module>ServiceProvider.php`
- `Domain/Models/<Module>.php`
- `Application/Actions/Create<Module>Action.php`
- `Application/Data/<Module>Data.php`
- `Presentation/Http/Controllers/<Module>Controller.php`
- `Presentation/Http/Requests/Store<Module>Request.php`
- `Presentation/Http/Resources/<Module>Resource.php`
- `Presentation/ViewModels/Show<Module>ViewModel.php`
- `routes/web.php`
- `Presentation/Views/index.blade.php`
- `config/module.php`
- an example migration

If Astrotomic Translatable integration is active, model and migration generation switch to translatable equivalents.

## How Module Discovery Works

At runtime the package:

1. Scans the configured modules directory
2. Looks for module folders containing `module.json`
3. Reads the module manifest
4. Registers only enabled modules
5. Merges module config
6. Loads module resources
7. Registers module service providers

Discovery is handled by the module finder and registry, not by generated module providers themselves.

## The module.json Manifest

Each module is described by `module.json`.

Example:

```json
{
    "name": "Catalog",
    "slug": "catalog",
    "description": "Catalog module.",
    "enabled": true,
    "namespace": "Modules\\Catalog",
    "features": [
        "api",
        "testing",
        "policies"
    ],
    "providers": [
        "Modules\\Catalog\\Infrastructure\\Providers\\CatalogServiceProvider"
    ],
    "paths": {
        "routes": "routes",
        "views": "Presentation/Views",
        "migrations": "Infrastructure/Migrations",
        "translations": "lang",
        "config": "config"
    },
    "feature_manifest": {
        "api": {
            "guard": "sanctum",
            "middleware": [
                "api",
                "auth:sanctum"
            ]
        }
    },
    "priority": 0
}
```

### Important Manifest Keys

- `name`: human-friendly module name
- `slug`: route/config friendly identifier
- `enabled`: whether the module should be booted
- `namespace`: base PHP namespace for the module
- `features`: feature keys used when the module was generated
- `providers`: module service providers to register
- `paths`: relative paths for routes, views, migrations, translations, config
- `feature_manifest`: extra metadata generated by features
- `priority`: load ordering hint

## Artisan Command

### Basic Syntax

```bash
php artisan modular:make {name}
```

### Available Options

```bash
php artisan modular:make Blog \
    --force \
    --feature=api \
    --feature=events \
    --feature=testing \
    --without-feature=testing
```

### Supported Feature Keys

- `api`
- `permissions`
- `media`
- `events`
- `jobs`
- `observers`
- `policies`
- `testing`

If an unknown feature is requested, the command fails with a clear error listing valid feature keys.

## Module Features

Features are the package’s future-proof extension point. They let scaffolding grow without turning `ModuleGenerator` into a god class.

Each feature can contribute:

- directories
- generated files
- manifest metadata
- service provider imports
- service provider boot logic
- service provider register logic
- action imports
- action post-create hooks

### `api`

Purpose:

- generate API routes and an API controller
- default to Sanctum-protected API middleware
- support mobile clients and SPA backends

Generated files:

- `Presentation/Http/Controllers/Api/<Module>ApiController.php`
- `routes/api.php`

Default API middleware:

```php
['api', 'auth:sanctum']
```

Default API URI prefix:

```text
api/<module-slug>
```

### `permissions`

Purpose:

- create a single place for module permission names
- prepare future integration with permission packages or Gate rules

Generated file:

- `Domain/Permissions/<Module>Permissions.php`

### `media`

Purpose:

- establish media collection conventions without hard-coding a media library
- prepare future integration with media packages

Generated files:

- `Domain/Media/<Module>Media.php`
- `Application/Actions/Attach<Module>MediaAction.php`

### `events`

Purpose:

- generate domain event scaffolding
- register listeners in the module provider
- dispatch an event from the generated create action

Generated files:

- `Domain/Events/<Module>Created.php`
- `Application/Listeners/Update<Module>Projection.php`

Provider behavior:

- registers the listener with `Event::listen(...)`

Action behavior:

- dispatches `event(new <Module>Created($model))`

### `jobs`

Purpose:

- create queue job presets inside the application layer

Generated file:

- `Application/Jobs/Sync<Module>SearchIndexJob.php`

### `observers`

Purpose:

- add model observer scaffolding and provider registration

Generated file:

- `Infrastructure/Observers/<Module>Observer.php`

Provider behavior:

- registers `<Module>::observe(<Module>Observer::class)`

### `policies`

Purpose:

- create authorization policy scaffolding
- register policy mappings in the module provider

Generated file:

- `Application/Policies/<Module>Policy.php`

Provider behavior:

- registers `Gate::policy(...)`

### `testing`

Purpose:

- create example module-level test folders and starter tests

Generated files:

- `tests/Feature/<Module>ApiTest.php` or `tests/Feature/<Module>WebTest.php`
- `tests/Unit/Create<Module>ActionTest.php`

The generated test files intentionally use `markTestSkipped(...)` because every application’s test bootstrapping strategy is different. They are presets, not false-positive tests.

## Optional Integrations

The package supports several optional ecosystem integrations in a package-detection-driven way.

### Spatie Laravel Data

Package:

- `spatie/laravel-data`

Behavior:

- generated DTOs extend `Spatie\LaravelData\Data`

Fallback if not installed:

- generates a native readonly DTO with `from()` and `toArray()`

### Spatie Laravel View Models

Package:

- `spatie/laravel-view-models`

Behavior:

- generated view models extend `Spatie\ViewModels\ViewModel`

Fallback if not installed:

- generates an `Arrayable` view model that still works with `view()`

### Astrotomic Translatable

Package:

- `astrotomic/laravel-translatable`

Behavior:

- generated model uses `Translatable`
- translation model is generated
- migrations split into aggregate and translation tables

### Laravel Localization

Package:

- `mcamara/laravel-localization`

Behavior:

- if enabled in config and route localization is turned on, discovered module routes are wrapped in a localized group

## Configuration

The published config file is `config/modular.php`.

### Modules

```php
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
```

Use this section to control:

- where modules live
- their base namespace
- the manifest filename
- which folders are always generated

### Discovery

```php
'discovery' => [
    'scan_depth' => 1,
    'require_manifest' => true,
],
```

Use this section to control:

- how deep the scanner searches
- whether a module must contain `module.json`

### Routes

```php
'routes' => [
    'enabled' => true,
    'path' => 'routes',
    'loading' => 'directory',
    'localized' => false,
],
```

### Migrations

```php
'migrations' => [
    'enabled' => true,
    'path' => 'Infrastructure/Migrations',
    'loading' => 'directory',
],
```

### Views

```php
'views' => [
    'enabled' => true,
    'path' => 'Presentation/Views',
    'namespace_strategy' => 'slug',
    'namespace_prefix' => null,
],
```

Supported namespace strategies:

- `slug`
- `studly`
- `prefix`

### Translations

```php
'translations' => [
    'enabled' => true,
    'path' => 'lang',
],
```

### Module Config Loading

```php
'config_loading' => [
    'enabled' => true,
    'path' => 'config',
    'key_prefix' => 'modular.modules',
],
```

Module config files are merged under keys like:

```php
config('modular.modules.blog.module');
```

### Stub Overrides

```php
'stubs' => [
    'path' => null,
],
```

If you publish or maintain custom stubs, point this path to your own stub directory.

### Feature Defaults and Feature Config

```php
'features' => [
    'defaults' => [],
    'available' => [
        'api',
        'permissions',
        'media',
        'events',
        'jobs',
        'observers',
        'policies',
        'testing',
    ],
    'api' => [
        'routes_path' => 'routes/api.php',
        'route_name_prefix' => 'api.',
        'uri_prefix' => 'api',
        'middleware' => [
            'api',
            'auth:sanctum',
        ],
    ],
    'testing' => [
        'path' => 'tests',
    ],
],
```

Recommended API-first team setup:

```php
'features' => [
    'defaults' => ['api', 'testing'],
],
```

### Integrations

```php
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
```

## Runtime Behavior

When the application boots, enabled modules are processed as follows:

1. module namespaces are registered with a runtime autoloader
2. module config files are merged
3. module service providers are registered
4. module views are loaded
5. module translations are loaded
6. module migrations are loaded
7. module route files are required

This keeps modules self-contained while the package owns the cross-cutting runtime behavior.

## Extending the Package

The package is designed so future capabilities can plug in cleanly.

### Add a New Feature

Implement:

```php
Aurnob\LaravelDddModular\Contracts\ModuleFeature
```

Return a `ModuleFeatureContribution` that can provide:

- directories
- stub-driven files
- manifest metadata
- provider register statements
- provider boot statements
- action imports
- action post-create statements

Then register the feature in the package service provider or your own application-level extension layer.

### Why Features Use Composition

Do not extend `ModuleGenerator` or `ModuleRegistrar` for every new capability. That quickly creates a maintenance problem.

Instead:

- feature classes contribute isolated pieces
- the feature manager resolves and coordinates them
- the generator merges the contributions into the final module output

That is the main extensibility model of the package.

## Working Alongside nwidart/laravel-modules

This package does not require `nwidart/laravel-modules`, but it can coexist with it if you already use its `Modules/` directory convention.

Recommended approach:

- let this package own the opinionated structure and scaffolding
- keep `module.json` as the source of truth for this package
- avoid mixing multiple independent discovery systems for the same resources unless you understand the boot order clearly

## Testing the Package

This package currently includes tests for:

- module discovery
- registry lookup
- enabled versus disabled modules
- config merge behavior
- view namespace behavior
- base command generation
- feature-driven command generation

To run tests locally after installing dependencies:

```bash
composer test
```

## Summary

Use this package if you want:

- strong module conventions
- DDD-oriented folders by default
- feature-based scaffolding
- optional API-first generation
- optional ecosystem integrations
- an architecture that stays maintainable as new module capabilities are added
