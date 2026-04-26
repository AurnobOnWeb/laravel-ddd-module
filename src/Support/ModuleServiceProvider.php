<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Support;

use Aurnob\LaravelDddModular\Module\Module;
use Aurnob\LaravelDddModular\Module\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = '';

    protected function module(): ?Module
    {
        if ($this->moduleName === '') {
            return null;
        }

        return $this->app->make(ModuleRegistry::class)->find($this->moduleName);
    }

    protected function frameworkVersion(): FrameworkVersion
    {
        return $this->app->make(FrameworkVersion::class);
    }
}
