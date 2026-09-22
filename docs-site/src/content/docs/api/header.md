---
title: Header
description: Complete fluent API for composing a Filament Page Header.
---

`Header` is the main schema component. It owns the visual header layout while accepting native Filament schema components, entries, and actions inside its slots.

This reference focuses on configuration methods intended for application code. Runtime getters used by the package while rendering are intentionally omitted.

## Complete example

```php
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Components\Header;
use MortalKiller\FilamentPageHeader\Components\MetadataEntry;
use MortalKiller\FilamentPageHeader\Enums\BreadcrumbPosition;
use MortalKiller\FilamentPageHeader\Enums\HeaderActionsPosition;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

Header::make()
    ->heading(fn ($record) => $record->name)
    ->description(fn ($record) => $record->email)
    ->avatar(fn ($record) => $record->avatar_url)
    ->initials(fn ($record) => $record->name)
    ->icon(Heroicon::OutlinedUser)
    ->badges([
        TextEntry::make('status')->badge(),
    ])
    ->metadata([
        MetadataEntry::make('email')
            ->fieldIcon(Heroicon::OutlinedEnvelope),
        MetadataEntry::make('created_at')
            ->dateTime(),
    ])
    ->actionsPosition(HeaderActionsPosition::End)
    ->breadcrumbs(BreadcrumbPosition::Inside)
    ->subNavigation()
    ->compact()
    ->whenCompact(fn (CompactHeader $compact) => $compact
        ->show(HeaderPart::Description, HeaderPart::Badges)
        ->only(HeaderPart::Metadata, ['email']));
```

## Creating a header

| Method | Description |
| --- | --- |
| `make(array\|Closure $schema = []): static` | Create and configure the header component. Optional schema content becomes the default content slot. |
| `schema([...])` | Native Filament component API inherited by `Header`; add content to the default content slot. |

`Header::make()` supplies the current page heading and subheading automatically until you override them.

## Heading and description

| Method | Description |
| --- | --- |
| `heading(mixed $state, bool $html = false): static` | Set the page heading. Accepts literals or closures. |
| `headingSchema(array\|Closure $components): static` | Replace the shorthand heading with a custom schema composition. |
| `description(mixed $state, bool $html = false): static` | Set the supporting description. |
| `descriptionSchema(array\|Closure $components): static` | Use native components for the description area. |

When using `headingSchema()`, keep one principal `h1` in the page. The package's [`Heading` component](../heading-subheading/) is available for custom composition.

## Identity

The identity area chooses a single visual using this order: custom `leading()` content, resolved avatar/image, initials, then icon.

| Method | Description |
| --- | --- |
| `avatar(mixed $url): static` | Render a circular avatar from a URL, closure, or native `ImageEntry`. |
| `image(mixed $url): static` | Render a square contained image, useful for products and entities that should not be cropped as avatars. |
| `initials(mixed $name): static` | Provide the name used to generate an initials fallback. The first two words are used. |
| `icon(string\|BackedEnum\|Closure\|null $icon): static` | Provide the final icon fallback. |
| `initialsBgColor(...): static` | Set an initials background using a panel color alias, Filament palette, or closure. |
| `initialsTextColor(...): static` | Override the initials foreground color. |
| `iconBgColor(...): static` | Set an icon fallback background color. |
| `iconColor(...): static` | Override the icon foreground color. |
| `leading(array\|Closure $components): static` | Provide a completely custom leading schema; this takes precedence over avatar/image/initials/icon rendering. |

### Avatar with fallbacks

```php
use Filament\Support\Icons\Heroicon;

Header::make()
    ->avatar(fn ($record) => $record->avatar_url)
    ->initials(fn ($record) => $record->name)
    ->icon(Heroicon::OutlinedUser);
```

If the avatar URL is empty or invalid, initials are tried next. If no initials can be rendered, the icon is used.

### Native ImageEntry

```php
use Filament\Infolists\Components\ImageEntry;

Header::make()
    ->avatar(
        ImageEntry::make('photo')
            ->disk('public'),
    );
```

Using `ImageEntry` preserves Filament's storage, disk, visibility, and temporary URL behavior.

## Content slots

