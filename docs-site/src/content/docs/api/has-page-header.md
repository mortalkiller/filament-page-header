---
title: HasPageHeader
description: Page trait, schema resolution, record context, and page-level override points.
---

Add `HasPageHeader` to each Filament page that should opt into the package.

```php
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

class EditCustomer extends EditRecord
{
    use HasPageHeader;
}
```

The panel must also have [`PageHeaderPlugin`](../page-header-plugin/) registered.

## Main override points

| Method | Use it for |
| --- | --- |
| `headerSchema(Schema $schema): Schema` | Define an inline header schema or delegate to a reusable schema class. |
| `getPageHeaderRecord(): Model\|array\|null` | Change the record/context supplied to header schema components and closures. |
| `pageHeaderOptions(HeaderOptions $defaults): HeaderOptions` | Override sticky/compact/offset options for one page. |
| `getPageHeaderSchemaClass(): ?string` | Advanced override for reusable schema resolution. Normally convention discovery or `schemaFor()` is enough. |

## Inline schema

```php
use Filament\Schemas\Schema;
use MortalKiller\FilamentPageHeader\Components\Header;

public function headerSchema(Schema $schema): Schema
{
    return $schema->components([
        Header::make()
            ->heading(fn ($record) => $record->name),
    ]);
}
```

An inline `headerSchema()` on the page takes precedence over normal reusable schema discovery.

If the resolved schema is empty, the page falls back to Filament's native header.

## Reusable Resource schema convention

For:

```text
App\Filament\Resources\Orders\OrderResource
```

the trait can discover:

```text
App\Filament\Resources\Orders\Schemas\OrderHeader
```

when it exposes:

```php
use Filament\Schemas\Schema;

final class OrderHeader
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // ...
        ]);
    }
}
```

You can also map a class explicitly with [`PageHeaderPlugin::schemaFor()`](../page-header-plugin/#map-a-resource-to-a-schema).

## Record context

By default, Resource pages use their current Resource record. Pages without a record receive `null`.

Override the context for tenants, parent records, singleton settings, custom pages, or arrays:

```php
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

public function getPageHeaderRecord(): ?Model
{
    return Filament::getTenant();
}
```

Array contexts are supported:

```php
public function getPageHeaderRecord(): array
{
    return [
        'name' => 'System status',
        'environment' => app()->environment(),
    ];
}
```

This changes only the header schema context. It does not replace the page's Resource record or grant authorization.

## Page-level options

```php
use MortalKiller\FilamentPageHeader\HeaderOptions;

public function pageHeaderOptions(HeaderOptions $defaults): HeaderOptions
{
    return $defaults
        ->offset(64)
        ->compactBelow(900);
}
```

`HeaderOptions` is immutable, so return the new value.

A mode configured directly on the first top-level `Header` can further override the resolved page options.

## Useful inspection methods

These are public but usually do not need to be overridden:

| Method | Description |
| --- | --- |
| `getPageHeaderOptions(): HeaderOptions` | Return the fully resolved options for the page. |
| `getPageHeaderComponent(): ?Header` | Return the first top-level `Header` component in the schema. |
| `pageHeaderIsEnabled(): bool` | Check whether the active Filament panel has the plugin registered. |

## Native header precedence

If the page defines its own `getHeader()`, that application-level customization keeps precedence over automatic package behavior.

The package also leaves native header actions, authorization, modals, and action forms intact.
