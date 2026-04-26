<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Illuminate\Contracts\Config\Repository;

abstract class AbstractFeature
{
    public function __construct(
        protected readonly Repository $config,
    ) {
    }
}
