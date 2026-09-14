<?php

declare(strict_types=1);

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Icons\Heroicon;
use Livewire\Livewire;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Components\MetadataEntry;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\ExamplePage;

it('renders an initials avatar and native metadata without manual layout', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->heading('Mariana Costa')->description('Contact details')
            ->avatar(null)->initials('  Mariana   Costa  ')
            ->badges([TextEntry::make('status')->state('Active')->badge()])
            ->metadata([TextEntry::make('reference')->state('C000042')])
            ->summary([TextEntry::make('quotes')->state(12)]),
    ])->toHtml();

    expect($html)->toContain('MC', 'Mariana Costa', 'Contact details', 'C000042', 'fph-summary')
        ->not->toContain('<img');
    expect(substr_count($html, '<h1 '))->toBe(1);
});

it('escapes avatar names and only accepts safe image URLs', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->heading('<script>unsafe</script>')->initials('<img onerror=alert(1)>')
            ->avatar('javascript:alert(1)'),
    ])->toHtml();

    expect($html)->not->toContain('<script>', 'src="javascript:', '<img onerror=')
        ->toContain('&lt;script&gt;unsafe&lt;/script&gt;');
});

it('renders a configured photo with an accessible text alternative', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->heading('Mariana Costa')->avatar('/avatar.svg')->initials('Mariana Costa'),
    ])->toHtml();

    expect($html)->toContain('src="/avatar.svg"', 'alt="Mariana Costa"');
});

it('resolves the identity visual from image to initials to icon', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->heading('Photo')->avatar('/avatar.svg')->initials('Mariana Costa')->icon(Heroicon::OutlinedUser),
        Header::make()->heading('Initials')->initials('Carlos Silva')->icon(Heroicon::OutlinedUser),
        Header::make()->heading('Icon')->icon(Heroicon::OutlinedUser),
    ])->toHtml();

    expect($html)
        ->toContain('src="/avatar.svg"', 'CS', 'class="fph-avatar fph-icon"')
        ->not->toContain('MC');
});

it('applies semantic and explicit colors to initials avatars', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->heading('Mariana Costa')->initials('Mariana Costa')
            ->initialsColor('primary')->initialsTextColor('white'),
        Header::make()->heading('Carlos Silva')->initials('Carlos Silva')
            ->initialsColor(Color::Blue),
    ])->toHtml();

    $primary = FilamentColor::getColor('primary');
    $primaryText = Color::calculateContrastRatio($primary[600], $primary[950]) >= Color::calculateContrastRatio($primary[600], $primary[50])
        ? $primary[950]
        : $primary[50];
    $blueText = Color::calculateContrastRatio(Color::Blue[600], Color::Blue[950]) >= Color::calculateContrastRatio(Color::Blue[600], Color::Blue[50])
        ? Color::Blue[950]
        : Color::Blue[50];

    expect($html)
        ->toContain('--fph-avatar-background: '.$primary[600], '--fph-avatar-text: white')
        ->toContain('--fph-avatar-background: '.Color::Blue[600], '--fph-avatar-text: '.$blueText)
        ->not->toContain('--fph-avatar-text: '.$primaryText);
});

it('hides badge labels by default without changing metadata labels', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $header = Header::make()->badges(fn (): array => [TextEntry::make('status')->state('Active')->badge()])
        ->metadata([TextEntry::make('reference')->label('Customer reference')->state('C42')]);
    Schema::make($page)->components([$header])->toHtml();

    expect($header->getChildSchema('badges')->getComponents()[0]->isLabelHidden())->toBeTrue();
    expect($header->getChildSchema('metadata')->getComponents()[0]->isLabelHidden())->toBeFalse();
});

it('keeps per-header modes isolated from panel defaults', function (): void {
    $defaults = PageHeaderPlugin::make()->sticky()->compactBelow(1024)->getOptions();
    $header = Header::make()->compact();

    expect($header->resolveOptions($defaults)->toArray()['mode'])->toBe('compact');
    expect($header->resolveOptions($defaults)->toArray()['breakpoints'])->toBe([]);
    expect($defaults->toArray()['mode'])->toBe('sticky');
    expect($defaults->toArray()['compactBelow'])->toBe(1024);
});

