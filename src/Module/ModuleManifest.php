<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Module;

use Illuminate\Support\Str;

final class ModuleManifest
{
    /**
     * @param  array<int, string>  $providers
     * @param  array<string, string>  $paths
     * @param  array<string, mixed>  $metadata
     */
    private function __construct(
        private readonly string $name,
        private readonly string $slug,
        private readonly string $namespace,
        private readonly bool $enabled,
        private readonly array $providers,
        private readonly array $paths,
        private readonly array $metadata,
        private readonly int $priority,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $data, string $directoryName, array $config): self
    {
        $name = (string) ($data['name'] ?? Str::studly($directoryName));
        $studlyName = Str::studly($name);
        $namespace = (string) ($data['namespace'] ?? trim((string) data_get($config, 'modules.namespace', 'Modules'), '\\').'\\'.$studlyName);
        $slug = (string) ($data['slug'] ?? Str::of($name)->kebab()->value());
        $paths = array_merge([
            'routes' => (string) data_get($config, 'routes.path', 'routes'),
            'views' => (string) data_get($config, 'views.path', 'Presentation/Views'),
            'migrations' => (string) data_get($config, 'migrations.path', 'Infrastructure/Migrations'),
            'translations' => (string) data_get($config, 'translations.path', 'lang'),
            'config' => (string) data_get($config, 'config_loading.path', 'config'),
        ], (array) ($data['paths'] ?? []));

        $providers = array_values((array) ($data['providers'] ?? [
            $namespace.'\\Infrastructure\\Providers\\'.$studlyName.'ServiceProvider',
        ]));

        return new self(
            $name,
            $slug,
            $namespace,
            (bool) ($data['enabled'] ?? true),
            $providers,
            $paths,
            $data,
            (int) ($data['priority'] ?? 0),
        );
    }

    public function toModule(string $basePath): Module
    {
        return new Module(
            $this->name,
            $this->slug,
            $basePath,
            $this->namespace,
            $this->enabled,
            $this->providers,
            $this->paths,
            $this->metadata,
            $this->priority,
        );
    }
}
