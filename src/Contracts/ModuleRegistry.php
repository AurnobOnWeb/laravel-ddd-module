<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Contracts;

use Aurnob\LaravelDddModular\Module\Module;

interface ModuleRegistry
{
    /**
     * @return array<int, Module>
     */
    public function all(): array;

    /**
     * @return array<int, Module>
     */
    public function enabled(): array;

    public function find(string $name): ?Module;

    public function has(string $name): bool;

    public function refresh(): void;
}
