<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Tests;

use Aurnob\LaravelDddModular\Module\ModuleRegistry;

final class ModuleRegistryTest extends TestCase
{
    public function test_it_finds_modules_by_name_and_slug(): void
    {
        $this->createModule('Blog');
        $this->createModule('Content', [
            'slug' => 'content-hub',
        ]);

        $registry = $this->app->make(ModuleRegistry::class);
        $registry->refresh();

        self::assertTrue($registry->has('Blog'));
        self::assertTrue($registry->has('content-hub'));
        self::assertSame('Content', $registry->find('content-hub')?->name());
    }
}
