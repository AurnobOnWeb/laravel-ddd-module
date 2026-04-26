<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Contracts;

interface ModuleIntegration
{
    public function key(): string;

    public function configured(): bool;

    public function installed(): bool;

    public function active(): bool;

    /**
     * @return array<string, mixed>
     */
    public function context(): array;
}
