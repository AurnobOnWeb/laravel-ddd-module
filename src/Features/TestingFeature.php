<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Aurnob\LaravelDddModular\Contracts\ModuleFeature;
use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;
use Aurnob\LaravelDddModular\Generation\StubFile;

final class TestingFeature extends AbstractFeature implements ModuleFeature
{
    public function key(): string
    {
        return 'testing';
    }

    public function description(): string
    {
        return 'Generate unit and feature test presets inside the module.';
    }

    public function contribute(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): ModuleFeatureContribution
    {
        $testsPath = trim((string) $this->config->get('modular.features.testing.path', 'tests'), '/');
        $isApi = in_array('api', $selectedFeatures, true);

        return new ModuleFeatureContribution(
            directories: [
                $testsPath.'/Feature',
                $testsPath.'/Unit',
            ],
            files: [
                new StubFile(
                    $testsPath.'/Feature/'.($isApi ? $blueprint->studlyName().'ApiTest.php' : $blueprint->studlyName().'WebTest.php'),
                    $isApi ? 'feature-test-feature-api.stub' : 'feature-test-feature-web.stub',
                    [
                        'module_test_feature_namespace' => $blueprint->namespace.'\\Tests\\Feature',
                        'module_test_feature_class' => $isApi ? $blueprint->studlyName().'ApiTest' : $blueprint->studlyName().'WebTest',
                    ],
                ),
                new StubFile(
                    $testsPath.'/Unit/Create'.$blueprint->studlyName().'ActionTest.php',
                    'feature-test-unit-action.stub',
                    [
                        'module_test_unit_namespace' => $blueprint->namespace.'\\Tests\\Unit',
                        'module_test_unit_class' => 'Create'.$blueprint->studlyName().'ActionTest',
                    ],
                ),
            ],
            manifest: [
                'features' => [
                    'testing' => [
                        'paths' => [
                            $testsPath.'/Feature',
                            $testsPath.'/Unit',
                        ],
                    ],
                ],
            ],
        );
    }
}
