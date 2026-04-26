<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Module;

use Illuminate\Support\Str;

final class Module
{
    /**
     * @param  array<int, string>  $providers
     * @param  array<int, string>  $features
     * @param  array<string, string>  $paths
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private readonly string $name,
        private readonly string $slug,
        private readonly string $basePath,
        private readonly string $namespace,
        private readonly bool $enabled,
        private readonly array $providers,
        private readonly array $features,
        private readonly array $paths,
        private readonly array $metadata,
        private readonly int $priority = 0,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function studlyName(): string
    {
        return Str::studly($this->name);
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return array<int, string>
     */
    public function providers(): array
    {
        return $this->providers;
    }

    /**
     * @return array<int, string>
     */
    public function features(): array
    {
        return $this->features;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array(strtolower($feature), array_map('strtolower', $this->features), true);
    }

    /**
     * @return array<string, string>
     */
    public function paths(): array
    {
        return $this->paths;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function path(string $key): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.trim($this->paths[$key] ?? '', DIRECTORY_SEPARATOR);
    }

    public function routePath(): string
    {
        return $this->path('routes');
    }

    public function migrationPath(): string
    {
        return $this->path('migrations');
    }

    public function viewPath(): string
    {
        return $this->path('views');
    }

    public function translationPath(): string
    {
        return $this->path('translations');
    }

    public function configPath(): string
    {
        return $this->path('config');
    }

    public function viewNamespace(string $strategy = 'slug', ?string $prefix = null): string
    {
        return match ($strategy) {
            'studly' => $this->studlyName(),
            'prefix' => trim(($prefix ?? 'modules').'.'.$this->slug, '.'),
            default => $this->slug,
        };
    }
}
