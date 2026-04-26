<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Module;

use Aurnob\LaravelDddModular\Contracts\ModuleRegistry as ModuleRegistryContract;
use Illuminate\Support\Str;

final class ModuleRegistry implements ModuleRegistryContract
{
    /**
     * @var array<int, Module>|null
     */
    private ?array $modules = null;

    public function __construct(
        private readonly ModuleFinder $finder,
    ) {
    }

    public function all(): array
    {
        return $this->modules ??= $this->finder->discover();
    }

    public function enabled(): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (Module $module): bool => $module->isEnabled(),
        ));
    }

    public function find(string $name): ?Module
    {
        $needle = Str::lower($name);

        foreach ($this->all() as $module) {
            if (Str::lower($module->name()) === $needle || Str::lower($module->slug()) === $needle) {
                return $module;
            }
        }

        return null;
    }

    public function has(string $name): bool
    {
        return $this->find($name) !== null;
    }

    public function refresh(): void
    {
        $this->modules = null;
    }
}
