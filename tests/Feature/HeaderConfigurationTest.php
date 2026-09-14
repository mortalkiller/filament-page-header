<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Schemas\Schema;
use Livewire\Livewire;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\ExamplePage;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\OrderResource;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\CreateOrder;

it('rejects a resource schema without a callable configure method', function (): void {
    expect(fn () => PageHeaderPlugin::get()->schemaFor(OrderResource::class, stdClass::class))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects schema mappings for classes that are not resources', function (): void {
    expect(fn () => PageHeaderPlugin::get()->schemaFor(stdClass::class, AlternateHeaderConfiguration::class))
        ->toThrow(InvalidArgumentException::class);
});

it('reports a useful error without a current panel', function (): void {
    Filament::setCurrentPanel(null);
    expect(fn () => PageHeaderPlugin::get())->toThrow(LogicException::class, 'not registered');
});

it('reports a useful error when the current panel has not enabled the plugin', function (): void {
    Filament::setCurrentPanel(Panel::make()->id('without-headers'));
    expect(fn () => PageHeaderPlugin::get())->toThrow(LogicException::class, 'not registered');
});

it('allows explicit schema mappings without leaking into another plugin instance', function (): void {
    PageHeaderPlugin::get()->schemaFor(OrderResource::class, AlternateHeaderConfiguration::class);
    expect((new CreateOrder)->getPageHeaderSchemaClass())->toBe(AlternateHeaderConfiguration::class)
        ->and(PageHeaderPlugin::make()->getSchemaFor(OrderResource::class))->toBeNull();
});

it('omits empty optional content while preserving the main heading', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $schema = Schema::make($page)->components([
        Header::make()->heading('Only a title')->description(null)->badges([])->metadata([])->summary([]),
    ]);
    expect($schema->toHtml())->toContain('Only a title')
        ->not->toContain('class="fph-subheading"')
        ->not->toContain('class="fph-inline fph-badges"')
        ->not->toContain('class="fph-summary"');
});

it('keeps unsaved input when dynamic header content changes', function (): void {
    Livewire::test(ExamplePage::class)
        ->set('note', 'Keep this unsaved note')
        ->set('status', 'approved')
        ->assertSee('approved')
        ->assertSet('note', 'Keep this unsaved note')
        ->assertSet('actionCount', 0);
});

it('preserves denied action visibility', function (): void {
    Livewire::test(ExamplePage::class)->assertDontSee('Forbidden action')->assertSet('actionCount', 0);
});

class AlternateHeaderConfiguration
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([Header::make()->heading('Alternate header')]);
    }
}
