<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Generation;

final class ModuleFeatureContribution
{
    /**
     * @param  array<int, string>  $directories
     * @param  array<int, StubFile>  $files
     * @param  array<string, scalar>  $replacements
     * @param  array<string, mixed>  $manifest
     * @param  array<int, string>  $providerImports
     * @param  array<int, string>  $providerRegisterStatements
     * @param  array<int, string>  $providerBootStatements
     * @param  array<int, string>  $actionImports
     * @param  array<int, string>  $actionAfterStatements
     */
    public function __construct(
        private readonly array $directories = [],
        private readonly array $files = [],
        private readonly array $replacements = [],
        private readonly array $manifest = [],
        private readonly array $providerImports = [],
        private readonly array $providerRegisterStatements = [],
        private readonly array $providerBootStatements = [],
        private readonly array $actionImports = [],
        private readonly array $actionAfterStatements = [],
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function directories(): array
    {
        return $this->directories;
    }

    /**
     * @return array<int, StubFile>
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * @return array<string, scalar>
     */
    public function replacements(): array
    {
        return $this->replacements;
    }

    /**
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        return $this->manifest;
    }

    /**
     * @return array<int, string>
     */
    public function providerImports(): array
    {
        return $this->providerImports;
    }

    /**
     * @return array<int, string>
     */
    public function providerRegisterStatements(): array
    {
        return $this->providerRegisterStatements;
    }

    /**
     * @return array<int, string>
     */
    public function providerBootStatements(): array
    {
        return $this->providerBootStatements;
    }

    /**
     * @return array<int, string>
     */
    public function actionImports(): array
    {
        return $this->actionImports;
    }

    /**
     * @return array<int, string>
     */
    public function actionAfterStatements(): array
    {
        return $this->actionAfterStatements;
    }
}
