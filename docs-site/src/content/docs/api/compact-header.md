---
title: CompactHeader
description: Select blocks and individual fields that remain visible in compact mode.
---

`CompactHeader` is configured inside `Header::whenCompact()`.

Each callback starts with no optional parts selected. Add the parts you want to preserve with `show()` or select specific named fields with `only()`.

## Example

```php
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

Header::make()
    ->compact()
    ->whenCompact(fn (CompactHeader $compact) => $compact
        ->show(
            HeaderPart::Image,
            HeaderPart::Description,
            HeaderPart::Badges,
        )
        ->only(HeaderPart::Metadata, [
            'reference',
            'customer',
        ]));
```

## Methods

| Method | Description |
| --- | --- |
| `show(HeaderPart ...$parts): self` | Keep one or more complete header parts visible in compact mode. |
| `only(HeaderPart $part, array $fields): self` | Keep only the listed direct named fields from a part, and mark that part visible. |
| `isVisible(HeaderPart $part): bool` | Inspect whether a part is selected. Usually only needed by package internals or advanced extensions. |
| `getFields(HeaderPart $part): ?array` | Read the selected fields for a part. Usually only needed by package internals or advanced extensions. |

## Available parts

See [Enums](../enums/) for the complete `HeaderPart` list.

Typical configuration:

```php
$compact
    ->show(HeaderPart::Image)
    ->show(HeaderPart::Badges)
    ->only(HeaderPart::Metadata, ['reference']);
```

## Field selection

`only()` matches the direct component name/key inside the selected slot.

Given:

```php
Header::make()->metadata([
    MetadataEntry::make('reference'),
    MetadataEntry::make('customer'),
    MetadataEntry::make('created_at'),
]);
```

you can retain only:

```php
->whenCompact(fn (CompactHeader $compact) => $compact
    ->only(HeaderPart::Metadata, [
        'reference',
        'customer',
    ]));
```

Native visibility conditions still apply. Selecting a hidden field does not force it to render.

## Parts that do not support `only()`

Use `show()` for:

- `HeaderPart::Image`
- `HeaderPart::Breadcrumbs`
- `HeaderPart::SubNavigation`

Image and native navigation are complete blocks rather than field collections.

```php
$compact->show(
    HeaderPart::Breadcrumbs,
    HeaderPart::SubNavigation,
);
```

Passing those parts to `only()` throws an `InvalidArgumentException`.

## Empty field arrays

An empty array does not produce a visible selected part:

```php
$compact->only(HeaderPart::Metadata, []);
```

Use `show(HeaderPart::Metadata)` if you want the complete metadata block.
