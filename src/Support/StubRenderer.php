<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Support;

use Aurnob\LaravelDddModular\Contracts\StubRenderer as StubRendererContract;

final class StubRenderer implements StubRendererContract
{
    public function render(string $stub, array $replacements): string
    {
        $normalized = [];

        foreach ($replacements as $key => $value) {
            $normalized['{{ '.$key.' }}'] = (string) $value;
        }

        return strtr($stub, $normalized);
    }
}
