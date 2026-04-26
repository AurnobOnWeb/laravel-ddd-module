<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Module;

use Aurnob\LaravelDddModular\LaravelDddModularServiceProvider;
use Aurnob\LaravelDddModular\Integration\IntegrationManager;
use Aurnob\LaravelDddModular\Support\NamespaceAutoloader;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use RuntimeException;

final class ModuleRegistrar
{
    public function __construct(
        private readonly Application $app,
        private readonly Filesystem $files,
        private readonly ModuleRegistry $registry,
        private readonly IntegrationManager $integrations,
        private readonly NamespaceAutoloader $autoloader,
    ) {
    }

    public function registerModules(LaravelDddModularServiceProvider $provider): void
    {
        $this->registerNamespaces();

        foreach ($this->registry->enabled() as $module) {
            $this->mergeModuleConfig($provider, $module);
        }

        foreach ($this->registry->enabled() as $module) {
            foreach ($module->providers() as $providerClass) {
                $this->registerProvider($providerClass);
            }
        }
    }

    public function bootModules(LaravelDddModularServiceProvider $provider): void
    {
        foreach ($this->registry->enabled() as $module) {
            $this->loadViews($provider, $module);
            $this->loadTranslations($provider, $module);
            $this->loadMigrations($provider, $module);
            $this->loadRoutes($module);
        }
    }

    private function registerNamespaces(): void
    {
        $config = $this->config();
        $baseNamespace = (string) $config->get('modular.modules.namespace', 'Modules');
        $basePath = (string) $config->get('modular.modules.path', base_path('Modules'));

        $this->autoloader->registerNamespace($baseNamespace, $basePath);

        foreach ($this->registry->all() as $module) {
            $this->autoloader->registerNamespace($module->namespace(), $module->basePath());
        }
    }

    private function mergeModuleConfig(LaravelDddModularServiceProvider $provider, Module $module): void
    {
        if (! $this->config()->get('modular.config_loading.enabled', true)) {
            return;
        }

        $configPath = $module->configPath();

        if (! $this->files->isDirectory($configPath)) {
            return;
        }

        $prefix = trim((string) $this->config()->get('modular.config_loading.key_prefix', 'modular.modules'), '.');

        foreach ($this->files->files($configPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $key = trim($prefix.'.'.$module->slug().'.'.$file->getFilenameWithoutExtension(), '.');
            $provider->modularMergeConfigFrom($file->getRealPath(), $key);
        }
    }

    private function loadViews(LaravelDddModularServiceProvider $provider, Module $module): void
    {
        if (! $this->config()->get('modular.views.enabled', true)) {
            return;
        }

        $path = $module->viewPath();

        if (! $this->files->isDirectory($path)) {
            return;
        }

        $provider->modularLoadViewsFrom(
            $path,
            $module->viewNamespace(
                (string) $this->config()->get('modular.views.namespace_strategy', 'slug'),
                $this->config()->get('modular.views.namespace_prefix'),
            ),
        );
    }

    private function loadTranslations(LaravelDddModularServiceProvider $provider, Module $module): void
    {
        if (! $this->config()->get('modular.translations.enabled', true)) {
            return;
        }

        $path = $module->translationPath();

        if ($this->files->isDirectory($path)) {
            $provider->modularLoadTranslationsFrom($path, $module->slug());
        }
    }

    private function loadMigrations(LaravelDddModularServiceProvider $provider, Module $module): void
    {
        if (! $this->config()->get('modular.migrations.enabled', true)) {
            return;
        }

        $path = $module->migrationPath();

        if ($this->files->isDirectory($path)) {
            $provider->modularLoadMigrationsFrom($path);
        }
    }

    private function loadRoutes(Module $module): void
    {
        if (! $this->config()->get('modular.routes.enabled', true)) {
            return;
        }

        if (method_exists($this->app, 'routesAreCached') && $this->app->routesAreCached()) {
            return;
        }

        $routePath = $module->routePath();

        if (! $this->files->isDirectory($routePath)) {
            return;
        }

        foreach ($this->files->files($routePath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $this->requireRouteFile($file->getRealPath());
        }
    }

    private function requireRouteFile(string $path): void
    {
        if (
            $this->config()->get('modular.routes.localized', false)
            && $this->integrations->usesLocalizedRoutes()
        ) {
            Route::group($this->integrations->localizedRouteAttributes(), static function () use ($path): void {
                require $path;
            });

            return;
        }

        require $path;
    }

    private function registerProvider(string $providerClass): void
    {
        if (! class_exists($providerClass)) {
            throw new RuntimeException(sprintf('Module service provider [%s] could not be autoloaded.', $providerClass));
        }

        $this->app->register($providerClass);
    }

    private function config(): Repository
    {
        return $this->app['config'];
    }
}
