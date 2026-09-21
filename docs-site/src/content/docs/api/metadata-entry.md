---
title: MetadataEntry
description: TextEntry-compatible metadata fields with field-level icons.
---

`MetadataEntry` extends Filament's native `TextEntry`. All normal `TextEntry` formatting, visibility, links, copyable state, colors, placeholders, and actions remain available.

Its additional API places an icon beside the **complete field** — label and value together — instead of placing the icon inside the value.

## Example

```php
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use MortalKiller\FilamentPageHeader\Components\MetadataEntry;

MetadataEntry::make('customer_number')
    ->label('Customer number')
    ->fieldIcon(Heroicon::OutlinedHashtag)
    ->fieldIconPosition(IconPosition::Before)
    ->fieldIconSize(24)
    ->copyable();
```

## Methods

| Method | Default | Description |
| --- | --- | --- |
| `fieldIcon(string\|BackedEnum\|Closure\|null $icon): static` | `null` | Set the icon displayed beside the whole field. |
| `fieldIconPosition(IconPosition\|Closure $position): static` | `IconPosition::Before` | Place the field icon before or after the label/value block. |
| `fieldIconSize(int\|Closure $size): static` | `24` | Set the icon size in CSS pixels. The resolved value must be a positive integer. |

## Field icon vs native TextEntry icon

These two APIs solve different layout problems:

```php
MetadataEntry::make('email')
    ->fieldIcon(Heroicon::OutlinedEnvelope)
    ->icon(Heroicon::OutlinedCheck);
```

- `fieldIcon()` belongs to Filament Page Header and wraps the complete field.
- Native `icon()` belongs to `TextEntry` and remains part of the value rendering.

Use whichever placement matches the information hierarchy you want.

## Closures

All three additional methods support closures:

```php
MetadataEntry::make('email')
    ->fieldIcon(fn ($record) => $record->verified
        ? Heroicon::OutlinedCheckBadge
        : Heroicon::OutlinedEnvelope)
    ->fieldIconSize(fn () => 20);
```

Returning `null` from `fieldIcon()` removes the icon and its reserved space.
