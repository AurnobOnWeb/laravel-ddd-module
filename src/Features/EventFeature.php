<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Features;

use Aurnob\LaravelDddModular\Contracts\ModuleFeature;
use Aurnob\LaravelDddModular\Generation\ModuleBlueprint;
use Aurnob\LaravelDddModular\Generation\ModuleFeatureContribution;
use Aurnob\LaravelDddModular\Generation\StubFile;

final class EventFeature extends AbstractFeature implements ModuleFeature
{
    public function key(): string
    {
        return 'events';
    }

    public function description(): string
    {
        return 'Generate a domain event, listener, and dispatch hook.';
    }

    public function contribute(ModuleBlueprint $blueprint, array $selectedFeatures, array $context = []): ModuleFeatureContribution
    {
        $eventClass = $blueprint->studlyName().'Created';
        $listenerClass = 'Update'.$blueprint->studlyName().'Projection';

        return new ModuleFeatureContribution(
            directories: [
                'Domain/Events',
                'Application/Listeners',
            ],
            files: [
                new StubFile(
                    'Domain/Events/'.$eventClass.'.php',
                    'feature-event.stub',
                    [
                        'event_namespace' => $blueprint->namespace.'\\Domain\\Events',
                        'event_class' => $eventClass,
                    ],
                ),
                new StubFile(
                    'Application/Listeners/'.$listenerClass.'.php',
                    'feature-listener.stub',
                    [
                        'listener_namespace' => $blueprint->namespace.'\\Application\\Listeners',
                        'listener_class' => $listenerClass,
                        'event_namespace' => $blueprint->namespace.'\\Domain\\Events',
                        'event_class' => $eventClass,
                    ],
                ),
            ],
            providerImports: [
                'use '.$blueprint->namespace.'\\Application\\Listeners\\'.$listenerClass.';',
                'use '.$blueprint->namespace.'\\Domain\\Events\\'.$eventClass.';',
                'use Illuminate\\Support\\Facades\\Event;',
            ],
            providerBootStatements: [
                'Event::listen('.$eventClass.'::class, '.$listenerClass.'::class);',
            ],
            actionImports: [
                'use '.$blueprint->namespace.'\\Domain\\Events\\'.$eventClass.';',
            ],
            actionAfterStatements: [
                'event(new '.$eventClass.'($model));',
            ],
            manifest: [
                'features' => [
                    'events' => [
                        'dispatched' => [$eventClass],
                    ],
                ],
            ],
        );
    }
}
