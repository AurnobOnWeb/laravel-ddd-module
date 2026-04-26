<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Integration;

use Aurnob\LaravelDddModular\Contracts\ModuleIntegration;
use Illuminate\Contracts\Config\Repository;

abstract class AbstractIntegration implements ModuleIntegration
{
    public function __construct(
        protected readonly Repository $config,
    ) {
    }

    public function configured(): bool
    {
        return (bool) $this->config->get('modular.integrations.'.$this->key(), false);
    }

    public function active(): bool
    {
        return $this->configured() && $this->installed();
    }

    public function context(): array
    {
        return [
            $this->key() => $this->active(),
        ];
    }
}
