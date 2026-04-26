<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Integration;

final class AstrotomicTranslatableIntegration extends AbstractIntegration
{
    public function key(): string
    {
        return 'astrotomic_translatable';
    }

    public function installed(): bool
    {
        return trait_exists('Astrotomic\\Translatable\\Translatable');
    }

    public function context(): array
    {
        return [
            'uses_astrotomic_translatable' => $this->active(),
        ];
    }
}
