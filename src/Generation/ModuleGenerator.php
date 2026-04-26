<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Generation;

use Aurnob\LaravelDddModular\Contracts\StubRenderer;
use Aurnob\LaravelDddModular\Integration\IntegrationManager;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

final class ModuleGenerator
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly Repository $config,
        private readonly StubRepository $stubs,
        private readonly StubRenderer $renderer,
        private readonly IntegrationManager $integrations,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function generate(string $name, bool $force = false): array
    {
        $blueprint = ModuleBlueprint::from(
            $name,
            (string) $this->config->get('modular.modules.namespace', 'Modules'),
            (string) $this->config->get('modular.modules.path', base_path('Modules')),
        );

        if ($this->files->exists($blueprint->basePath) && ! $force) {
            throw new RuntimeException(sprintf('Module [%s] already exists at [%s].', $blueprint->name, $blueprint->basePath));
        }

        $this->files->ensureDirectoryExists($blueprint->basePath);

        foreach ((array) $this->config->get('modular.modules.default_folders', []) as $folder) {
            $this->files->ensureDirectoryExists($blueprint->path($folder));
        }

        $files = $this->buildFiles($blueprint);

        foreach ($files as $file) {
            $directory = dirname($file->path());
            $this->files->ensureDirectoryExists($directory);
            $this->files->put($file->path(), $file->contents());
        }

        return array_map(
            static fn (GeneratedFile $file): string => $file->path(),
            $files,
        );
    }

    /**
     * @return array<int, GeneratedFile>
     */
    private function buildFiles(ModuleBlueprint $blueprint): array
    {
        $usesSpatieData = $this->integrations->isActive('spatie_data');
        $usesViewModels = $this->integrations->isActive('spatie_view_models');
        $usesTranslatable = $this->integrations->isActive('astrotomic_translatable');

        $timestamp = now()->format('Y_m_d_His');
        $translationTimestamp = now()->addSecond()->format('Y_m_d_His');
        $viewNamespace = $this->viewNamespaceFor($blueprint);

        $common = [
            'module' => $blueprint->name,
            'module_slug' => $blueprint->slug,
            'module_description' => $blueprint->name.' module.',
            'module_namespace' => $blueprint->namespace,
            'module_provider_fqcn' => $blueprint->providerFqcn(),
            'provider_namespace' => $blueprint->providerNamespace(),
            'provider_class' => $blueprint->providerClass(),
            'controller_namespace' => $blueprint->namespace.'\\Presentation\\Http\\Controllers',
            'controller_class' => $blueprint->controllerClass(),
            'model_namespace' => $blueprint->namespace.'\\Domain\\Models',
            'model_class' => $blueprint->modelClass(),
            'translation_model_class' => $blueprint->translationModelClass(),
            'translation_model_namespace' => $blueprint->namespace.'\\Domain\\Models',
            'data_namespace' => $blueprint->namespace.'\\Application\\Data',
            'data_class' => $blueprint->dataClass(),
            'action_namespace' => $blueprint->namespace.'\\Application\\Actions',
            'action_class' => $blueprint->actionClass(),
            'request_namespace' => $blueprint->namespace.'\\Presentation\\Http\\Requests',
            'request_class' => $blueprint->requestClass(),
            'resource_namespace' => $blueprint->namespace.'\\Presentation\\Http\\Resources',
            'resource_class' => $blueprint->resourceClass(),
            'view_model_namespace' => $blueprint->namespace.'\\Presentation\\ViewModels',
            'view_model_class' => $blueprint->viewModelClass(),
            'table' => $blueprint->table(),
            'translation_table' => $blueprint->translationTable(),
            'view_namespace' => $viewNamespace,
            'route_name_index' => $blueprint->slug.'.index',
            'route_name_store' => $blueprint->slug.'.store',
            'route_prefix' => $blueprint->slug,
            'migration_file' => $timestamp.'_create_'.$blueprint->table().'_table.php',
            'translation_migration_file' => $translationTimestamp.'_create_'.$blueprint->translationTable().'_table.php',
            'controller_uses' => implode("\n", array_filter([
                'use '.$blueprint->namespace.'\\Application\\Actions\\'.$blueprint->actionClass().';',
                'use '.$blueprint->namespace.'\\Application\\Data\\'.$blueprint->dataClass().';',
                'use '.$blueprint->namespace.'\\Presentation\\Http\\Requests\\'.$blueprint->requestClass().';',
                'use '.$blueprint->namespace.'\\Presentation\\ViewModels\\'.$blueprint->viewModelClass().';',
                'use App\\Http\\Controllers\\Controller;',
            ])),
            'index_body' => "        return view('".$viewNamespace."::index', new ".$blueprint->viewModelClass()."('".$blueprint->name."'));\n",
            'store_body' => $usesTranslatable
                ? "        \$action->execute(".$blueprint->dataClass()."::from(\$request->validated()));\n\n        return redirect()->route('".$blueprint->slug.".index');\n"
                : "        \$action->execute(".$blueprint->dataClass()."::from(\$request->validated()));\n\n        return redirect()->route('".$blueprint->slug.".index');\n",
            'action_use_data' => 'use '.$blueprint->namespace.'\\Application\\Data\\'.$blueprint->dataClass().';',
            'action_use_model' => 'use '.$blueprint->namespace.'\\Domain\\Models\\'.$blueprint->modelClass().';',
            'action_payload' => $usesTranslatable
                ? "        return {$blueprint->modelClass()}::query()->create([\n            'slug' => Str::slug(\$data->title),\n            app()->getLocale() => [\n                'title' => \$data->title,\n                'body' => \$data->body,\n            ],\n        ]);\n"
                : "        return {$blueprint->modelClass()}::query()->create(\$data->toArray());\n",
            'action_extra_imports' => $usesTranslatable ? "use Illuminate\\Support\\Str;\n" : '',
            'view_model_base_import' => $usesViewModels ? 'use Spatie\\ViewModels\\ViewModel;' : 'use Illuminate\\Contracts\\Support\\Arrayable;',
            'view_model_base_class' => $usesViewModels ? 'extends ViewModel' : 'implements Arrayable',
            'view_model_class_declaration' => $usesViewModels
                ? 'final class '.$blueprint->viewModelClass().' extends ViewModel'
                : 'final readonly class '.$blueprint->viewModelClass().' implements Arrayable',
            'view_model_body' => $usesViewModels
                ? "    public function __construct(private readonly string \$moduleName)\n    {\n    }\n\n    public function moduleName(): string\n    {\n        return \$this->moduleName;\n    }\n\n    public function pageTitle(): string\n    {\n        return \$this->moduleName.' module';\n    }\n"
                : "    public function __construct(private readonly string \$moduleName)\n    {\n    }\n\n    public function toArray(): array\n    {\n        return [\n            'moduleName' => \$this->moduleName,\n            'pageTitle' => \$this->moduleName.' module',\n        ];\n    }\n",
            'data_imports' => $usesSpatieData ? 'use Spatie\\LaravelData\\Data;' : '',
            'data_class_declaration' => $usesSpatieData
                ? 'final class '.$blueprint->dataClass().' extends Data'
                : 'final readonly class '.$blueprint->dataClass(),
            'data_body' => $usesSpatieData
                ? "    public function __construct(\n        public string \$title,\n        public ?string \$body = null,\n    ) {\n    }\n"
                : "    public function __construct(\n        public string \$title,\n        public ?string \$body = null,\n    ) {\n    }\n\n    public static function from(array \$payload): self\n    {\n        return new self(\n            title: (string) (\$payload['title'] ?? ''),\n            body: isset(\$payload['body']) ? (string) \$payload['body'] : null,\n        );\n    }\n\n    public function toArray(): array\n    {\n        return [\n            'title' => \$this->title,\n            'body' => \$this->body,\n        ];\n    }\n",
        ];

        $files = [
            new GeneratedFile(
                $blueprint->path('module.json'),
                $this->render('module.json.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('Infrastructure/Providers/'.$blueprint->providerClass().'.php'),
                $this->render('module-service-provider.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('Presentation/Http/Controllers/'.$blueprint->controllerClass().'.php'),
                $this->render('controller.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('Application/Actions/'.$blueprint->actionClass().'.php'),
                $this->render('action.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('Application/Data/'.$blueprint->dataClass().'.php'),
                $this->render('data.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('Presentation/Http/Requests/'.$blueprint->requestClass().'.php'),
                $this->render('request.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('Presentation/Http/Resources/'.$blueprint->resourceClass().'.php'),
                $this->render('resource.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('Presentation/ViewModels/'.$blueprint->viewModelClass().'.php'),
                $this->render('view-model.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('routes/web.php'),
                $this->render('routes.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('Presentation/Views/index.blade.php'),
                $this->render('view.stub', $common),
            ),
            new GeneratedFile(
                $blueprint->path('config/module.php'),
                $this->render('config.stub', $common),
            ),
        ];

        if ($usesTranslatable) {
            $files[] = new GeneratedFile(
                $blueprint->path('Domain/Models/'.$blueprint->modelClass().'.php'),
                $this->render('translatable-model.stub', $common),
            );
            $files[] = new GeneratedFile(
                $blueprint->path('Domain/Models/'.$blueprint->translationModelClass().'.php'),
                $this->render('translation-model.stub', $common),
            );
            $files[] = new GeneratedFile(
                $blueprint->path('Infrastructure/Migrations/'.$common['migration_file']),
                $this->render('translatable-migration.stub', $common),
            );
            $files[] = new GeneratedFile(
                $blueprint->path('Infrastructure/Migrations/'.$common['translation_migration_file']),
                $this->render('translation-migration.stub', $common),
            );
        } else {
            $files[] = new GeneratedFile(
                $blueprint->path('Domain/Models/'.$blueprint->modelClass().'.php'),
                $this->render('model.stub', $common),
            );
            $files[] = new GeneratedFile(
                $blueprint->path('Infrastructure/Migrations/'.$common['migration_file']),
                $this->render('migration.stub', $common),
            );
        }

        return $files;
    }

    private function render(string $stub, array $replacements): string
    {
        return $this->renderer->render(
            $this->stubs->contents($stub),
            $replacements,
        );
    }

    private function viewNamespaceFor(ModuleBlueprint $blueprint): string
    {
        $strategy = (string) $this->config->get('modular.views.namespace_strategy', 'slug');
        $prefix = $this->config->get('modular.views.namespace_prefix');

        return match ($strategy) {
            'studly' => $blueprint->name,
            'prefix' => trim((string) ($prefix ?: 'modules').'.'.$blueprint->slug, '.'),
            default => $blueprint->slug,
        };
    }
}
