<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Support;

use Illuminate\Filesystem\Filesystem;

final class NamespaceAutoloader
{
    /**
     * @var array<string, string>
     */
    private array $prefixes = [];

    private bool $registered = false;

    public function __construct(
        private readonly Filesystem $files,
    ) {
    }

    public function registerNamespace(string $prefix, string $path): void
    {
        $normalizedPrefix = trim($prefix, '\\').'\\';
        $normalizedPath = rtrim($path, DIRECTORY_SEPARATOR);

        $this->prefixes[$normalizedPrefix] = $normalizedPath;

        if (! $this->registered) {
            spl_autoload_register($this->autoload(...));
            $this->registered = true;
        }
    }

    private function autoload(string $class): void
    {
        foreach ($this->prefixes as $prefix => $path) {
            if (! str_starts_with($class, $prefix)) {
                continue;
            }

            $relativeClass = substr($class, strlen($prefix));
            $file = $path.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass).'.php';

            if ($this->files->exists($file)) {
                require_once $file;
            }
        }
    }
}
