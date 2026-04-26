<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Aurnob\LaravelDddModular\Contracts\ModuleFeature;
use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;
use Aurnob\LaravelDddModular\Generation\StubFile;

final class MediaFeature extends AbstractFeature implements ModuleFeature
{
    public function key(): string
    {
        return 'media';
    }

    public function description(): string
    {
        return 'Generate module media abstractions and an attach-media action.';
    }

    public function contribute(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): ModuleFeatureContribution
    {
        return new ModuleFeatureContribution(
            directories: [
                'Domain/Media',
            ],
            files: [
                new StubFile(
                    'Domain/Media/'.$blueprint->studlyName().'Media.php',
                    'feature-media.stub',
                    [
                        'media_namespace' => $blueprint->namespace.'\\Domain\\Media',
                        'media_class' => $blueprint->studlyName().'Media',
                    ],
                ),
                new StubFile(
                    'Application/Actions/Attach'.$blueprint->studlyName().'MediaAction.php',
                    'feature-attach-media-action.stub',
                    [
                        'media_action_namespace' => $blueprint->namespace.'\\Application\\Actions',
                        'media_action_class' => 'Attach'.$blueprint->studlyName().'MediaAction',
                        'media_namespace' => $blueprint->namespace.'\\Domain\\Media',
                        'media_class' => $blueprint->studlyName().'Media',
                    ],
                ),
            ],
            manifest: [
                'features' => [
                    'media' => [
                        'collections' => ['default', 'gallery'],
                    ],
                ],
            ],
        );
    }
}
