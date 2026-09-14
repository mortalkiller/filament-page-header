<?php

declare(strict_types=1);

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Livewire\Livewire;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;
use MortalKiller\FilamentPageHeader\HeaderOptions;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\ExamplePage;

it('selects compact blocks without changing the scroll mode or another header', function (): void {
    $header = Header::make()->whenCompact(fn (CompactHeader $compact) => $compact
        ->show(HeaderPart::Image, HeaderPart::Description));
    expect($header->isSlotHiddenWhenCompact('leading'))->toBeFalse()
        ->and($header->isSlotHiddenWhenCompact('description'))->toBeFalse()
        ->and($header->isSlotHiddenWhenCompact('badges'))->toBeTrue()
        ->and($header->isSlotHiddenWhenCompact('heading'))->toBeFalse()
        ->and($header->resolveOptions(new HeaderOptions)->toArray()['mode'])->toBe('normal')
        ->and(Header::make()->isSlotHiddenWhenCompact('description'))->toBeTrue();
});

it('preserves native hidden fields and marks only unselected fields for compaction', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $header = Header::make()->heading('Product')->metadata([
        TextEntry::make('supplier.name')->state('Visible supplier')->extraAttributes(['data-existing' => 'kept']),
        TextEntry::make('reference')->state('Expanded reference'),
        TextEntry::make('secret')->state('Never rendered')->hidden(),
    ])->whenCompact(fn (CompactHeader $compact) => $compact
        ->only(HeaderPart::Metadata, ['supplier.name', 'secret'])->show(HeaderPart::Metadata));
    $html = Schema::make($page)->components([$header])->toHtml();
    expect($html)->toContain('Visible supplier', 'Expanded reference', 'data-existing="kept"', 'data-fph-exclude-compact="true"')
        ->not->toContain('Never rendered');
    expect($header->isSlotHiddenWhenCompact('metadata'))->toBeFalse();
    $entries = $header->getChildSchema('metadata')->getComponents();
    expect($entries[0]->getExtraAttributes()['data-fph-exclude-compact'])->toBe('false')
        ->and($entries[1]->getExtraAttributes()['data-fph-exclude-compact'])->toBe('true');
});

it('collapses a block when none of its selected fields are available', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $header = Header::make()->heading('Product')->metadata([
        TextEntry::make('secret')->state('Never rendered')->hidden(),
        TextEntry::make('reference')->state('Expanded only'),
    ])->whenCompact(fn (CompactHeader $compact) => $compact->only(HeaderPart::Metadata, ['secret']));
    Schema::make($page)->components([$header])->toHtml();
    expect($header->isSlotHiddenWhenCompact('metadata'))->toBeTrue();
});

it('rejects invalid compact field selections', function (): void {
    expect(fn () => (new CompactHeader)->only(HeaderPart::Image, ['photo']))->toThrow(InvalidArgumentException::class);
    expect(fn () => (new CompactHeader)->only(HeaderPart::Metadata, ['']))->toThrow(InvalidArgumentException::class);
    expect(fn () => (new CompactHeader)->only(HeaderPart::Metadata, [123]))->toThrow(InvalidArgumentException::class);
});

it('replaces compact configuration and supports an empty selection', function (): void {
    $header = Header::make()->whenCompact(fn (CompactHeader $compact) => $compact->show(HeaderPart::Metadata))
        ->whenCompact(function (CompactHeader $compact): void {});

    foreach (HeaderPart::cases() as $part) {
        expect($header->isSlotHiddenWhenCompact($part->value))->toBeTrue();
    }
    expect($header->isSlotHiddenWhenCompact('heading'))->toBeFalse();
});

it('selects a keyed layout as a whole and collapses an empty field list', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $layout = Grid::make()->key('identity')->schema([
        TextEntry::make('code')->state('Nested code'),
    ]);
    $header = Header::make()->heading('Product')->schema([$layout])
        ->whenCompact(fn (CompactHeader $compact) => $compact->only(HeaderPart::Content, ['identity']));
    Schema::make($page)->components([$header])->toHtml();
    expect($header->isSlotHiddenWhenCompact('default'))->toBeFalse()
        ->and($layout->getExtraAttributes()['data-fph-exclude-compact'])->toBe('false');

    $header->whenCompact(fn (CompactHeader $compact) => $compact->only(HeaderPart::Content, [])->show(HeaderPart::Content));
    $header->renderSlot('default');
    expect($header->isSlotHiddenWhenCompact('default'))->toBeTrue();
});
