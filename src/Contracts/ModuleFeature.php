<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Contracts;

use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;

interface ModuleFeature
{
    public function key(): string;

    public function description(): string;

    /**
     * @param  array<int, string>  $selectedFeatures
     * @param  array<string, mixed>  $context
     */
    public function contribute(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): ModuleFeatureContribution;
}
