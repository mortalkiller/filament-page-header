---
title: API Reference
description: Complete reference for the public Filament Page Header configuration API.
---

The API reference documents the package's public configuration surface. Use the [guides](/filament-page-header/guides/configuration/) when you want task-oriented examples and this section when you need to know which methods, arguments, enums, and override points are available.

## Public API

| API | Use it for |
| --- | --- |
| [`PageHeaderPlugin`](./page-header-plugin/) | Panel registration, global header modes, responsive behavior, offsets, and Resource schema mappings. |
| [`Header`](./header/) | Heading, description, identity, badges, metadata, summary, navigation, and per-header behavior. |
| [`MetadataEntry`](./metadata-entry/) | Native `TextEntry` metadata with an icon beside the complete field. |
| [`Heading` and `Subheading`](./heading-subheading/) | Custom heading composition when the shorthand `heading()` / `description()` methods are not enough. |
| [`CompactHeader`](./compact-header/) | Select which blocks or named fields remain visible in compact mode. |
| [`HasPageHeader`](./has-page-header/) | Opt a Filament page into the package and customize schema, record context, and page-level options. |
| [`HeaderOptions`](./header-options/) | Immutable sticky/compact/offset configuration used by plugins and page overrides. |
| [Enums](./enums/) | `HeaderMode`, `HeaderPart`, and `BreadcrumbPosition`. |

## API conventions

The package follows Filament's fluent configuration style. Most setters return the same instance:

```php
Header::make()
    ->heading(fn ($record) => $record->name)
    ->initials(fn ($record) => $record->name)
    ->metadata([
        MetadataEntry::make('email'),
    ])
    ->compact();
```

Configuration values commonly accept closures. They are evaluated through Filament's normal component evaluation system, so record and Livewire injection work where the underlying component supports them.

The package intentionally reuses native Filament components. `MetadataEntry` extends `TextEntry`, `Heading` extends `Entry`, and the slots on `Header` accept normal Filament schema components and actions.

## Typical setup

```php
use Filament\Schemas\Schema;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;

class EditCustomer extends EditRecord
{
    use HasPageHeader;

    public function headerSchema(Schema $schema): Schema
    {
        return $schema->components([
            Header::make()
                ->heading(fn ($record) => $record->name)
                ->description(fn ($record) => $record->email)
                ->initials(fn ($record) => $record->name),
        ]);
    }
}
```

For a complete walkthrough, start with [Installation](/filament-page-header/getting-started/installation/) and [Header configuration](/filament-page-header/guides/configuration/).
