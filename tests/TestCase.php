<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Tests;

use Aurnob\LaravelDddModular\LaravelDddModularServiceProvider;
use Aurnob\LaravelDddModular\Module\ModuleRegistry;
use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected string $modulesPath;

    protected Filesystem $files;

    protected function setUp(): void
    {
        $this->files = new Filesystem();
        $this->modulesPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-ddd-modular-tests'.DIRECTORY_SEPARATOR.'Modules';

        $this->files->deleteDirectory(dirname($this->modulesPath));
        $this->files->ensureDirectoryExists($this->modulesPath);

        parent::setUp();

        $this->app['config']->set('modular.modules.path', $this->modulesPath);
        $this->app->make(ModuleRegistry::class)->refresh();
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory(dirname($this->modulesPath));

        parent::tearDown();
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDddModularServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('modular.modules.path', sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-ddd-modular-tests'.DIRECTORY_SEPARATOR.'Modules');
        $app['config']->set('modular.integrations.spatie_data', false);
        $app['config']->set('modular.integrations.spatie_view_models', false);
        $app['config']->set('modular.integrations.astrotomic_translatable', false);
        $app['config']->set('modular.integrations.laravel_localization', false);
        $app['config']->set('modular.routes.localized', false);
    }

    /**
     * @param  array<string, mixed>  $manifestOverrides
     */
    protected function createModule(string $name, array $manifestOverrides = []): string
    {
        $modulePath = $this->modulesPath.DIRECTORY_SEPARATOR.$name;

        $this->files->ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'config');
        $this->files->ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'routes');
        $this->files->ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'Presentation/Views');
        $this->files->ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'Infrastructure/Migrations');
        $this->files->ensureDirectoryExists($modulePath.DIRECTORY_SEPARATOR.'lang');

        $manifest = array_replace_recursive([
            'name' => $name,
            'slug' => strtolower($name),
            'enabled' => true,
            'namespace' => 'Modules\\'.$name,
            'providers' => [],
            'paths' => [
                'routes' => 'routes',
                'views' => 'Presentation/Views',
                'migrations' => 'Infrastructure/Migrations',
                'translations' => 'lang',
                'config' => 'config',
            ],
        ], $manifestOverrides);

        $this->files->put(
            $modulePath.DIRECTORY_SEPARATOR.'module.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );

        return $modulePath;
    }
}
