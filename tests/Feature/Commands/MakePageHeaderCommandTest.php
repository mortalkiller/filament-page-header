<?php

declare(strict_types=1);

use Filament\Panel;
use Filament\PanelRegistry;
use Illuminate\Filesystem\Filesystem;

function makeGeneratorFixture(array $pageTypes = ['list', 'create', 'view', 'edit'], bool $withTrait = false): array
{
    static $counter = 0;

    $counter++;

    $namespace = 'MortalKiller\\FilamentPageHeader\\Tests\\Generated\\GeneratorFixture'.$counter;
    $directory = sys_get_temp_dir().'/filament-page-header-generator-'.getmypid().'-'.$counter;
    $resourceDirectory = "{$directory}/Resources/Orders";
    $pagesDirectory = "{$resourceDirectory}/Pages";

    $filesystem = app(Filesystem::class);
    $filesystem->ensureDirectoryExists($pagesDirectory);

    $modelClass = "{$namespace}\\Order";
    $resourceClass = "{$namespace}\\Resources\\Orders\\OrderResource";

    $filesystem->put("{$directory}/Order.php", <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use Illuminate\\Database\\Eloquent\\Model;

class Order extends Model
{
    protected \$guarded = [];
}

PHP);

    require_once "{$directory}/Order.php";

    $pageDefinitions = [
        'list' => ['ListOrders', 'Filament\\Resources\\Pages\\ListRecords'],
        'create' => ['CreateOrder', 'Filament\\Resources\\Pages\\CreateRecord'],
        'view' => ['ViewOrder', 'Filament\\Resources\\Pages\\ViewRecord'],
        'edit' => ['EditOrder', 'Filament\\Resources\\Pages\\EditRecord'],
    ];

    $pageClasses = [];

    foreach ($pageTypes as $type) {
        [$class, $baseClass] = $pageDefinitions[$type];
        $baseBasename = class_basename($baseClass);
        $pageClass = "{$namespace}\\Resources\\Orders\\Pages\\{$class}";
        $pageClasses[$type] = $pageClass;

        $traitImport = $withTrait
            ? "use MortalKiller\\FilamentPageHeader\\Concerns\\HasPageHeader;\n"
            : '';
        $traitUse = $withTrait
            ? "    use HasPageHeader;\n\n"
            : '';

        $filesystem->put("{$pagesDirectory}/{$class}.php", <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}\\Resources\\Orders\\Pages;

use {$baseClass};
{$traitImport}use {$resourceClass};

class {$class} extends {$baseBasename}
{
{$traitUse}    protected static string \$resource = OrderResource::class;
}

PHP);

        require_once "{$pagesDirectory}/{$class}.php";
    }

    $imports = [];
    $routes = [];

    foreach ($pageTypes as $type) {
        [$class] = $pageDefinitions[$type];
        $imports[] = "use {$namespace}\\Resources\\Orders\\Pages\\{$class};";

        $route = match ($type) {
            'list' => "'index' => {$class}::route('/')",
            'create' => "'create' => {$class}::route('/create')",
            'view' => "'view' => {$class}::route('/{record}')",
            'edit' => "'edit' => {$class}::route('/{record}/edit')",
        };

        $routes[] = "            {$route},";
    }

    $importsBlock = implode("\n", $imports);
    $routesBlock = implode("\n", $routes);

    $filesystem->put("{$resourceDirectory}/OrderResource.php", <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace}\\Resources\\Orders;

use Filament\\Resources\\Resource;
use {$modelClass};
{$importsBlock}

class OrderResource extends Resource
{
    protected static ?string \$model = Order::class;

    public static function getPages(): array
    {
        return [
{$routesBlock}
        ];
    }
}

PHP);

    require_once "{$resourceDirectory}/OrderResource.php";

    $panelId = 'generator-'.$counter;

    app(PanelRegistry::class)->register(
        Panel::make()
            ->id($panelId)
            ->resources([$resourceClass]),
    );

    return [
        'directory' => $directory,
        'resourceDirectory' => $resourceDirectory,
        'resource' => $resourceClass,
        'panel' => $panelId,
        'pages' => $pageClasses,
    ];
}

function generatorFileContents(string $class): string
{
    $path = (new ReflectionClass($class))->getFileName();

    return file_get_contents($path);
}