| Method | Description |
| --- | --- |
| `badges(array\|Closure $components): static` | Render status-like entries. Direct entries have their labels hidden automatically. |
| `metadata(array\|Closure $components): static` | Render secondary fields such as references, owners, dates, and links. |
| `summary(array\|Closure $components): static` | Render totals or summary information. |
| `schema([...])` | Add extra content below the standard slots using Filament's native schema API. |

All slots accept native Filament components. Keep persistence and expensive application work outside rendering closures.

## Native page actions

| Method | Default | Description |
| --- | --- | --- |
| `actionsPosition(HeaderActionsPosition\|Closure $position): static` | `End` | Position the native action block before the main row (`Start`), after it (`End`), or below the header content (`Below`). |

Declare actions in the page's native `getHeaderActions()`. Logical positions respect RTL; mobile keeps the existing full-width area after the details. Positioning never changes the order of actions within the block.

```php
Header::make()
    ->actionsPosition(HeaderActionsPosition::Below)
    ->compact()
    ->whenCompact(fn (CompactHeader $compact) => $compact
        ->actions(['save', 'approve']));
```

The example assumes the page already declares native actions named `save` and `approve`. Use `hideActions()` for exclusion instead of inclusion. See [Native header actions](../../guides/native-actions/) and [CompactHeader](../compact-header/) for group handling, selection precedence and interaction guarantees.

## Breadcrumbs

```php
use MortalKiller\FilamentPageHeader\Enums\BreadcrumbPosition;

Header::make()->breadcrumbs(BreadcrumbPosition::Outside);
Header::make()->breadcrumbs(BreadcrumbPosition::Inside);
Header::make()->breadcrumbs(BreadcrumbPosition::Hidden);
```

| Method | Default | Description |
| --- | --- | --- |
| `breadcrumbs(BreadcrumbPosition\|Closure $position): static` | `Outside` | Keep breadcrumbs outside the card, move them inside, or hide them. |

Global Filament breadcrumb disabling remains authoritative.

## Sub-navigation

```php
Header::make()->subNavigation();
```

| Method | Default | Description |
| --- | --- | --- |
| `subNavigation(bool\|Closure $condition = true): static` | `false` | Move existing native Page/Resource sub-navigation into the header. |

The package reuses Filament's existing navigation data. It does not create routes, URLs, active-state rules, or authorization logic.

Relation Manager content tabs remain in the page content; `subNavigation()` is for native page/record sub-navigation.

## Header mode

| Method | Description |
| --- | --- |
| `normal(): static` | Override the inherited mode with normal scrolling. |
| `sticky(): static` | Override the inherited mode with a pinned full header. |
| `compact(): static` | Override the inherited mode with compact-on-scroll behavior. |
| `mode(HeaderMode $mode): static` | Set the mode explicitly with an enum. |
| `compactBelow(int $width): static` | Use compact behavior below a viewport width while keeping the selected base mode above it. |

Per-header mode configuration overrides the panel defaults from [`PageHeaderPlugin`](../page-header-plugin/).

## Compact content

```php
use MortalKiller\FilamentPageHeader\CompactHeader;
use MortalKiller\FilamentPageHeader\Enums\HeaderPart;

Header::make()
    ->compact()
    ->whenCompact(fn (CompactHeader $compact) => $compact
        ->show(HeaderPart::Image, HeaderPart::Badges)
        ->only(HeaderPart::Metadata, ['reference', 'owner']));
```

`whenCompact(Closure $configure)` configures exactly which optional blocks remain visible. See [CompactHeader](../compact-header/) for the complete selection rules.

The heading always remains available. Native page actions remain available by default unless an action selection is configured. Native authorization, visibility and disabled state still apply.

## Deprecated compatibility methods

These methods remain for existing version 1/early version 2 integrations but should not be used in new code:

| Method | Replacement |
| --- | --- |
| `retainSummaryWhenCompact(bool $condition = true)` | `whenCompact(...)->show(HeaderPart::Summary)` |
| `hideWhenCompact(array $slots)` | `whenCompact()` with `show()` / `only()` |

Do not mix the legacy compact selection API with `whenCompact()`; the last configuration path wins.
