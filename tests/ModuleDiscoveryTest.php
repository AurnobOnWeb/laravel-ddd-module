<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Tests;

use Aurnob\LaravelDddModular\Module\ModuleRegistry;

final class ModuleDiscoveryTest extends TestCase
{
    public function test_it_discovers_modules_from_the_modules_directory(): void
    {
        $this->createModule('Blog', [
            'features' => ['api', 'testing'],
        ]);

        $registry = $this->app->make(ModuleRegistry::class);
        $registry->refresh();

        $module = $registry->find('Blog');

        self::assertNotNull($module);
        self::assertSame('Blog', $module->name());
        self::assertSame('blog', $module->slug());
        self::assertSame($this->modulesPath.DIRECTORY_SEPARATOR.'Blog', $module->basePath());
        self::assertSame('Modules\\Blog', $module->namespace());
        self::assertTrue($module->hasFeature('api'));
        self::assertTrue($module->hasFeature('testing'));
    }

    public function test_it_discovers_nested_modules_within_configured_scan_depth(): void
    {
        $this->app['config']->set('modular.discovery.scan_depth', 2);

        $modulePath = $this->createModule('Blog');
        $nestedPath = $this->modulesPath.DIRECTORY_SEPARATOR.'Content'.DIRECTORY_SEPARATOR.'Blog';

        $this->files->ensureDirectoryExists(dirname($nestedPath));
        $this->files->moveDirectory($modulePath, $nestedPath);

        $registry = $this->app->make(ModuleRegistry::class);
        $registry->refresh();

        $module = $registry->find('Blog');

        self::assertNotNull($module);
        self::assertSame($nestedPath, $module->basePath());
    }
}
