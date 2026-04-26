<?php

declare(strict_types=1);

namespace Aurnob\LaravelDddModular\Commands;

use Aurnob\LaravelDddModular\Generation\ModuleGenerator;
use Illuminate\Console\Command;
use RuntimeException;

final class MakeModuleCommand extends Command
{
    protected $signature = 'modular:make {name : The module name} {--force : Overwrite existing files if the module already exists}';

    protected $description = 'Generate a DDD module with the configured architecture.';

    public function __construct(
        private readonly ModuleGenerator $generator,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $files = $this->generator->generate(
                (string) $this->argument('name'),
                (bool) $this->option('force'),
            );
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Module [%s] generated successfully.', $this->argument('name')));

        foreach ($files as $file) {
            $this->line(' - '.$file);
        }

        return self::SUCCESS;
    }
}
