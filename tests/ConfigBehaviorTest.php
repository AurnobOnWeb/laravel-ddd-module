<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Tests;

use Aurnob\LaravelDddModular\LaravelDddModularServiceProvider;
use Aurnob\LaravelDddModular\Module\ModuleRegistrar;
use Aurnob\LaravelDddModular\Module\ModuleRegistry;

final class ConfigBehaviorTest extends TestCase
{
    public function test_it_merges_module_config_under_the_package_key_prefix(): void
    {
        $modulePath = $this->createModule('Blog');

        $this->files->put(
            $modulePath.DIRECTORY_SEPARATOR.'config/module.php',
            <<<'PHP'
<?php

return [
    'driver' => 'sync',
];
PHP,
        );

        $this->app->make(ModuleRegistry::class)->refresh();
        $this->app->make(ModuleRegistrar::class)->registerModules(new LaravelDddModularServiceProvider($this->app));

        self::assertSame('sync', config('modular.modules.blog.module.driver'));
    }

    public function test_it_uses_the_configured_view_namespace_strategy(): void
    {
        $this->app['config']->set('modular.views.namespace_strategy', 'prefix');
        $this->app['config']->set('modular.views.namespace_prefix', 'ddd');

        $this->createModule('Blog');

        $this->app->make(ModuleRegistry::class)->refresh();
        $this->app->make(ModuleRegistrar::class)->bootModules(new LaravelDddModularServiceProvider($this->app));

        $hints = $this->app['view']->getFinder()->getHints();

        self::assertArrayHasKey('ddd.blog', $hints);
    }
}
