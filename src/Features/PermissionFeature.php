<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Aurnob\LaravelDddModular\Contracts\ModuleFeature;
use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;
use Aurnob\LaravelDddModular\Generation\StubFile;

final class PermissionFeature extends AbstractFeature implements ModuleFeature
{
    public function key(): string
    {
        return 'permissions';
    }

    public function description(): string
    {
        return 'Generate a module permission definition class.';
    }

    public function contribute(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): ModuleFeatureContribution
    {
        return new ModuleFeatureContribution(
            directories: [
                'Domain/Permissions',
            ],
            files: [
                new StubFile(
                    'Domain/Permissions/'.$blueprint->studlyName().'Permissions.php',
                    'feature-permissions.stub',
                    [
                        'permissions_namespace' => $blueprint->namespace.'\\Domain\\Permissions',
                        'permissions_class' => $blueprint->studlyName().'Permissions',
                    ],
                ),
            ],
            manifest: [
                'features' => [
                    'permissions' => [
                        'source' => $blueprint->namespace.'\\Domain\\Permissions\\'.$blueprint->studlyName().'Permissions',
                    ],
                ],
            ],
        );
    }
}
