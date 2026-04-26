<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Generation;

use Illuminate\Support\Str;

final class ModuleBlueprint
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $namespace,
        public readonly string $basePath,
    ) {
    }

    public static function from(string $name, string $baseNamespace, string $modulesPath): self
    {
        $studlyName = Str::studly($name);

        return new self(
            $studlyName,
            Str::of($studlyName)->kebab()->value(),
            trim($baseNamespace, '\\').'\\'.$studlyName,
            rtrim($modulesPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$studlyName,
        );
    }

    public function studlyName(): string
    {
        return $this->name;
    }

    public function pluralSlug(): string
    {
        return Str::plural($this->slug);
    }

    public function table(): string
    {
        return Str::snake(Str::pluralStudly($this->name));
    }

    public function translationTable(): string
    {
        return Str::snake($this->name).'_translations';
    }

    public function modelClass(): string
    {
        return $this->studlyName();
    }

    public function translationModelClass(): string
    {
        return $this->studlyName().'Translation';
    }

    public function controllerClass(): string
    {
        return $this->studlyName().'Controller';
    }

    public function actionClass(): string
    {
        return 'Create'.$this->studlyName().'Action';
    }

    public function dataClass(): string
    {
        return $this->studlyName().'Data';
    }

    public function requestClass(): string
    {
        return 'Store'.$this->studlyName().'Request';
    }

    public function resourceClass(): string
    {
        return $this->studlyName().'Resource';
    }

    public function viewModelClass(): string
    {
        return 'Show'.$this->studlyName().'ViewModel';
    }

    public function providerClass(): string
    {
        return $this->studlyName().'ServiceProvider';
    }

    public function providerNamespace(): string
    {
        return $this->namespace.'\\Infrastructure\\Providers';
    }

    public function providerFqcn(): string
    {
        return $this->providerNamespace().'\\'.$this->providerClass();
    }

    public function path(string $relativePath): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    }
}
