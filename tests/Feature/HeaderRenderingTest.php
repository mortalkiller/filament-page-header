<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;
use MortalKiller\FilamentPageHeader\Components\Heading;
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Order;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\CustomHeaderPage;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\EmptyPage;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\ExamplePage;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\CreateOrder;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\EditOrder;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\ListOrders;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\Pages\ViewOrder;

it('falls back when no header schema exists', function (): void {
    expect((new EmptyPage)->getHeader())->toBeNull();
});

it('does not opt other panels in', function (): void {
    Filament::setCurrentPanel(Panel::make()->id('other'));
    expect((new ExamplePage)->getHeader())->toBeNull();
});

it('honors an existing custom header', function (): void {
    Livewire::test(CustomHeaderPage::class)->assertSee('Existing custom header')->assertDontSeeHtml('data-fph-root');
});

it('renders a schema without a record and preserves one heading', function (): void {
    $component = Livewire::test(ExamplePage::class)
        ->assertSee('Example header')->assertSee('Supporting text')->assertSee('REF-100')
        ->assertSee('Confirm')->assertSeeHtml('data-fph-root');
    expect(preg_match_all('/<h1(?:\s|>)/', $component->html()))->toBe(1)
        ->and($component->instance()->getTitle())->toBe('Native title');
});

it('preserves browser-managed header state during Livewire morphs', function (): void {
    Livewire::test(ExamplePage::class)
        ->assertSeeHtml('data-fph-root')
        ->assertSeeHtml('wire:ignore.self');
});

it('overrides panel modes per page without mutating defaults', function (): void {
    $component = Livewire::test(ExamplePage::class);
    expect($component->instance()->getPageHeaderOptions()->toArray()['mode'])->toBe('compact')
        ->and(PageHeaderPlugin::get()->getOptions()->toArray()['mode'])->toBe('normal');
});

it('escapes untrusted heading text', function (): void {
    Livewire::test(ExamplePage::class)->set('headerText', '<script>alert(1)</script>')
        ->assertSeeHtml('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->assertDontSeeHtml('<script>alert(1)</script>');
});

it('only renders heading html after explicit opt in', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $plain = Schema::make($page)->components([Heading::make('title')->state(new HtmlString('<em>Safe title</em>'))]);
    $trusted = Schema::make($page)->components([Heading::make('title')->state('<em>Safe title</em>')->html()]);
    expect($plain->toHtml())->toContain('&lt;em&gt;Safe title&lt;/em&gt;')
        ->and($trusted->toHtml())->toContain('<em>Safe title</em>');
});

it('preserves all heading and action render hooks', function (): void {
    $hooks = [PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE, PanelsRenderHook::PAGE_HEADER_HEADING_AFTER, PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE, PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER];
    foreach ($hooks as $index => $hook) {
        FilamentView::registerRenderHook($hook, fn (): string => 'hook-'.$index, scopes: ExamplePage::class);
    }
    $component = Livewire::test(ExamplePage::class);
    foreach (array_keys($hooks) as $index) {
        $component->assertSee('hook-'.$index);
    }
});

it('binds the shared resource schema to the record and native enum', function (): void {
    $order = Order::create(['reference' => 'ORDER-42', 'status' => 'approved']);
    Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertSee('ORDER-42')->assertSee('Approved order')->assertSeeHtml('data-fph-root');
});

it('supports create and list pages without allocating a record', function (string $page): void {
    Livewire::test($page)->assertSeeHtml('data-fph-root');
    expect(Order::count())->toBe(0);
})->with([[CreateOrder::class], [ListOrders::class]]);

it('keeps edit form state and the native save form target', function (): void {
    $order = Order::create(['reference' => 'BEFORE']);
    Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
        ->assertSeeHtml('form="form"')->fillForm(['reference' => 'AFTER'])->call('save')->assertHasNoFormErrors();
    expect($order->refresh()->reference)->toBe('AFTER');
});

it('keeps confirmation actions functional without losing inputs', function (): void {
    Livewire::test(ExamplePage::class)->set('note', 'Unsaved note')
        ->mountAction('confirm')->assertActionMounted('confirm')
        ->callMountedAction()->assertSet('actionCount', 1)->assertSet('note', 'Unsaved note');
});

it('isolates plugin settings between instances', function (): void {
    $first = PageHeaderPlugin::make()->mode(HeaderMode::Sticky);
    $second = PageHeaderPlugin::make();
    expect($first->getOptions()->toArray()['mode'])->toBe('sticky')
        ->and($second->getOptions()->toArray()['mode'])->toBe('normal');
});
