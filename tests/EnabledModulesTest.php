<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Tests;

use Aurnob\LaravelDddModular\Module\ModuleRegistry;

final class EnabledModulesTest extends TestCase
{
    public function test_it_returns_only_enabled_modules(): void
    {
        $this->createModule('Blog', [
            'enabled' => true,
        ]);
        $this->createModule('Shop', [
            'enabled' => false,
        ]);

        $registry = $this->app->make(ModuleRegistry::class);
        $registry->refresh();

        self::assertCount(2, $registry->all());
        self::assertCount(1, $registry->enabled());
        self::assertSame('Blog', $registry->enabled()[0]->name());
    }
}
