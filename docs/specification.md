# Filament Page Header specification

## Boundary

Filament Page Header is an independent presentation package for supported Filament 4/5, Laravel 12/13, and compatible PHP versions.

The package must not depend on a consuming application's models, tenant implementation, database schema, provider integrations, deployment topology, or business rules. A consuming application configures the plugin and supplies native Filament schema components, records, actions, and authorization.

Version 2 replaces the previous `HeaderLayout` API with `Header`. See [migration.md](migration.md) for the upgrade path.

## Composition

The header may contain:

- Breadcrumbs.
- A leading identity visual.
- Heading and description.
- Badges.
- Native page actions.
- Metadata.
- Summary information.
- Additional schema content.
- Native page/record sub-navigation.

Native page actions render once. The package must not duplicate, replace, or re-authorize them.

On mobile, content must wrap without page-level horizontal scrolling and standalone actions must remain usable at narrow widths.

## Identity

`Header::make()` inherits the page heading and subheading until the consuming page overrides them.

Common identity methods include:

- `heading()`
- `description()`
- `avatar()`
- `image()`
- `initials()`
- `icon()`
- `leading()`
- `headingSchema()`
- `descriptionSchema()`

The visual fallback order is:

1. Custom `leading()` content.
2. A resolved avatar or image.
3. Generated initials.
4. An icon.

Only one automatic identity visual is rendered.

Color methods accept supported Filament color aliases/palettes or closures. Explicit foreground configuration remains optional.

`image()` is intended for product/entity imagery that should use a square contained frame rather than circular avatar cropping.

## Content slots

`badges()`, `metadata()`, `summary()`, and the default schema accept native Filament schema components and closures.

Native component visibility, formatting, links, copyable state, authorization, and actions remain authoritative.

Empty slots must not create empty visual separators.

## MetadataEntry

`MetadataEntry` extends native `TextEntry`.

Package-specific methods:

- `fieldIcon()`
- `fieldIconPosition()`
- `fieldIconSize()`

The field icon sits beside the complete label/value wrapper. Native `TextEntry::icon()` remains independent and continues to apply to the native value rendering.

Direct metadata fields may be separated visually. Separators must follow actual wrapped rows and hidden fields must not leave orphan separators.

## Configuration and lifecycle

Panel registration is opt-in through `PageHeaderPlugin`. Page integration is opt-in through `HasPageHeader`.

Supported schema sources include:

- Inline `headerSchema()`.
- Convention-based Resource schema discovery.
- Explicit `schemaFor()` mappings.

A missing or empty package schema falls back to Filament's native header.

The header schema receives the page's current Resource record by default. `getPageHeaderRecord()` may return another model, an array, or `null` for custom contexts.

Changing the header context must not change the Resource record, persistence behavior, or authorization.

## Modes and responsive behavior

The public modes are:

- `HeaderMode::Normal`
- `HeaderMode::Sticky`
- `HeaderMode::Compact`

They can be selected through `normal()`, `sticky()`, `compact()`, or `mode()`.

`compactBelow(int)` adds a viewport threshold. `responsive()` supports explicit minimum-width mode mappings at plugin level.

A header-level mode override must not mutate panel defaults or another header instance.

Sticky positioning uses the resolved page scroll context and an automatic or explicit top offset. Very short viewports or layouts where the header cannot safely remain pinned must fall back to usable scrolling behavior.

## Compact content

Without explicit typed selection, compact mode retains the core identity and native actions while collapsing optional supporting content.

`whenCompact()` configures typed compact content using `CompactHeader` and `HeaderPart`.

`show()` retains complete blocks. `only()` retains specific direct named fields where field selection is supported.

Compact selection must not:

- Override native visibility or authorization.
- Duplicate actions or schema components.
- Trigger Livewire requests while scrolling.
- Lose unsaved input state.
- Break keyboard focus.

Legacy compact methods remain compatibility APIs and are documented as deprecated.

## Breadcrumbs and sub-navigation

Breadcrumb placement is configured with `BreadcrumbPosition::Outside`, `Inside`, or `Hidden`.

`subNavigation()` integrates existing native Filament Page/Resource sub-navigation into the header. It does not create routes or navigation state.

Filament remains responsible for URLs, authorization, active state, generated navigation items, and SPA transitions.

Relation Manager content tabs remain in the page content and are not moved by `subNavigation()`.

## Assets and themes

The package owns its CSS and browser behavior and does not require a consuming application to rebuild a Tailwind theme.

Published Filament assets must be refreshed after package asset changes.

The package follows Filament light/dark tokens and preserves semantic colors from native entries and actions.

Reduced-motion preferences must be respected.

## Generator

The Artisan generator may:

- Create the conventional reusable Resource header schema.
- Add `HasPageHeader` to selected standard Resource pages.
- Support explicit panel selection.
- Support non-interactive page selection.
- Avoid duplicate imports/traits on repeated runs.
- Leave existing application methods and actions intact.

The generator must not guess application-specific fields or business rules.

## Compatibility

Package major versions describe this package's API and do not map directly to Filament major versions.

The declared Composer constraints and CI matrix define supported framework combinations. Compatibility claims should be based on package tests and CI rather than on one external application.
