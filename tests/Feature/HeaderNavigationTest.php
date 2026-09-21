<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Livewire\Livewire;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\ExamplePage;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\NavigationCluster;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\NavigationPage;

it('preserves the existing outside breadcrumb layout by default', function (): void {
    $dom = navigationDom(Livewire::test(ExamplePage::class)->html());
    expect($dom->query('//*[@class="fph-breadcrumbs"]/following-sibling::*[@data-fph-root]')->length)->toBe(1)
        ->and($dom->query('//*[@data-fph-header]//*[contains(@class, "fi-breadcrumbs")]')->length)->toBe(0)
        ->and($dom->query('//*[@class="fph-breadcrumbs"]//a')->item(0)->getAttribute('href'))->toBe('/');
});

it('places native breadcrumbs inside the card or hides them', function (string $position, int $count): void {
    $dom = navigationDom(Livewire::test(NavigationPage::class, ['breadcrumbPosition' => $position])->html());
    expect($dom->query('//*[@data-fph-header]//*[@class="fph-breadcrumbs"]')->length)->toBe($count)
        ->and($dom->query('//*[@class="fph-breadcrumbs"]')->length)->toBe($count);
    if ($count) {
        expect($dom->query('//*[@class="fph-breadcrumbs"]//a')->item(0)->getAttribute('href'))->toBe('/');
    }
})->with([['inside', 1], ['hidden', 0]]);

it('respects breadcrumbs disabled on the panel', function (): void {
    Filament::getCurrentPanel()->breadcrumbs(false);
    Livewire::test(NavigationPage::class, ['breadcrumbPosition' => 'inside'])->assertDontSeeHtml('class="fph-breadcrumbs"');
});

it('renders native navigation once through the header with native links and permissions', function (): void {
    $component = Livewire::test(NavigationPage::class, ['headerNavigation' => true]);
    $component->assertDontSee('Restricted')->assertDontSee('Secret export');
    $dom = navigationDom($component->html());
    expect($dom->query('//*[@data-fph-header]//*[@data-fph-sub-navigation]')->length)->toBe(1)
        ->and($dom->query('//*[contains(@class, "fi-page-sub-navigation-sidebar-ctn")]')->length)->toBe(0)
        ->and($dom->query('//*[contains(concat(" ", normalize-space(@class), " "), " fi-page-sub-navigation-tabs ")]')->length)->toBe(1)
        ->and($dom->query('//*[contains(concat(" ", normalize-space(@class), " "), " fi-page-sub-navigation-dropdown ")]')->length)->toBe(1)
        ->and($dom->query('//*[@data-fph-sub-navigation]//a[@href="/test/navigation/overview" and @aria-current="page"]')->length)->toBeGreaterThan(0)
        ->and($dom->query('//*[@data-fph-sub-navigation]//a[@href="/test/navigation/export" and @target="_blank"]')->length)->toBe(2);
});

it('leaves navigation in its native location unless the header owns it', function (array $parameters): void {
    $dom = navigationDom(Livewire::test(NavigationPage::class, $parameters)->html());
    expect($dom->query('//*[@data-fph-sub-navigation]')->length)->toBe(0)
        ->and($dom->query('//*[contains(@class, "fi-page-sub-navigation-sidebar-ctn")]')->length)->toBe(1);
})->with([[[]], [['headerNavigation' => true, 'emptyHeader' => true]], [['headerNavigation' => true, 'customHeader' => true]]]);

it('does not suppress navigation in a panel without the plugin', function (): void {
    Filament::setCurrentPanel(Panel::make()->id('other'));
    $dom = navigationDom(Livewire::test(NavigationPage::class, ['headerNavigation' => true])->html());
    expect($dom->query('//*[@data-fph-sub-navigation]')->length)->toBe(0)
        ->and($dom->query('//*[contains(@class, "fi-page-sub-navigation-sidebar-ctn")]')->length)->toBe(1);
});

