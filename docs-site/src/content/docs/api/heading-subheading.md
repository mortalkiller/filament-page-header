---
title: Heading and Subheading
description: Components for advanced heading and description composition.
---

The normal `Header::heading()` and `Header::description()` methods are enough for most pages. Use `Heading` and `Subheading` directly when you need to compose those areas with native schema components.

## Heading

`Heading` extends Filament's native `Entry`, so standard entry state, placeholders, visibility, and icon configuration remain available.

```php
use Filament\Support\Icons\Heroicon;
use MortalKiller\FilamentPageHeader\Components\Heading;
use MortalKiller\FilamentPageHeader\Components\Header;

Header::make()
    ->headingSchema([
        Heading::make('name')
            ->icon(Heroicon::OutlinedUser),
    ]);
```

### Additional method

| Method | Default | Description |
| --- | --- | --- |
| `html(bool\|Closure $condition = true): static` | `false` | Render the resolved state as HTML instead of escaping it. |

By default, heading content is escaped.

Only enable `html()` for HTML you trust or sanitize in your application.

## Subheading

`Subheading` extends `Heading` and exposes the same configuration surface. Its only package-specific difference is the rendered typography/view.

```php
use MortalKiller\FilamentPageHeader\Components\Subheading;

Header::make()
    ->descriptionSchema([
        Subheading::make('email'),
    ]);
```

## Shorthand equivalents

These two configurations are conceptually equivalent:

```php
Header::make()
    ->heading(fn ($record) => $record->name)
    ->description(fn ($record) => $record->email);
```

```php
Header::make()
    ->headingSchema([
        Heading::make('name'),
    ])
    ->descriptionSchema([
        Subheading::make('email'),
    ]);
```

Prefer the shorthand for simple text. Use the schema methods when you need richer composition.
