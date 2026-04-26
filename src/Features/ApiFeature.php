<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Aurnob\LaravelDddModular\Contracts\ModuleFeature;
use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;
use Aurnob\LaravelDddModular\Generation\StubFile;

final class ApiFeature extends AbstractFeature implements ModuleFeature
{
    public function key(): string
    {
        return 'api';
    }

    public function description(): string
    {
        return 'Generate Sanctum-protected API routes and an API controller.';
    }

    public function contribute(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): ModuleFeatureContribution
    {
        $middleware = (array) $this->config->get('modular.features.api.middleware', ['api', 'auth:sanctum']);
        $uriPrefix = trim((string) $this->config->get('modular.features.api.uri_prefix', 'api'), '/');
        $routeNamePrefix = (string) $this->config->get('modular.features.api.route_name_prefix', 'api.');
        $routePath = (string) $this->config->get('modular.features.api.routes_path', 'routes/api.php');

        return new ModuleFeatureContribution(
            directories: [
                'Presentation/Http/Controllers/Api',
            ],
            files: [
                new StubFile(
                    'Presentation/Http/Controllers/Api/'.$blueprint->studlyName().'ApiController.php',
                    'feature-api-controller.stub',
                    [
                        'api_controller_namespace' => $blueprint->namespace.'\\Presentation\\Http\\Controllers\\Api',
                        'api_controller_class' => $blueprint->studlyName().'ApiController',
                    ],
                ),
                new StubFile(
                    $routePath,
                    'feature-api-routes.stub',
                    [
                        'api_controller_namespace' => $blueprint->namespace.'\\Presentation\\Http\\Controllers\\Api',
                        'api_controller_class' => $blueprint->studlyName().'ApiController',
                        'api_route_middleware' => var_export($middleware, true),
                        'api_route_uri_prefix' => trim($uriPrefix.'/'.$blueprint->slug, '/'),
                        'api_route_name_prefix' => trim($routeNamePrefix, '.').'.'.$blueprint->slug.'.',
                    ],
                ),
            ],
            manifest: [
                'features' => [
                    'api' => [
                        'guard' => 'sanctum',
                        'middleware' => $middleware,
                    ],
                ],
            ],
        );
    }
}
