---
title: PageHeaderPlugin
description: Panel-level configuration for Filament Page Header.
---

`PageHeaderPlugin` registers the package on a Filament panel and defines defaults inherited by headers in that panel.

## Registration

```php
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;

$panel->plugin(
    PageHeaderPlugin::make(),
);
```

Register the plugin separately on each panel that should use custom page headers.

## Common configuration

```php
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;

$panel->plugin(
    PageHeaderPlugin::make()
        ->sticky()
        ->compactBelow(1024),
);
```

## Methods

| Method | Description |
| --- | --- |
| `make(): self` | Resolve a plugin instance from the container. |
| `normal(): self` | Use normal scrolling as the panel default. |
| `sticky(): self` | Keep the full header pinned after it reaches the sticky edge. |
| `compact(): self` | Switch to the compact layout after the header reaches the sticky edge. |
| `mode(HeaderMode $mode): self` | Set the panel default using a `HeaderMode` enum case. |
| `compactBelow(int $width): self` | Use compact behavior strictly below the given viewport width. |
| `responsive(array $breakpoints): self` | Map minimum viewport widths to `HeaderMode` cases. |
| `offset(?int $pixels): self` | Override the sticky top offset. Use `null` for automatic resolution. |
| `topbarSelector(?string $selector): self` | Override the selector used to measure the Filament topbar. |
| `options(HeaderOptions $options): self` | Replace the complete options value object. |
| `schemaFor(string $resource, string $schema): self` | Explicitly map a Filament Resource to a reusable header schema class. |
| `get(): self` | Get the plugin registered on the current panel. |
| `getOptions(): HeaderOptions` | Read the current panel defaults. |
| `getSchemaFor(string $resource): ?string` | Read an explicit Resource schema mapping. |

## Responsive modes

```php
use MortalKiller\FilamentPageHeader\Enums\HeaderMode;
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;

PageHeaderPlugin::make()
    ->responsive([
        0 => HeaderMode::Normal,
        768 => HeaderMode::Sticky,
        1280 => HeaderMode::Compact,
    ]);
```

The array key is a minimum viewport width in CSS pixels. Breakpoints are sorted numerically before being sent to the browser.

Calling `normal()`, `sticky()`, `compact()`, or `mode()` replaces previously configured responsive modes.

## Compact below a breakpoint

```php
PageHeaderPlugin::make()
    ->sticky()
    ->compactBelow(1024);
```

At 1024 px and above the configured base mode applies. Below 1024 px the header uses compact behavior.

## Custom sticky offset

Normally the package measures the Filament topbar automatically. You can supply a fixed offset:

```php
PageHeaderPlugin::make()
    ->sticky()
    ->offset(72);
```

Or change the element used for automatic topbar measurement:

```php
PageHeaderPlugin::make()
    ->sticky()
    ->topbarSelector('.my-custom-topbar');
```

Pass `null` to `topbarSelector()` if no topbar should be measured.

## Map a Resource to a schema

```php
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Schemas\OrderHeader;

PageHeaderPlugin::make()
    ->schemaFor(OrderResource::class, OrderHeader::class);
```

The schema class must expose a public static `configure(Schema $schema): Schema` method.

Explicit mappings take precedence over convention-based schema discovery. See [HasPageHeader](../has-page-header/) for the full resolution flow.
