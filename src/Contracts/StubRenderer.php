<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Contracts;

interface StubRenderer
{
    /**
     * @param  array<string, scalar>  $replacements
     */
    public function render(string $stub, array $replacements): string;
}
