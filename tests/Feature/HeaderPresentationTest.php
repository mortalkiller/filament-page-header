<?php

declare(strict_types=1);

use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Icons\Heroicon;
use Livewire\Livewire;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Enums\AvatarShape;
use MortalKiller\FilamentPageHeader\Enums\AvatarStyle;
use MortalKiller\FilamentPageHeader\Tests\Fixtures\Pages\ExamplePage;

function presentationHtml(Header ...$headers): string
{
    return Schema::make(Livewire::test(ExamplePage::class)->instance())->components($headers)->toHtml();
}

it('draws avatars as circles with solid colours unless told otherwise', function (): void {
    $header = Header::make()->heading('Mariana Costa')->initials('Mariana Costa')->initialsBgColor('primary');

    expect($header->getAvatarShape())->toBe(AvatarShape::Circle)
        ->and($header->getAvatarStyle())->toBe(AvatarStyle::Solid)
        ->and(presentationHtml($header))
        ->toContain('data-fph-avatar-shape="circle"', 'data-fph-avatar-style="solid"')
        ->not->toContain('--fph-avatar-background-dark');
});

it('draws the initials and icon fallbacks as rounded squares', function (): void {
    $html = presentationHtml(
        Header::make()->heading('Mariana Costa')->initials('Mariana Costa')->avatarShape(AvatarShape::Rounded),
        Header::make()->heading('Icon')->icon(Heroicon::OutlinedUser)->avatarShape(fn (): AvatarShape => AvatarShape::Rounded),
    );

    expect(substr_count($html, 'data-fph-avatar-shape="rounded"'))->toBe(2);
});

it('tints a soft avatar with its colour in both themes', function (): void {
    $html = presentationHtml(
        Header::make()->heading('Mariana Costa')->initials('Mariana Costa')
            ->initialsBgColor('primary')->avatarStyle(AvatarStyle::Soft),
        Header::make()->heading('Icon')->icon(Heroicon::OutlinedUser)
            ->iconBgColor(Color::Blue)->iconColor(Color::Red)->avatarStyle(AvatarStyle::Soft),
    );

    $primary = FilamentColor::getColor('primary');

    expect($html)
        ->toContain('data-fph-avatar-style="soft"')
        ->toContain('--fph-avatar-background: '.$primary[100], '--fph-avatar-text: '.$primary[700])
        ->toContain('--fph-avatar-background-dark: '.$primary[900], '--fph-avatar-text-dark: '.$primary[300])
        // An explicit text colour is kept in both themes.
        ->toContain('--fph-avatar-background: '.Color::Blue[100], '--fph-avatar-text: '.Color::Red[600], '--fph-avatar-text-dark: '.Color::Red[600]);
});

it('rejects an avatar shape or style that is not a case of its enum', function (string $method): void {
    $header = Header::make()->{$method}(fn (): string => 'square');

    expect(fn () => $method === 'avatarShape' ? $header->getAvatarShape() : $header->getAvatarStyle())
        ->toThrow(InvalidArgumentException::class);
})->with(['avatarShape', 'avatarStyle']);

it('is contained in a card by default', function (): void {
    expect(Header::make()->isContained())->toBeTrue();

    Livewire::test(ExamplePage::class)
        ->assertSeeHtml('data-fph-contained="true"')
        ->assertSeeHtml('class="fph-header fi-section"');
});

it('sits on the page without the card when it is not contained', function (): void {
    expect(Header::make()->contained(false)->isContained())->toBeFalse()
        ->and(Header::make()->contained(fn (): bool => false)->isContained())->toBeFalse();

    Livewire::test(ExamplePage::class, ['contained' => false])
        ->assertSeeHtml('data-fph-contained="false"')
        ->assertSeeHtml('class="fph-header"')
        ->assertDontSeeHtml('class="fph-header fi-section"');
});
