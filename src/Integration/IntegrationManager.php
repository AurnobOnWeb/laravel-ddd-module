<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Integration;

use Aurnob\LaravelDddModular\Contracts\ModuleIntegration;
use RuntimeException;

final class IntegrationManager
{
    /**
     * @param  array<int, ModuleIntegration>  $integrations
     */
    public function __construct(
        private readonly array $integrations,
    ) {
    }

    public function isActive(string $key): bool
    {
        return $this->integration($key)->active();
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        $context = [];

        foreach ($this->integrations as $integration) {
            $context = array_merge($context, $integration->context());
        }

        return $context;
    }

    public function usesLocalizedRoutes(): bool
    {
        return $this->isActive('laravel_localization');
    }

    /**
     * @return array<string, mixed>
     */
    public function localizedRouteAttributes(): array
    {
        /** @var LaravelLocalizationIntegration $integration */
        $integration = $this->integration('laravel_localization');

        return $integration->routeGroupAttributes();
    }

    private function integration(string $key): ModuleIntegration
    {
        foreach ($this->integrations as $integration) {
            if ($integration->key() === $key) {
                return $integration;
            }
        }

        throw new RuntimeException(sprintf('Unknown integration [%s].', $key));
    }
}
