<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Pages\Enums\SubNavigationPosition;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Order;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages\ManageOrderNotes;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Resources\NavigationPages\ViewNavigationOrder;

beforeEach(function (): void {
    Schema::create('order_notes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('order_id');
        $table->string('body');
        $table->timestamps();
    });
    $this->order = Order::create(['reference' => 'NAV-42']);
    $this->order->notes()->create(['body' => 'Independent relation content']);
});

it('keeps real record routes and filters inaccessible resource pages', function (): void {
    $page = Livewire::test(ViewNavigationOrder::class, ['record' => $this->order->getRouteKey()]);
    $dom = navigationDom($page->html());
    $id = $this->order->getRouteKey();
    expect($dom->query('//*[@data-fph-sub-navigation]//a[contains(@href, "/navigation-orders/'.$id.'/notes")]')->length)->toBe(2);
    $page->assertDontSee('Restricted record page')->assertSee('Independent relation content');
    expect($dom->query('//*[@data-fph-header]//*[contains(@class, "fi-ta-ctn")]')->length)->toBe(0);
});

it('renders a ManageRelatedRecords page with its native table and header navigation', function (): void {
    Livewire::test(ManageOrderNotes::class, ['record' => $this->order->getRouteKey()])
        ->assertSeeHtml('data-fph-sub-navigation')->assertSee('Independent relation content')
        ->assertDontSee('Restricted record page');
});

it('leaves combined relation tabs and their content order owned by Filament', function (string $position): void {
    $page = Livewire::test(ViewNavigationOrder::class, ['record' => $this->order->getRouteKey(), 'combined' => true, 'contentPosition' => $position]);
    $dom = navigationDom($page->html());
    $tabs = $dom->query('//*[@role="tab"]');
    $labels = array_map(fn ($tab) => trim($tab->textContent), iterator_to_array($tabs));
    expect($labels)->toBe($position === 'before' ? ['View', 'Notes'] : ['Notes', 'View']);
    expect($dom->query('//*[@data-fph-header]//*[@role="tab"]')->length)->toBe(0);
    $page->set('activeRelationManager', '0')->assertSee('Independent relation content');
    $page->set('activeRelationManager', null)->assertSet('activeRelationManager', null)->assertSee('NAV-42');
})->with(['before', 'after']);

it('preserves all native navigation positions when integration is disabled', function (SubNavigationPosition $position): void {
    Filament::getCurrentPanel()->subNavigationPosition($position);
    $dom = navigationDom(Livewire::test(ViewNavigationOrder::class, ['record' => $this->order->getRouteKey(), 'headerNavigation' => false])->html());
    expect($dom->query('//*[@data-fph-sub-navigation]')->length)->toBe(0)
        ->and($dom->query('//*[contains(@class, "fi-page-has-sub-navigation-'.$position->value.'")]')->length)->toBe(1);
})->with([SubNavigationPosition::Start, SubNavigationPosition::End, SubNavigationPosition::Top]);
