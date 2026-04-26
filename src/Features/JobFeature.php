<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Aurnob\LaravelDddModular\Contracts\ModuleFeature;
use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;
use Aurnob\LaravelDddModular\Generation\StubFile;

final class JobFeature extends AbstractFeature implements ModuleFeature
{
    public function key(): string
    {
        return 'jobs';
    }

    public function description(): string
    {
        return 'Generate a queue job skeleton for the module.';
    }

    public function contribute(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): ModuleFeatureContribution
    {
        return new ModuleFeatureContribution(
            directories: [
                'Application/Jobs',
            ],
            files: [
                new StubFile(
                    'Application/Jobs/Sync'.$blueprint->studlyName().'SearchIndexJob.php',
                    'feature-job.stub',
                    [
                        'job_namespace' => $blueprint->namespace.'\\Application\\Jobs',
                        'job_class' => 'Sync'.$blueprint->studlyName().'SearchIndexJob',
                    ],
                ),
            ],
            manifest: [
                'features' => [
                    'jobs' => [
                        'queued' => ['Sync'.$blueprint->studlyName().'SearchIndexJob'],
                    ],
                ],
            ],
        );
    }
}
