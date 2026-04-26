<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular;

use Aurnob\LaravelDddModular\Commands\MakeModuleCommand;
use Aurnob\LaravelDddModular\Contracts\ModuleRegistry as ModuleRegistryContract;
use Aurnob\LaravelDddModular\Contracts\StubRenderer as StubRendererContract;
use Aurnob\LaravelDddModular\Generation\ModuleGenerator;
use Aurnob\LaravelDddModular\Generation\StubRepository;
use Aurnob\LaravelDddModular\Integration\AstrotomicTranslatableIntegration;
use Aurnob\LaravelDddModular\Integration\IntegrationManager;
use Aurnob\LaravelDddModular\Integration\LaravelLocalizationIntegration;
use Aurnob\LaravelDddModular\Integration\SpatieLaravelDataIntegration;
use Aurnob\LaravelDddModular\Integration\SpatieViewModelsIntegration;
use Aurnob\LaravelDddModular\Module\ModuleFinder;
use Aurnob\LaravelDddModular\Module\ModuleRegistrar;
use Aurnob\LaravelDddModular\Module\ModuleRegistry;
use Aurnob\LaravelDddModular\Support\FrameworkVersion;
use Aurnob\LaravelDddModular\Support\NamespaceAutoloader;
use Aurnob\LaravelDddModular\Support\StubRenderer;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

final class LaravelDddModularServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/modular.php', 'modular');

        $this->registerBindings();

        $this->app->make(ModuleRegistrar::class)->registerModules($this);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/modular.php' => config_path('modular.php'),
        ], 'modular-config');

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs/modular'),
        ], 'modular-stubs');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
            ]);
        }

        $this->app->make(ModuleRegistrar::class)->bootModules($this);
    }

    public function modularMergeConfigFrom(string $path, string $key): void
    {
        $this->mergeConfigFrom($path, $key);
    }

    public function modularLoadViewsFrom(string|array $path, string $namespace): void
    {
        $this->loadViewsFrom($path, $namespace);
    }

    public function modularLoadTranslationsFrom(string $path, string $namespace): void
    {
        $this->loadTranslationsFrom($path, $namespace);
    }

    public function modularLoadMigrationsFrom(string|array $paths): void
    {
        $this->loadMigrationsFrom($paths);
    }

    private function registerBindings(): void
    {
        $this->app->singleton(FrameworkVersion::class, fn (): FrameworkVersion => FrameworkVersion::fromApplication($this->app));

        $this->app->singleton(NamespaceAutoloader::class, fn ($app): NamespaceAutoloader => new NamespaceAutoloader(
            $app->make(Filesystem::class),
        ));

        $this->app->singleton(StubRepository::class, fn ($app): StubRepository => new StubRepository(
            $app->make(Filesystem::class),
            $app['config'],
            __DIR__.'/../stubs',
        ));

        $this->app->singleton(StubRenderer::class, fn (): StubRenderer => new StubRenderer());
        $this->app->alias(StubRenderer::class, StubRendererContract::class);

        $this->app->singleton(IntegrationManager::class, function ($app): IntegrationManager {
            return new IntegrationManager([
                new SpatieLaravelDataIntegration($app['config']),
                new SpatieViewModelsIntegration($app['config']),
                new AstrotomicTranslatableIntegration($app['config']),
                new LaravelLocalizationIntegration($app['config']),
            ]);
        });

        $this->app->singleton(ModuleFinder::class, fn ($app): ModuleFinder => new ModuleFinder(
            $app->make(Filesystem::class),
            $app['config'],
        ));

        $this->app->singleton(ModuleRegistry::class, fn ($app): ModuleRegistry => new ModuleRegistry(
            $app->make(ModuleFinder::class),
        ));
        $this->app->alias(ModuleRegistry::class, ModuleRegistryContract::class);

        $this->app->singleton(ModuleRegistrar::class, fn ($app): ModuleRegistrar => new ModuleRegistrar(
            $app,
            $app->make(Filesystem::class),
            $app->make(ModuleRegistry::class),
            $app->make(IntegrationManager::class),
            $app->make(NamespaceAutoloader::class),
        ));

        $this->app->singleton(ModuleGenerator::class, fn ($app): ModuleGenerator => new ModuleGenerator(
            $app->make(Filesystem::class),
            $app['config'],
            $app->make(StubRepository::class),
            $app->make(StubRendererContract::class),
            $app->make(IntegrationManager::class),
        ));
    }
}
