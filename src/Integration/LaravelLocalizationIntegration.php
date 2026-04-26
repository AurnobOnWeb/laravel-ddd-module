<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Integration;

final class LaravelLocalizationIntegration extends AbstractIntegration
{
    public function key(): string
    {
        return 'laravel_localization';
    }

    public function installed(): bool
    {
        return class_exists('Mcamara\\LaravelLocalization\\Facades\\LaravelLocalization');
    }

    /**
     * @return array<string, mixed>
     */
    public function routeGroupAttributes(): array
    {
        $facade = 'Mcamara\\LaravelLocalization\\Facades\\LaravelLocalization';

        return [
            'prefix' => $facade::setLocale(),
            'middleware' => (array) $this->config->get('modular.integrations.laravel_localization_middleware', [
                'localize',
                'localizationRedirect',
                'localeViewPath',
            ]),
        ];
    }

    public function context(): array
    {
        return [
            'uses_laravel_localization' => $this->active(),
        ];
    }
}
