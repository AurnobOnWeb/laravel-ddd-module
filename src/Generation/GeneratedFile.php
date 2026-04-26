<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Generation;

final class GeneratedFile
{
    public function __construct(
        private readonly string $path,
        private readonly string $contents,
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function contents(): string
    {
        return $this->contents;
    }
}
