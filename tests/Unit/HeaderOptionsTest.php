<?php

declare(strict_types=1);

use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\HeaderOptions;

it('defaults to normal mode and automatic topbar offset', function (): void {
    $options = new HeaderOptions;
    expect($options->toArray())->toBe([
        'mode' => 'normal',
        'breakpoints' => [],
        'offset' => null,
        'topbarSelector' => '.fi-topbar-ctn, .fi-topbar',
        'hideBreadcrumbsWhenCompact' => true,
        'compactBelow' => null,
    ]);
});

it('returns independent immutable configurations', function (): void {
    $original = new HeaderOptions;
    $changed = $original->mode(HeaderMode::Compact)->offset(72);
    expect($original->toArray()['mode'])->toBe('normal')
        ->and($changed->toArray()['mode'])->toBe('compact')
        ->and($changed->toArray()['offset'])->toBe(72);
});

it('sorts responsive modes by minimum viewport width', function (): void {
    $options = (new HeaderOptions)->responsive([1024 => HeaderMode::Sticky, 640 => HeaderMode::Compact]);
    expect($options->toArray()['breakpoints'])->toBe([
        ['minWidth' => 640, 'mode' => 'compact'],
        ['minWidth' => 1024, 'mode' => 'sticky'],
    ]);
});

it('rejects negative offsets', function (): void {
    (new HeaderOptions)->offset(-1);
})->throws(InvalidArgumentException::class);

it('rejects invalid breakpoint definitions', function (): void {
    (new HeaderOptions)->responsive(['tablet' => HeaderMode::Compact]);
})->throws(InvalidArgumentException::class);

it('allows automatic offset to be disabled and breadcrumbs retained', function (): void {
    $options = (new HeaderOptions)->topbarSelector(null)->offset(0)->hideBreadcrumbsWhenCompact(false);
    expect($options->toArray()['topbarSelector'])->toBeNull()
        ->and($options->toArray()['offset'])->toBe(0)
        ->and($options->toArray()['hideBreadcrumbsWhenCompact'])->toBeFalse();
});
