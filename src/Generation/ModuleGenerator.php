<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Generation;

use Aurnob\LaravelDddModular\Contracts\StubRenderer;
use Aurnob\LaravelDddModular\Features\FeatureManager;
use Aurnob\LaravelDddModular\Integration\IntegrationManager;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final class ModuleGenerator
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly Repository $config,
        private readonly StubRepository $stubs,
        private readonly StubRenderer $renderer,
        private readonly IntegrationManager $integrations,
        private readonly FeatureManager $features,
    ) {
    }

    /**
     * @param  array<int, string>  $requestedFeatures
     * @param  array<int, string>  $excludedFeatures
     * @return array<int, string>
     */
    public function generate(
        string $name,
        bool $force = false,
        array $requestedFeatures = [],
        array $excludedFeatures = [],
    ): array {
        $blueprint = ModuleBlueprint::from(
            $name,
            (string) $this->config->get('modular.modules.namespace', 'Modules'),
            (string) $this->config->get('modular.modules.path', base_path('Modules')),
        );

        if ($this->files->exists($blueprint->basePath) && ! $force) {
            throw new RuntimeException(sprintf('Module [%s] already exists at [%s].', $blueprint->name, $blueprint->basePath));
        }

        $selectedFeatures = $this->features->resolve($requestedFeatures, $excludedFeatures);
        $this->files->ensureDirectoryExists($blueprint->basePath);
        $directories = (array) $this->config->get('modular.modules.default_folders', []);

        foreach ($this->features->contributions($blueprint, $selectedFeatures, [
            'phase' => 'directories',
        ]) as $contribution) {
            $directories = array_merge($directories, $contribution->directories());
        }

        foreach (array_values(array_unique($directories)) as $folder) {
            $this->files->ensureDirectoryExists($blueprint->path($folder));
        }

        $files = $this->buildFiles($blueprint, $selectedFeatures);

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
     * @param  array<int, string>  $selectedFeatures
     * @return array<int, GeneratedFile>
     */
    private function buildFiles(ModuleBlueprint $blueprint, array $selectedFeatures): array
    {
        $usesSpatieData = $this->integrations->isActive('spatie_data');
        $usesViewModels = $this->integrations->isActive('spatie_view_models');
        $usesTranslatable = $this->integrations->isActive('astrotomic_translatable');

        $timestamp = now()->format('Y_m_d_His');
        $translationTimestamp = now()->addSecond()->format('Y_m_d_His');
        $viewNamespace = $this->viewNamespaceFor($blueprint);
        $featureContributions = $this->features->contributions($blueprint, $selectedFeatures, [
            'uses_spatie_data' => $usesSpatieData,
            'uses_spatie_view_models' => $usesViewModels,
            'uses_astrotomic_translatable' => $usesTranslatable,
        ]);

        $providerImports = [];
        $providerRegisterStatements = [];
        $providerBootStatements = [];
        $actionImports = [];
        $actionAfterStatements = [];
        $featureReplacements = [];
        $featureManifest = [];

        foreach ($featureContributions as $contribution) {
            $providerImports = array_merge($providerImports, $contribution->providerImports());
            $providerRegisterStatements = array_merge($providerRegisterStatements, $contribution->providerRegisterStatements());
            $providerBootStatements = array_merge($providerBootStatements, $contribution->providerBootStatements());
            $actionImports = array_merge($actionImports, $contribution->actionImports());
            $actionAfterStatements = array_merge($actionAfterStatements, $contribution->actionAfterStatements());
            $featureReplacements = array_merge($featureReplacements, $contribution->replacements());
            $featureManifest = array_replace_recursive($featureManifest, $contribution->manifest());
        }

        $common = array_merge([
            'module' => $blueprint->name,
            'module_json_name' => $this->jsonString($blueprint->name),
            'module_slug' => $blueprint->slug,
            'module_json_slug' => $this->jsonString($blueprint->slug),
            'module_description' => $blueprint->name.' module.',
            'module_json_description' => $this->jsonString($blueprint->name.' module.'),
            'module_namespace' => $blueprint->namespace,
            'module_json_namespace' => $this->jsonString($blueprint->namespace),
            'module_provider_fqcn' => $blueprint->providerFqcn(),
            'module_json_provider_fqcn' => $this->jsonString($blueprint->providerFqcn()),
            'module_features_json' => $this->jsonList($selectedFeatures, 4),
            'module_features_php' => $this->phpList($selectedFeatures, 4),
            'module_feature_manifest_json' => $this->jsonObject($featureManifest, 4),
            'provider_namespace' => $blueprint->providerNamespace(),
            'provider_class' => $blueprint->providerClass(),
            'provider_imports' => $this->renderImports($providerImports),
            'provider_register_body' => $this->renderStatements($providerRegisterStatements),
            'provider_boot_body' => $this->renderStatements($providerBootStatements),
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
            'store_body' => "        \$action->execute(".$blueprint->dataClass()."::from(\$request->validated()));\n\n        return redirect()->route('".$blueprint->slug.".index');\n",
            'action_use_data' => 'use '.$blueprint->namespace.'\\Application\\Data\\'.$blueprint->dataClass().';',
            'action_use_model' => 'use '.$blueprint->namespace.'\\Domain\\Models\\'.$blueprint->modelClass().';',
            'action_payload' => $usesTranslatable
                ? "        \$model = {$blueprint->modelClass()}::query()->create([\n            'slug' => Str::slug(\$data->title),\n            app()->getLocale() => [\n                'title' => \$data->title,\n                'body' => \$data->body,\n            ],\n        ]);\n"
                : "        \$model = {$blueprint->modelClass()}::query()->create(\$data->toArray());\n",
            'action_extra_imports' => $this->renderImports(array_merge(
                $usesTranslatable ? ['use Illuminate\\Support\\Str;'] : [],
                $actionImports,
            )),
            'action_after_create' => $this->renderStatements($actionAfterStatements, '        ', ''),
            'view_model_base_import' => $usesViewModels ? 'use Spatie\\ViewModels\\ViewModel;' : 'use Illuminate\\Contracts\\Support\\Arrayable;',
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
        ], $featureReplacements);

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

        foreach ($featureContributions as $contribution) {
            foreach ($contribution->files() as $featureFile) {
                $files[] = new GeneratedFile(
                    $blueprint->path($featureFile->path()),
                    $this->render($featureFile->stub(), array_merge($common, $featureFile->replacements())),
                );
            }
        }

        return $files;
    }

    /**
     * @param  array<string, scalar>  $replacements
     */
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

    /**
     * @param  array<int, string>  $imports
     */
    private function renderImports(array $imports): string
    {
        $imports = array_values(array_unique(array_filter(array_map('trim', $imports))));

        if ($imports === []) {
            return '';
        }

        return implode("\n", $imports)."\n";
    }

    /**
     * @param  array<int, string>  $statements
     */
    private function renderStatements(array $statements, string $indent = '        ', string $fallback = '        //'): string
    {
        $statements = array_values(array_unique(array_filter(array_map('trim', $statements))));

        if ($statements === []) {
            return $fallback;
        }

        return implode("\n", array_map(
            static fn (string $statement): string => $indent.$statement,
            $statements,
        ));
    }

    /**
     * @param  array<int, string>  $items
     */
    private function jsonList(array $items, int $indentSpaces = 0): string
    {
        if ($items === []) {
            return '[]';
        }

        return $this->indentMultiline(
            (string) json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $indentSpaces,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function jsonObject(array $payload, int $indentSpaces = 0): string
    {
        if ($payload === []) {
            return '{}';
        }

        return $this->indentMultiline(
            (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            $indentSpaces,
        );
    }

    private function jsonString(string $value): string
    {
        return (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<int, string>  $items
     */
    private function phpList(array $items, int $indentSpaces = 0): string
    {
        if ($items === []) {
            return '[]';
        }

        $lines = ['['];

        foreach ($items as $item) {
            $lines[] = '    '.var_export($item, true).',';
        }

        $lines[] = ']';

        return $this->indentMultiline(implode("\n", $lines), $indentSpaces);
    }

    private function indentMultiline(string $value, int $indentSpaces): string
    {
        if ($indentSpaces <= 0) {
            return $value;
        }

        $indent = str_repeat(' ', $indentSpaces);
        $lines = explode("\n", $value);

        return implode("\n", array_map(
            static fn (string $line, int $index): string => $index === 0 ? $line : $indent.$line,
            $lines,
            array_keys($lines),
        ));
    }
}
