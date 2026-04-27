<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Module;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final class ModuleFinder
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly Repository $config,
    ) {
    }

    /**
     * @return array<int, Module>
     */
    public function discover(): array
    {
        $modulesPath = (string) $this->config->get('modular.modules.path', base_path('Modules'));
        $manifestFile = (string) $this->config->get('modular.modules.manifest', 'module.json');
        $scanDepth = max(1, (int) $this->config->get('modular.discovery.scan_depth', 1));

        if (! $this->files->isDirectory($modulesPath)) {
            return [];
        }

        $modules = [];

        foreach ($this->candidateDirectories($modulesPath, $scanDepth) as $directory) {
            $manifestPath = $directory.DIRECTORY_SEPARATOR.$manifestFile;

            if (! $this->files->exists($manifestPath)) {
                if ((bool) $this->config->get('modular.discovery.require_manifest', true)) {
                    continue;
                }

                $data = [];
            } else {
                $json = $this->files->get($manifestPath);
                $data = json_decode($json, true);

                if (! is_array($data)) {
                    throw new RuntimeException(sprintf('Module manifest [%s] could not be decoded.', $manifestPath));
                }
            }

            $directoryName = basename($directory);
            $manifest = ModuleManifest::fromArray($data, $directoryName, (array) $this->config->get('modular', []));
            $modules[] = $manifest->toModule($directory);
        }

        usort($modules, fn (Module $left, Module $right): int => $left->priority() <=> $right->priority());

        return $modules;
    }

    /**
     * @return array<int, string>
     */
    private function candidateDirectories(string $modulesPath, int $scanDepth): array
    {
        $directories = [];

        $this->scanDirectories($modulesPath, $scanDepth, 1, $directories);

        return $directories;
    }

    /**
     * @param  array<int, string>  $directories
     */
    private function scanDirectories(string $path, int $scanDepth, int $depth, array &$directories): void
    {
        foreach ($this->files->directories($path) as $directory) {
            $directories[] = $directory;

            if ($depth >= $scanDepth) {
                continue;
            }

            $this->scanDirectories($directory, $scanDepth, $depth + 1, $directories);
        }
    }
}
