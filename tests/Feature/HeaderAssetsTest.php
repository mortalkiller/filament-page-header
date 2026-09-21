<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\Livewire;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\ExamplePage;

function headerAssetDocument(): DOMXPath
{
    $document = new DOMDocument;
    @$document->loadHTML(Blade::render('<x-filament-panels::layout.base>Page content</x-filament-panels::layout.base>'));

    return new DOMXPath($document);
}

it('includes the header stylesheet once in the initial head after the panel theme', function (): void {
    Filament::getCurrentPanel()->theme(new HtmlString('<link rel="stylesheet" href="/custom-panel-theme.css">'));

    $document = headerAssetDocument();
    $links = $document->query('//link[@rel="stylesheet" and contains(@href, "/css/mortalkiller/filament-page-header/page-header.css")]');

    expect($links->length)->toBe(1)
        ->and($links->item(0)->parentNode->nodeName)->toBe('head')
        ->and($links->item(0)->hasAttribute('data-navigate-track'))->toBeTrue()
        ->and($document->query('//head/link[contains(@href, "page-header.css")]/preceding-sibling::link[@href="/custom-panel-theme.css"]')->length)->toBe(1);
});

it('does not leak the stylesheet into a panel without the plugin after an enabled panel boots', function (): void {
    Filament::setCurrentPanel(Panel::make()->id('native')->path('native'));

    expect(headerAssetDocument()->query('//link[contains(@href, "page-header.css")]')->length)->toBe(0);
});

it('does not duplicate or leak styles when multiple enabled panels boot', function (): void {
    $second = Panel::make()->id('second')->path('second')->plugin(PageHeaderPlugin::make());
    Filament::setCurrentPanel($second);
    $second->boot();

    expect(headerAssetDocument()->query('//head/link[contains(@href, "page-header.css")]')->length)->toBe(1);

    Filament::setCurrentPanel(Filament::getPanel('test'));

    expect(headerAssetDocument()->query('//head/link[contains(@href, "page-header.css")]')->length)->toBe(1);
});

it('keeps Alpine behavior without deferring the header stylesheet to Alpine', function (): void {
    $html = Livewire::test(ExamplePage::class)->html();
    $document = new DOMDocument;
    @$document->loadHTML($html);
    $root = (new DOMXPath($document))->query('//*[@data-fph-root]')->item(0);

    expect($root)->not->toBeNull()
        ->and($root->hasAttribute('x-load-css'))->toBeFalse()
        ->and($root->hasAttribute('x-load'))->toBeTrue()
        ->and($root->getAttribute('x-load-src'))->toContain('/components/page-header.js')
        ->and($root->getAttribute('x-data'))->toStartWith('pageHeader(');
});

it('registers the styles hook once when the plugin is registered twice on a panel', function (): void {
    $panel = Panel::make()
        ->id('duplicate')
        ->path('duplicate')
        ->plugin(PageHeaderPlugin::make())
        ->plugin(PageHeaderPlugin::make()->sticky());

    Filament::setCurrentPanel($panel);
    $panel->boot();

    $plugin = $panel->getPlugin(PageHeaderPlugin::ID);

    expect(headerAssetDocument()->query('//head/link[contains(@href, "page-header.css")]')->length)->toBe(1)
        ->and($plugin)->toBeInstanceOf(PageHeaderPlugin::class)
        ->and($plugin->getOptions()->toArray()['mode'])->toBe('sticky');
});
