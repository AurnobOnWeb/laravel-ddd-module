<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Generation;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final class StubRepository
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly Repository $config,
        private readonly string $packageStubPath,
    ) {
    }

    public function contents(string $stub): string
    {
        $overridePath = $this->config->get('modular.stubs.path');

        $paths = array_filter([
            $overridePath ? rtrim((string) $overridePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$stub : null,
            rtrim($this->packageStubPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$stub,
        ]);

        foreach ($paths as $path) {
            if ($this->files->exists($path)) {
                return $this->files->get($path);
            }
        }

        throw new RuntimeException(sprintf('Stub [%s] could not be found.', $stub));
    }
}
