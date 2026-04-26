<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Generation;

final class StubFile
{
    /**
     * @param  array<string, scalar>  $replacements
     */
    public function __construct(
        private readonly string $path,
        private readonly string $stub,
        private readonly array $replacements = [],
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function stub(): string
    {
        return $this->stub;
    }

    /**
     * @return array<string, scalar>
     */
    public function replacements(): array
    {
        return $this->replacements;
    }
}
