---
title: HeaderOptions
description: Immutable panel and page options for sticky and compact behavior.
---

`HeaderOptions` is an immutable value object. Configuration methods return a new instance rather than mutating the current one.

It is used internally by [`PageHeaderPlugin`](../page-header-plugin/) and can be customized per page through [`HasPageHeader::pageHeaderOptions()`](../has-page-header/#page-level-options).

## Defaults

A new instance is equivalent to:

```php
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\HeaderOptions;

$options = new HeaderOptions(
    mode: HeaderMode::Normal,
    breakpoints: [],
    offset: null,
    topbarSelector: '.fi-topbar-ctn, .fi-topbar',
    hideBreadcrumbsWhenCompact: true,
    compactBelow: null,
);
```

Direct construction is available, but the fluent methods are usually easier to read.

## Methods

| Method | Description |
| --- | --- |
| `mode(HeaderMode $mode): self` | Set the base mode and clear responsive/compact-below overrides. |
| `compactBelow(int $width): self` | Set the compact viewport threshold. Width must be positive. |
| `responsive(array $breakpoints): self` | Map non-negative minimum widths to `HeaderMode` cases. |
| `offset(?int $pixels): self` | Set a non-negative fixed top offset, or `null` for automatic handling. |
| `topbarSelector(?string $selector): self` | Set the selector used for topbar measurement, or `null` to disable it. |
| `hideBreadcrumbsWhenCompact(bool $condition = true): self` | Compatibility option retained for older integrations. Prefer `Header::breadcrumbs()` and `whenCompact()` for new code. |
| `toArray(): array` | Return the browser-ready normalized option payload. Normally used by the package renderer. |

## Page override

```php
public function pageHeaderOptions(HeaderOptions $defaults): HeaderOptions
{
    return $defaults
        ->offset(72)
        ->compactBelow(1024);
}
```

Because the object is immutable, this does **not** modify the panel's defaults or another page.

## Full replacement on the plugin

```php
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\HeaderOptions;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;

PageHeaderPlugin::make()
    ->options(
        new HeaderOptions(
            mode: HeaderMode::Sticky,
            offset: 64,
        ),
    );
```

Prefer the direct plugin methods such as `sticky()`, `compactBelow()`, and `offset()` unless you specifically want to construct the full value object.
