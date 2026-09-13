<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Tests;

use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use MortalKiller\FilamentPageHeader\PageHeaderServiceProvider;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\TestPanelProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected $enablesPackageDiscoveries = true;

    protected function getPackageProviders($app): array
    {
        return [PageHeaderServiceProvider::class, TestPanelProvider::class];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('x', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $app['view']->addNamespace('page-header-tests', __DIR__.'/Fixtures/views');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['db']->connection()->getSchemaBuilder()->create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('reference');
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Filament::setCurrentPanel(Filament::getPanel('test'));
        Filament::bootCurrentPanel();
    }
}
