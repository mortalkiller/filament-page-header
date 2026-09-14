<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Panel;
use Filament\Schemas\Schema;
use Livewire\Livewire;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\HeaderOptions;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\ExamplePage;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\OrderResource;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\ListOrders;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Schemas\OrderHeader;

it('omits empty optional slots instead of leaving empty rows', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->heading('Only a title')->description(null)
            ->metadata([TextEntry::make('secret')->state('Hidden')->hidden()])
            ->badges([])->leading([])->summary([]),
    ])->toHtml();

    expect($html)->toContain('Only a title')
        ->not->toContain('class="fph-subheading"')
        ->not->toContain('class="fph-metadata')
        ->not->toContain('class="fph-summary"')
        ->not->toContain('class="fph-leading"')
        ->not->toContain('class="fph-inline fph-badges"');
});

it('retains essential slots and allows explicitly choosing compact content', function (): void {
    $layout = Header::make()->hideWhenCompact(['metadata', 'description']);

    expect($layout->isSlotHiddenWhenCompact('metadata'))->toBeTrue()
        ->and($layout->isSlotHiddenWhenCompact('description'))->toBeTrue()
        ->and($layout->isSlotHiddenWhenCompact('heading'))->toBeFalse()
        ->and($layout->isSlotHiddenWhenCompact('badges'))->toBeFalse()
        ->and($layout->isSlotHiddenWhenCompact('default'))->toBeFalse();
});

it('rejects attempts to hide the page title in compact mode', function (): void {
    expect(fn () => Header::make()->hideWhenCompact(['heading']))
        ->toThrow(InvalidArgumentException::class);
});

it('keeps breadcrumbs configurable separately from layout slots', function (): void {
    $original = new HeaderOptions;
    $configured = $original->hideBreadcrumbsWhenCompact(false);

    expect($configured->toArray()['hideBreadcrumbsWhenCompact'])->toBeFalse()
        ->and($original->toArray()['hideBreadcrumbsWhenCompact'])->toBeTrue();
});

it('keeps resource schema mappings isolated between panels', function (): void {
    $first = PageHeaderPlugin::make()->schemaFor(OrderResource::class, OrderHeader::class);
    $second = PageHeaderPlugin::make();

    expect($first->getSchemaFor(OrderResource::class))->toBe(OrderHeader::class)
        ->and($second->getSchemaFor(OrderResource::class))->toBeNull();
});

it('uses an explicit resource schema mapping', function (): void {
    PageHeaderPlugin::get()->schemaFor(OrderResource::class, AlternativeHeaderForTest::class);

    expect((new ListOrders)->getPageHeaderSchemaClass())->toBe(AlternativeHeaderForTest::class);
    Livewire::test(ListOrders::class)->assertSee('Mapped resource header');
});

it('keeps an inline page schema ahead of resource mappings', function (): void {
    PageHeaderPlugin::get()->schemaFor(OrderResource::class, AlternativeHeaderForTest::class);

    Livewire::test(ExamplePage::class)->assertSee('Example header')->assertDontSee('Mapped resource header');
});

it('rejects invalid resource schema configuration early', function (): void {
    expect(fn () => PageHeaderPlugin::make()->schemaFor(OrderResource::class, HeaderOptions::class))
        ->toThrow(InvalidArgumentException::class, 'public static configure');
});

it('rejects mappings for classes that are not resources', function (): void {
    expect(fn () => PageHeaderPlugin::make()->schemaFor(HeaderOptions::class, OrderHeader::class))
        ->toThrow(InvalidArgumentException::class, 'Filament resource');
});

it('explains when the current panel has not registered the plugin', function (): void {
    Filament::setCurrentPanel(Panel::make()->id('not-enabled'));

    expect(fn () => PageHeaderPlugin::get())
        ->toThrow(LogicException::class, 'not registered for the current panel');
});

final class AlternativeHeaderForTest
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([Header::make()->heading('Mapped resource header')]);
    }
}
