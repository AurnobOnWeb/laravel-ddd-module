<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Integration;

final class SpatieLaravelDataIntegration extends AbstractIntegration
{
    public function key(): string
    {
        return 'spatie_data';
    }

    public function installed(): bool
    {
        return class_exists('Spatie\\LaravelData\\Data');
    }

    public function context(): array
    {
        return [
            'uses_spatie_data' => $this->active(),
        ];
    }
}
