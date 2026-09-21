# Header API and presentation implementation plan

## Design goals

Version 2 replaces the previous `HeaderLayout` API with `Header` and keeps the package focused on presentation and Filament integration.

A consuming application supplies content, records, native schema components, actions, and authorization. The package owns the page-header composition, responsive behavior, sticky/compact presentation, navigation placement, and package assets.

No application-specific models, database queries, service providers, infrastructure conventions, or business rules belong in the package API or documentation.

## Core execution

- [x] Add regression coverage for the identity API, escaping, hidden labels, responsive options, and per-header overrides.
- [x] Implement `Header` and migrate package fixtures to the new API.
- [x] Keep native page actions rendered once and preserve native action ordering, modals, forms, authorization, and groups.
- [x] Add `normal()`, `sticky()`, `compact()`, and `compactBelow()` at panel and header level.
- [x] Implement responsive light/dark presentation using Filament tokens.
- [x] Validate desktop/mobile behavior, long content, empty slots, images, actions, and one principal heading.
- [x] Update package README, migration guidance, specification, local-development workflow, and verification notes.

## Metadata follow-up

- [x] Add `MetadataEntry` field icons while retaining native `TextEntry` behavior.
- [x] Support before/after icon positions.
- [x] Add responsive metadata separators that follow actual wrapped rows.
- [x] Keep native visibility, formatting, links, copyable values, and actions authoritative.

## Product-style identity follow-up

- [x] Add `fieldIconSize(int|Closure)` with positive pixel validation and a 24 px default.
- [x] Add square `image()` presentation with contain fitting.
- [x] Add `descriptionSchema()` for richer supporting content such as copyable references.
- [x] Add fictional product fixtures to the workbench and screenshot suite.
- [x] Validate product-style headers in light/dark and desktop/mobile layouts.

## Typed compact content

- [x] Add `HeaderPart` and `CompactHeader`.
- [x] Add `whenCompact()`, `show()`, and `only()`.
- [x] Preserve native visibility, authorization, action instances, unsaved state, and focus behavior.
- [x] Support field-level compact selection without duplicating components.
- [x] Keep legacy compact methods available only for compatibility and document their replacements.

## Header navigation

- [x] Add configurable breadcrumb placement.
- [x] Integrate existing native Filament page/record sub-navigation.
- [x] Keep routing, active state, authorization, URLs, and SPA behavior native to Filament.
- [x] Allow breadcrumbs and sub-navigation to participate in typed compact selection.
- [x] Keep Relation Manager content tabs in their native page-content location.

## Verification contract

Package validation covers:

- PHP behavior and public API contracts.
- JavaScript breakpoint and measurement logic.
- Browser rendering in supported Filament versions.
- Desktop/mobile and light/dark layouts.
- Sticky and compact transitions.
- Native actions and navigation.
- Accessibility-sensitive focus and reduced-motion behavior.
- No horizontal overflow in supported demo compositions.
- No scroll-triggered Livewire requests.
- Preservation of unsaved form state across visual compaction.

Consumer applications should still run their own integration suites because their models, policies, themes, actions, and business rules remain outside this package.
