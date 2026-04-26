<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Integration;

final class SpatieViewModelsIntegration extends AbstractIntegration
{
    public function key(): string
    {
        return 'spatie_view_models';
    }

    public function installed(): bool
    {
        return class_exists('Spatie\\ViewModels\\ViewModel');
    }

    public function context(): array
    {
        return [
            'uses_spatie_view_models' => $this->active(),
        ];
    }
}