it('hides secondary content in compact mode unless explicitly retained', function (): void {
    $header = Header::make();

    foreach (['description', 'metadata', 'summary', 'default'] as $slot) {
        expect($header->isSlotHiddenWhenCompact($slot))->toBeTrue();
    }
    expect($header->retainSummaryWhenCompact()->isSlotHiddenWhenCompact('summary'))->toBeFalse();
    expect($header->isSlotHiddenWhenCompact('heading'))->toBeFalse();
});

it('validates compact breakpoint and allows returning to normal mode', function (): void {
    expect(fn () => PageHeaderPlugin::make()->compactBelow(0))->toThrow(InvalidArgumentException::class);
    $options = PageHeaderPlugin::make()->sticky()->compactBelow(1024)->normal()->getOptions()->toArray();
    expect($options['mode'])->toBe('normal');
    expect($options['compactBelow'])->toBeNull();
});

it('places a field icon around the complete native entry', function (IconPosition $position): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->metadata([
            MetadataEntry::make('reference')->label('Reference')->state('C42')->copyable()
                ->fieldIcon(fn (string $state) => $state === 'C42' ? Heroicon::OutlinedHashtag : null)
                ->fieldIconPosition(fn () => $position),
        ]),
    ])->toHtml();

    expect($html)->toContain('fph-field-icon', 'data-fph-icon-position="'.$position->value.'"', 'Reference', 'C42', 'copyable');
    expect(substr_count($html, 'class="fph-field-icon"'))->toBe(1);
})->with([IconPosition::Before, IconPosition::After]);

it('keeps native formatting links and actions inside an icon metadata entry', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->metadata([
            MetadataEntry::make('created_at')->state('2026-05-22')->date('d/m/Y')
                ->fieldIcon(Heroicon::OutlinedCalendar)->url('/customer')
                ->belowContent([Action::make('contact')->label('Contact customer')->url('mailto:customer@example.com')]),
        ]),
    ])->toHtml();

    expect($html)->toContain('22/05/2026', 'href="/customer"', 'mailto:customer@example.com', 'Contact customer');
});

it('omits icon space and hidden metadata without changing native escaping', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->metadata([
            MetadataEntry::make('name')->state('<script>unsafe</script>')->fieldIcon(fn () => null),
            MetadataEntry::make('secret')->state('Private value')->fieldIcon(Heroicon::OutlinedLockClosed)->hidden(),
        ]),
    ])->toHtml();

    expect($html)->toContain('&lt;script&gt;unsafe&lt;/script&gt;')->not->toContain('fph-field-icon', 'Private value', '<script>');
});

it('supports positive field icon sizes and validates dynamic sizes', function (): void {
    expect(MetadataEntry::make('code')->getFieldIconSize())->toBe(24);
    expect(MetadataEntry::make('code')->fieldIconSize(fn () => 32)->getFieldIconSize())->toBe(32);
    expect(fn () => MetadataEntry::make('code')->fieldIconSize(0))->toThrow(InvalidArgumentException::class);
    expect(fn () => MetadataEntry::make('code')->fieldIconSize(fn () => -1)->getFieldIconSize())->toThrow(InvalidArgumentException::class);
});

it('renders the configured field icon size', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $html = Schema::make($page)->components([
        Header::make()->metadata([MetadataEntry::make('code')->state('SKU-42')->fieldIcon(Heroicon::OutlinedTag)->fieldIconSize(32)]),
    ])->toHtml();

    expect($html)->toContain('--fph-field-icon-size: 32px');
});

it('supports product images and native copyable descriptions retained when compact', function (): void {
    $page = Livewire::test(ExamplePage::class)->instance();
    $header = Header::make()->image('/product.svg')->initials('Basic Shirt')
        ->descriptionSchema([TextEntry::make('code')->state('SUP-42')->hiddenLabel()->copyable()])
        ->hideWhenCompact(['metadata', 'summary', 'default']);
    $html = Schema::make($page)->components([$header])->toHtml();

    expect($html)->toContain('fph-image', 'src="/product.svg"', 'SUP-42', 'copyable');
    expect($header->isSlotHiddenWhenCompact('description'))->toBeFalse();
    expect($header->avatar('/customer.svg')->isImage())->toBeFalse();
});
