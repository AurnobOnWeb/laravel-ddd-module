<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Aurnob\LaravelDddModular\Contracts\ModuleFeature;
use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;
use Aurnob\LaravelDddModular\Generation\StubFile;

final class ObserverFeature extends AbstractFeature implements ModuleFeature
{
    public function key(): string
    {
        return 'observers';
    }

    public function description(): string
    {
        return 'Generate a model observer and register it in the module provider.';
    }

    public function contribute(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): ModuleFeatureContribution
    {
        $observerClass = $blueprint->studlyName().'Observer';

        return new ModuleFeatureContribution(
            directories: [
                'Infrastructure/Observers',
            ],
            files: [
                new StubFile(
                    'Infrastructure/Observers/'.$observerClass.'.php',
                    'feature-observer.stub',
                    [
                        'observer_namespace' => $blueprint->namespace.'\\Infrastructure\\Observers',
                        'observer_class' => $observerClass,
                    ],
                ),
            ],
            providerImports: [
                'use '.$blueprint->namespace.'\\Domain\\Models\\'.$blueprint->modelClass().';',
                'use '.$blueprint->namespace.'\\Infrastructure\\Observers\\'.$observerClass.';',
            ],
            providerBootStatements: [
                $blueprint->modelClass().'::observe('.$observerClass.'::class);',
            ],
            manifest: [
                'features' => [
                    'observers' => [
                        'registered' => [$observerClass],
                    ],
                ],
            ],
        );
    }
}
