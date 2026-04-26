<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Aurnob\LaravelDddModular\Contracts\ModuleFeature;
use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;
use Aurnob\LaravelDddModular\Generation\StubFile;

final class PolicyFeature extends AbstractFeature implements ModuleFeature
{
    public function key(): string
    {
        return 'policies';
    }

    public function description(): string
    {
        return 'Generate a policy and register it in the module provider.';
    }

    public function contribute(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): ModuleFeatureContribution
    {
        $policyClass = $blueprint->studlyName().'Policy';

        return new ModuleFeatureContribution(
            directories: [
                'Application/Policies',
            ],
            files: [
                new StubFile(
                    'Application/Policies/'.$policyClass.'.php',
                    'feature-policy.stub',
                    [
                        'policy_namespace' => $blueprint->namespace.'\\Application\\Policies',
                        'policy_class' => $policyClass,
                    ],
                ),
            ],
            providerImports: [
                'use '.$blueprint->namespace.'\\Application\\Policies\\'.$policyClass.';',
                'use '.$blueprint->namespace.'\\Domain\\Models\\'.$blueprint->modelClass().';',
                'use Illuminate\\Support\\Facades\\Gate;',
            ],
            providerBootStatements: [
                'Gate::policy('.$blueprint->modelClass().'::class, '.$policyClass.'::class);',
            ],
            manifest: [
                'features' => [
                    'policies' => [
                        'registered' => [$policyClass],
                    ],
                ],
            ],
        );
    }
}
