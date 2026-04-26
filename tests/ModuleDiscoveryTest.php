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
}
