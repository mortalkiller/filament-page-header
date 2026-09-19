<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Commands;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Support\Commands\Concerns\HasPanel;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Support\PageHeaderPageUpdater;
use ReflectionClass;
use RuntimeException;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\search;

final class MakePageHeaderCommand extends Command
{
    use HasPanel;

    protected $description = 'Create a page header schema for an existing Filament resource';

    protected $signature = 'make:filament-page-header
        {resource? : The Filament resource class or name}
        {--panel= : The Filament panel containing the resource}
        {--page=* : Resource page type to enable (list, create, view, edit)}
        {--no-pages : Generate the header schema without modifying resource pages}
        {--force : Overwrite the generated header schema if it already exists}';

    public function handle(Filesystem $filesystem, PageHeaderPageUpdater $pageUpdater): int
    {
        try {
            $this->configureTargetPanel();

            $resource = $this->resolveResource();
            $availablePages = $this->getStandardPages($resource);
            $selectedPages = $this->resolveSelectedPages($availablePages);

            [$schemaFqn, $schemaPath, $schemaContents] = $this->getSchemaDefinition($resource);

            if ($filesystem->exists($schemaPath) && ! $this->option('force')) {
                $this->components->warn("Page header schema [{$schemaFqn}] already exists; leaving it unchanged.");
            } else {
                $filesystem->ensureDirectoryExists(dirname($schemaPath));
                $filesystem->put($schemaPath, $schemaContents);

                $this->components->info("Page header schema [{$schemaFqn}] created successfully.");
            }

            foreach ($selectedPages as $pageType) {
                $pageClass = $availablePages[$pageType];

                if ($pageUpdater->addTrait($pageClass)) {
                    $this->components->info('Added HasPageHeader to ['.$pageClass.'].');
                } else {
                    $this->components->info('Page ['.$pageClass.'] already uses HasPageHeader.');
                }
            }

            if ($selectedPages === []) {
                $this->components->info('No resource pages were modified.');
            }

            return self::SUCCESS;
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    protected function configureTargetPanel(): void
    {
        $panels = Filament::getPanels();

        if ($panels === []) {
            throw new RuntimeException('No Filament panels are registered.');
        }

        $panelOption = $this->option('panel');

        if (filled($panelOption) && (Filament::getPanel((string) $panelOption, isStrict: false) === null)) {
            throw new RuntimeException("Filament panel [{$panelOption}] was not found.");
        }

        if (! $this->input->isInteractive() && blank($panelOption) && (count($panels) > 1)) {
            throw new RuntimeException('Multiple Filament panels are registered. Pass --panel to choose one.');
        }

        $this->configurePanel(question: 'Which panel contains the resource?');

        if (! ($this->panel instanceof Panel)) {
            throw new RuntimeException('A Filament panel could not be resolved.');
        }
    }

    /**
     * @return class-string<resource>
     */
    protected function resolveResource(): string
    {
        $resources = array_values($this->panel->getResources());

        if ($resources === []) {
            throw new RuntimeException("Panel [{$this->panel->getId()}] has no registered resources.");
        }

        $resourceInput = $this->argument('resource');

        if (blank($resourceInput)) {
            if (! $this->input->isInteractive()) {
                throw new RuntimeException('The resource argument is required in non-interactive mode.');
            }

            return search(
                label: 'Which resource should use a page header?',
                options: function (?string $search) use ($resources): array {
                    $search = (string) str($search)->trim()->replace(['\\', '/'], '');

                    return collect($resources)
                        ->filter(fn (string $resource): bool => blank($search)
                            || str($resource)->replace(['\\', '/'], '')->contains($search, ignoreCase: true))
                        ->mapWithKeys(fn (string $resource): array => [$resource => $this->getResourceLabel($resource)])
                        ->all();
                },
            );
        }

        $normalized = (string) str((string) $resourceInput)
            ->trim('/')
            ->trim('\\')
            ->trim()
            ->replace('/', '\\');

        if (class_exists($normalized) && is_subclass_of($normalized, Resource::class)) {
            if (! in_array($normalized, $resources, true)) {
                throw new RuntimeException("Resource [{$normalized}] is not registered in panel [{$this->panel->getId()}].");
            }

            return $normalized;
        }

        $candidates = array_values(array_filter(
            $resources,
            fn (string $resource): bool => $this->resourceMatchesInput($resource, $normalized),
        ));

        if (count($candidates) === 1) {
            return $candidates[0];
        }

        if (count($candidates) > 1) {
            throw new RuntimeException(
                "Resource [{$resourceInput}] is ambiguous in panel [{$this->panel->getId()}]. Use its fully-qualified class name.",
            );
        }

        throw new RuntimeException(
            "Resource [{$resourceInput}] was not found in panel [{$this->panel->getId()}].",
        );
    }

    /**
     * @param  class-string<resource>  $resource
     */
    protected function resourceMatchesInput(string $resource, string $input): bool
    {
        $resourceBasename = class_basename($resource);
        $resourceName = (string) str($resourceBasename)->beforeLast('Resource');
        $relative = $this->getResourceLabel($resource);
        $relativeWithoutSuffix = (string) str($relative)->beforeLast('Resource');

        return collect([
            $resourceBasename,
            $resourceName,
            $relative,
            $relativeWithoutSuffix,
        ])->contains(fn (string $candidate): bool => strcasecmp($candidate, $input) === 0);
    }

    /**
     * @param  class-string<resource>  $resource
     */
    protected function getResourceLabel(string $resource): string
    {
        $marker = '\\Resources\\';

        return str_contains($resource, $marker)
            ? (string) str($resource)->afterLast($marker)
            : class_basename($resource);
    }

    /**
     * @param  class-string<resource>  $resource
     * @return array<string, class-string>
     */
    protected function getStandardPages(string $resource): array
    {
        $pages = [];

        foreach ($resource::getPages() as $registration) {
            $page = $registration->getPage();

            $type = match (true) {
                is_subclass_of($page, ListRecords::class) => 'list',
                is_subclass_of($page, CreateRecord::class) => 'create',
                is_subclass_of($page, ViewRecord::class) => 'view',
                is_subclass_of($page, EditRecord::class) => 'edit',
                default => null,
            };

            if ($type !== null) {
                $pages[$type] ??= $page;
            }
        }

        return array_replace(
            array_fill_keys(['list', 'create', 'view', 'edit'], null),
            $pages,
        );
    }

    /**
     * @param  array<string, class-string|null>  $availablePages
     * @return list<string>
     */
    protected function resolveSelectedPages(array $availablePages): array
    {
        $availablePages = array_filter($availablePages);
        $requestedPages = array_values(array_filter((array) $this->option('page'), 'filled'));
        $noPages = (bool) $this->option('no-pages');

        if ($noPages && $requestedPages !== []) {
            throw new RuntimeException('The --no-pages option cannot be combined with --page.');
        }

        if ($noPages) {
            return [];
        }

        if ($requestedPages !== []) {
            $selected = [];

            foreach ($requestedPages as $page) {
                $page = $this->normalizePageType((string) $page);

                if (! array_key_exists($page, $availablePages)) {
                    throw new RuntimeException("Resource page type [{$page}] does not exist on the selected resource.");
                }

                $selected[] = $page;
            }

            return array_values(array_unique($selected));
        }

        if (! $this->input->isInteractive()) {
            throw new RuntimeException('Pass at least one --page option or use --no-pages in non-interactive mode.');
        }

        if ($availablePages === []) {
            $this->components->warn('No standard List, Create, View, or Edit resource pages were found.');

            return [];
        }

        $defaults = array_values(array_intersect(['list', 'view', 'edit'], array_keys($availablePages)));

        return multiselect(
            label: 'Which resource pages should use the page header?',
            options: collect($availablePages)
                ->mapWithKeys(fn (string $page, string $type): array => [
                    $type => ucfirst($type).' ('.class_basename($page).')',
                ])
                ->all(),
            default: $defaults,
            required: false,
        );
    }

    protected function normalizePageType(string $page): string
    {
        return match ((string) str($page)->trim()->lower()->replace(['-', '_'], '')) {
            'list', 'index', 'listrecords' => 'list',
            'create', 'createrecord' => 'create',
            'view', 'viewrecord' => 'view',
            'edit', 'editrecord' => 'edit',
            default => throw new RuntimeException(
                "Unknown resource page type [{$page}]. Expected list, create, view, or edit.",
            ),
        };
    }

    /**
     * @param  class-string<resource>  $resource
     * @return array{0: class-string, 1: string, 2: string}
     */
    protected function getSchemaDefinition(string $resource): array
    {
        $reflection = new ReflectionClass($resource);
        $resourcePath = $reflection->getFileName();

        if (! is_string($resourcePath)) {
            throw new RuntimeException("Unable to locate the source file for resource [{$resource}].");
        }

        $model = $resource::getModel();
        $modelBasename = class_basename($model);
        $schemaNamespace = $reflection->getNamespaceName().'\\Schemas';
        $schemaClass = "{$modelBasename}Header";
        $schemaFqn = "{$schemaNamespace}\\{$schemaClass}";
        $schemaPath = dirname($resourcePath).DIRECTORY_SEPARATOR.'Schemas'.DIRECTORY_SEPARATOR."{$schemaClass}.php";

        $contents = <<<PHP
<?php

declare(strict_types=1);

namespace {$schemaNamespace};

use Filament\\Schemas\\Schema;
use MortalKiller\\FilamentPageHeader\\Components\\Header;

final class {$schemaClass}
{
    public static function configure(Schema \$schema): Schema
    {
        return \$schema->components([
            Header::make(),
        ]);
    }
}

PHP;

        return [$schemaFqn, $schemaPath, $contents];
    }
}
