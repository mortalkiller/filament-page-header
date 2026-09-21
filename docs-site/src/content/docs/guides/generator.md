---
title: Page header generator
description: Generate conventional header schemas and opt Resource pages into Filament Page Header.
---

The package includes an Artisan generator for adding a conventional page header schema to an existing Filament Resource.

## Basic usage

Run:

```bash
php artisan make:filament-page-header OrderResource
```

When the command is interactive, it asks which Filament panel contains the Resource when necessary, resolves the Resource, and lets you choose which standard Resource pages should use `HasPageHeader`.

For a typical Resource, the page choices are:

```text
List
Create
View
Edit
```

List, View, and Edit are selected by default when they exist. Create remains optional because create pages normally do not have a persisted record yet.

The generated schema follows the package convention:

```text
app/Filament/Resources/Orders/Schemas/OrderHeader.php
```

with a minimal definition:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Schemas\Schema;
use MortalKiller\FilamentPageHeader\Components\Header;

final class OrderHeader
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Header::make(),
        ]);
    }
}
```

`Header::make()` keeps the normal page heading and subheading by default. The generator deliberately does not guess application-specific fields, badges, metadata, images, or business rules.

## Non-interactive usage

For scripts, CI, or explicit terminal usage, pass the desired page types:

```bash
php artisan make:filament-page-header OrderResource \
    --page=list \
    --page=view \
    --page=edit
```

Available standard page values are:

```text
list
create
view
edit
```

`index` is accepted as an alias for `list`. The corresponding `*Records` / `*Record` names are also accepted.

If you only want the conventional schema and do not want the command to edit Resource page files:

```bash
php artisan make:filament-page-header OrderResource --no-pages
```

In non-interactive mode, explicitly provide at least one `--page` or use `--no-pages`. This prevents the command from modifying Resource pages based on an implicit default.

## Multiple panels

When an application has multiple Filament panels, either choose the panel interactively or pass it explicitly:

```bash
php artisan make:filament-page-header OrderResource --panel=admin --page=edit
```

The Resource must be registered in the selected panel. If the same short Resource name is ambiguous, pass its fully-qualified class name:

```bash
php artisan make:filament-page-header \
    'App\Filament\Admin\Resources\Orders\OrderResource' \
    --panel=admin \
    --page=edit
```

## Existing schemas and repeated runs

The command is safe to run again.

If the conventional `*Header.php` schema already exists, it is left unchanged by default. Selected Resource pages are still checked and `HasPageHeader` is only added when missing.

Use `--force` only when you deliberately want to replace the conventional generated schema:

```bash
php artisan make:filament-page-header OrderResource \
    --page=edit \
    --force
```

`--force` affects the generated header schema. It does not replace Resource page files.

The command also avoids duplicate `HasPageHeader` imports and trait declarations.

## What the command changes

For selected pages, the command adds the package trait to the existing Resource Page class:

```php
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

class EditOrder extends EditRecord
{
    use HasPageHeader;

    // Existing page configuration remains in place.
}
```

It does not regenerate the Resource Page, remove existing methods, or replace native Filament actions.

The generator modifies application source files, so review the resulting diff before committing it.

## Unsupported page types

The automatic page selector currently targets standard Filament Resource pages:

- List records
- Create record
- View record
- Edit record

Custom Resource pages are not modified automatically. Add `HasPageHeader` to a custom page manually and use the same header schema convention or an inline `headerSchema()`.

Simple Resources or other Resource page types can still use `--no-pages` to generate the schema without automatic page changes.

## Next steps

After generation, customize the schema using the normal package API:

```php
Header::make()
    ->heading(fn ($record) => $record?->name)
    ->initials(fn ($record) => $record?->name)
    ->metadata([
        // Native Filament schema entries...
    ]);
```

See [Header configuration](configuration.md) for identity, metadata, compact mode, custom record contexts, and reusable Resource schemas.
