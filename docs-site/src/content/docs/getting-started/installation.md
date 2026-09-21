---
title: Installation
description: Install Filament Page Header and add your first custom page header.
---

## Requirements

Use the [compatibility guide](../compatibility/) to confirm the package version for your Filament and Laravel application.

## Install the package

```bash
composer require mortalkiller/filament-page-header:^2.0
```

Publish Filament assets:

```bash
php artisan filament:assets
```

The service provider is discovered automatically. There are no package migrations and no custom theme build is required.

## Register the plugin

Add the plugin to each Filament panel that should support custom page headers:

```php
use MortalKiller\FilamentPageHeader\PageHeaderPlugin;

$panel->plugin(PageHeaderPlugin::make());
```

Installation alone does not replace existing Filament headers. Pages opt in explicitly.

## Add your first header

For an existing Resource page, add the `HasPageHeader` trait and define a header schema:

```php
<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

class EditCustomer extends EditRecord
{
    use HasPageHeader;

    protected static string $resource = CustomerResource::class;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            Header::make()
                ->heading(fn (Model $record) => $record->getAttribute('name'))
                ->description(fn (Model $record) => $record->getAttribute('email'))
                ->initials(fn (Model $record) => $record->getAttribute('name')),
        ]);
    }
}
```

The trait also works with Create, View, List and custom pages. Create pages normally have no persisted record yet, so record-dependent closures should support `null`.

## Next steps

- Use the [generator](../../guides/generator/) to add the conventional schema automatically.
- Explore the complete [header configuration API](../../guides/configuration/).
- Review [sticky and compact modes](../../guides/configuration/#sticky-and-compact-modes).
