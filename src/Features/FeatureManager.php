<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Aurnob\LaravelDddModular\Contracts\ModuleFeature;
use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;
use Illuminate\Contracts\Config\Repository;
use RuntimeException;

final class FeatureManager
{
    /**
     * @var array<string, ModuleFeature>
     */
    private array $features = [];

    /**
     * @param  array<int, ModuleFeature>  $features
     */
    public function __construct(
        private readonly Repository $config,
        array $features,
    ) {
        foreach ($features as $feature) {
            $this->features[$feature->key()] = $feature;
        }
    }

    /**
     * @return array<int, string>
     */
    public function available(): array
    {
        $configured = $this->normalize((array) $this->config->get('modular.features.available', array_keys($this->features)));

        return array_values(array_filter(
            $configured,
            fn (string $feature): bool => isset($this->features[$feature]),
        ));
    }

    /**
     * @param  array<int, string>  $requested
     * @param  array<int, string>  $excluded
     * @return array<int, string>
     */
    public function resolve(array $requested = [], array $excluded = []): array
    {
        $selected = array_merge(
            (array) $this->config->get('modular.features.defaults', []),
            $requested,
        );

        $selected = $this->normalize($selected);
        $excluded = $this->normalize($excluded);
        $selected = array_values(array_filter(
            $selected,
            static fn (string $feature): bool => ! in_array($feature, $excluded, true),
        ));

        return $this->validate($selected);
    }

    /**
     * @param  array<int, string>  $selectedFeatures
     * @param  array<string, mixed>  $context
     * @return array<int, ModuleFeatureContribution>
     */
    public function contributions(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): array
    {
        $selected = $this->validate($this->normalize($selectedFeatures));
        $contributions = [];

        foreach ($selected as $featureKey) {
            $contributions[] = $this->features[$featureKey]->contribute($blueprint, $selected, $context);
        }

        return $contributions;
    }

    /**
     * @param  array<int, string>  $features
     * @return array<int, string>
     */
    private function normalize(array $features): array
    {
        $normalized = [];

        foreach ($features as $feature) {
            $value = strtolower(trim($feature));

            if ($value === '') {
                continue;
            }

            $normalized[] = $value;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param  array<int, string>  $selected
     * @return array<int, string>
     */
    private function validate(array $selected): array
    {
        $available = $this->available();

        foreach ($selected as $feature) {
            if (! in_array($feature, $available, true)) {
                throw new RuntimeException(sprintf(
                    'Unknown module feature [%s]. Available features: %s.',
                    $feature,
                    implode(', ', $available),
                ));
            }
        }

        return $selected;
    }
}
