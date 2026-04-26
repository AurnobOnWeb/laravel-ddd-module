<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Tests;

final class MakeModuleCommandTest extends TestCase
{
    public function test_it_generates_the_opinionated_module_structure(): void
    {
        $this->app['config']->set('modular.modules.namespace', 'Company\\Modules');

        $this->artisan('modular:make', ['name' => 'Blog'])
            ->assertExitCode(0);

        self::assertFileExists($this->modulesPath.'/Blog/module.json');
        self::assertFileExists($this->modulesPath.'/Blog/Infrastructure/Providers/BlogServiceProvider.php');
        self::assertFileExists($this->modulesPath.'/Blog/Domain/Models/Blog.php');
        self::assertFileExists($this->modulesPath.'/Blog/Application/Data/BlogData.php');
        self::assertFileExists($this->modulesPath.'/Blog/Application/Actions/CreateBlogAction.php');
        self::assertFileExists($this->modulesPath.'/Blog/Presentation/Http/Controllers/BlogController.php');
        self::assertFileExists($this->modulesPath.'/Blog/Presentation/ViewModels/ShowBlogViewModel.php');
        self::assertFileExists($this->modulesPath.'/Blog/Presentation/Views/index.blade.php');
        self::assertFileExists($this->modulesPath.'/Blog/routes/web.php');

        $manifest = file_get_contents($this->modulesPath.'/Blog/module.json');

        self::assertIsString($manifest);
        self::assertStringContainsString('Company\\\\Modules\\\\Blog', $manifest);
    }

    public function test_it_generates_selected_module_features(): void
    {
        $this->artisan('modular:make', [
            'name' => 'Catalog',
            '--feature' => ['api', 'events', 'policies', 'observers', 'testing'],
        ])->assertExitCode(0);

        self::assertFileExists($this->modulesPath.'/Catalog/Presentation/Http/Controllers/Api/CatalogApiController.php');
        self::assertFileExists($this->modulesPath.'/Catalog/routes/api.php');
        self::assertFileExists($this->modulesPath.'/Catalog/Domain/Events/CatalogCreated.php');
        self::assertFileExists($this->modulesPath.'/Catalog/Application/Policies/CatalogPolicy.php');
        self::assertFileExists($this->modulesPath.'/Catalog/Infrastructure/Observers/CatalogObserver.php');
        self::assertFileExists($this->modulesPath.'/Catalog/tests/Feature/CatalogApiTest.php');
        self::assertFileExists($this->modulesPath.'/Catalog/tests/Unit/CreateCatalogActionTest.php');

        $provider = file_get_contents($this->modulesPath.'/Catalog/Infrastructure/Providers/CatalogServiceProvider.php');
        $manifest = file_get_contents($this->modulesPath.'/Catalog/module.json');
        $action = file_get_contents($this->modulesPath.'/Catalog/Application/Actions/CreateCatalogAction.php');

        self::assertIsString($provider);
        self::assertIsString($manifest);
        self::assertIsString($action);
        self::assertStringContainsString('Gate::policy(Catalog::class, CatalogPolicy::class);', $provider);
        self::assertStringContainsString('Catalog::observe(CatalogObserver::class);', $provider);
        self::assertStringContainsString('"features": [', $manifest);
        self::assertStringContainsString('"api"', $manifest);
        self::assertStringContainsString('event(new CatalogCreated($model));', $action);
    }
}