it('preserves navigation render hooks in their relocated components', function (): void {
    foreach ([PanelsRenderHook::PAGE_SUB_NAVIGATION_TOP_BEFORE, PanelsRenderHook::PAGE_SUB_NAVIGATION_TOP_AFTER,
        PanelsRenderHook::PAGE_SUB_NAVIGATION_MOBILE_MENU_BEFORE, PanelsRenderHook::PAGE_SUB_NAVIGATION_MOBILE_MENU_AFTER] as $index => $hook) {
        FilamentView::registerRenderHook($hook, fn () => 'navigation-hook-'.$index, scopes: NavigationPage::class);
    }
    $html = Livewire::test(NavigationPage::class, ['headerNavigation' => true])->html();
    foreach (range(0, 3) as $index) {
        expect(substr_count($html, 'navigation-hook-'.$index))->toBe(1);
    }
});

it('controls navigation compact visibility and retains outside breadcrumbs within the sticky surface', function (bool $retain): void {
    $dom = navigationDom(Livewire::test(NavigationPage::class, ['headerNavigation' => true, 'retainNavigation' => $retain])->html());
    $value = $retain ? 'false' : 'true';
    expect($dom->query('//*[@data-fph-sub-navigation and @data-fph-hide-compact="'.$value.'"]')->length)->toBe(1)
        ->and($dom->query('//*[@data-fph-root]//*[@class="fph-breadcrumbs"]')->length)->toBe($retain ? 1 : 0);
})->with([true, false]);

it('rejects field selection for native navigation rather than silently ignoring it', function (HeaderPart $part): void {
    expect(fn () => (new CompactHeader)->only($part, ['anything']))->toThrow(InvalidArgumentException::class);
})->with(fn () => [HeaderPart::Breadcrumbs, HeaderPart::SubNavigation]);

it('keeps legacy compact selections from opting into pinned outside breadcrumbs', function (): void {
    $dom = navigationDom(Livewire::test(NavigationPage::class, ['legacyCompact' => true, 'headerNavigation' => true])->html());
    expect($dom->query('//*[@data-fph-root]//*[@class="fph-breadcrumbs"]')->length)->toBe(0)
        ->and($dom->query('//*[@data-fph-sub-navigation and @data-fph-hide-compact="true"]')->length)->toBe(1);
});

it('preserves native cached navigation outside rendering and cluster entry redirects', function (): void {
    $page = Livewire::test(NavigationPage::class, ['headerNavigation' => true])->instance();
    expect($page->getCachedSubNavigation())->toHaveCount(2);
    Livewire::test(NavigationCluster::class)
        ->assertRedirect('/test/navigation/overview');
});

it('does not render an empty navigation strip', function (): void {
    Livewire::test(NavigationPage::class, ['headerNavigation' => true, 'emptyNavigation' => true])
        ->assertSeeHtml('data-fph-header')->assertDontSeeHtml('data-fph-sub-navigation');
});

it('allows native navigation to supply breadcrumbs without recursive header rendering', function (): void {
    $dom = navigationDom(Livewire::test(NavigationPage::class, ['headerNavigation' => true, 'navigationBreadcrumbs' => true])->html());
    expect($dom->query('//*[@class="fph-breadcrumbs"]//a[@href="/test/navigation/overview"]')->length)->toBe(1)
        ->and($dom->query('//*[@data-fph-sub-navigation]')->length)->toBe(1);
});

it('allows the sub-navigation condition to inspect native cached navigation', function (): void {
    $dom = navigationDom(Livewire::test(NavigationPage::class, ['navigationCondition' => true, 'navigationBreadcrumbs' => true])->html());
    expect($dom->query('//*[@data-fph-sub-navigation]')->length)->toBe(1)
        ->and($dom->query('//*[contains(@class, "fi-page-sub-navigation-sidebar-ctn")]')->length)->toBe(0);
});
