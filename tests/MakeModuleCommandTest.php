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
}