it('generates a conventional header schema and enables selected resource pages', function (): void {
    $fixture = makeGeneratorFixture();

    $this->artisan('make:filament-page-header', [
        'resource' => 'OrderResource',
        '--panel' => $fixture['panel'],
        '--page' => ['list', 'edit'],
    ])->assertSuccessful();

    $schemaPath = $fixture['resourceDirectory'].'/Schemas/OrderHeader.php';

    expect(is_file($schemaPath))->toBeTrue();

    $schema = file_get_contents($schemaPath);

    expect($schema)
        ->toContain('final class OrderHeader')
        ->toContain('Header::make()');

    foreach (['list', 'edit'] as $type) {
        $page = generatorFileContents($fixture['pages'][$type]);

        expect(substr_count($page, 'use MortalKiller\\FilamentPageHeader\\Concerns\\HasPageHeader;'))->toBe(1)
            ->and(substr_count($page, 'use HasPageHeader;'))->toBe(1);
    }

    foreach (['create', 'view'] as $type) {
        expect(generatorFileContents($fixture['pages'][$type]))
            ->not->toContain('MortalKiller\\FilamentPageHeader\\Concerns\\HasPageHeader')
            ->not->toContain('use HasPageHeader;');
    }

    $this->artisan('make:filament-page-header', [
        'resource' => 'OrderResource',
        '--panel' => $fixture['panel'],
        '--page' => ['list', 'edit'],
    ])->assertSuccessful();

    foreach (['list', 'edit'] as $type) {
        $page = generatorFileContents($fixture['pages'][$type]);

        expect(substr_count($page, 'use MortalKiller\\FilamentPageHeader\\Concerns\\HasPageHeader;'))->toBe(1)
            ->and(substr_count($page, 'use HasPageHeader;'))->toBe(1);
    }
});

it('protects an existing schema unless force is explicitly requested', function (): void {
    $fixture = makeGeneratorFixture();

    $this->artisan('make:filament-page-header', [
        'resource' => $fixture['resource'],
        '--panel' => $fixture['panel'],
        '--no-pages' => true,
    ])->assertSuccessful();

    $schemaPath = $fixture['resourceDirectory'].'/Schemas/OrderHeader.php';
    file_put_contents($schemaPath, "<?php\n// Keep this custom schema.\n");

    $this->artisan('make:filament-page-header', [
        'resource' => $fixture['resource'],
        '--panel' => $fixture['panel'],
        '--no-pages' => true,
    ])->assertSuccessful();

    expect(file_get_contents($schemaPath))->toContain('Keep this custom schema.');

    $this->artisan('make:filament-page-header', [
        'resource' => $fixture['resource'],
        '--panel' => $fixture['panel'],
        '--no-pages' => true,
        '--force' => true,
    ])->assertSuccessful();

    expect(file_get_contents($schemaPath))
        ->not->toContain('Keep this custom schema.')
        ->toContain('final class OrderHeader');
});

it('supports no-pages without modifying resource page source files', function (): void {
    $fixture = makeGeneratorFixture();

    $before = collect($fixture['pages'])
        ->mapWithKeys(fn (string $class, string $type): array => [$type => generatorFileContents($class)])
        ->all();

    $this->artisan('make:filament-page-header', [
        'resource' => 'Order',
        '--panel' => $fixture['panel'],
        '--no-pages' => true,
    ])->assertSuccessful();

    foreach ($fixture['pages'] as $type => $class) {
        expect(generatorFileContents($class))->toBe($before[$type]);
    }
});

it('rejects unavailable and conflicting page options before writing files', function (): void {
    $fixture = makeGeneratorFixture(['list', 'edit']);

    $this->artisan('make:filament-page-header', [
        'resource' => 'OrderResource',
        '--panel' => $fixture['panel'],
        '--page' => ['view'],
    ])->assertFailed();

    expect(is_file($fixture['resourceDirectory'].'/Schemas/OrderHeader.php'))->toBeFalse();

    $this->artisan('make:filament-page-header', [
        'resource' => 'OrderResource',
        '--panel' => $fixture['panel'],
        '--page' => ['edit'],
        '--no-pages' => true,
    ])->assertFailed();

    expect($fixture['resourceDirectory'].'/Schemas/OrderHeader.php')->not->toBeFile();
});

it('does not duplicate an existing HasPageHeader trait', function (): void {
    $fixture = makeGeneratorFixture(['edit'], withTrait: true);

    $this->artisan('make:filament-page-header', [
        'resource' => 'OrderResource',
        '--panel' => $fixture['panel'],
        '--page' => ['edit'],
    ])->assertSuccessful();

    $page = generatorFileContents($fixture['pages']['edit']);

    expect(substr_count($page, 'use MortalKiller\\FilamentPageHeader\\Concerns\\HasPageHeader;'))->toBe(1)
        ->and(substr_count($page, 'use HasPageHeader;'))->toBe(1);
});
