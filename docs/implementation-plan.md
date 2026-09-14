# Header API and presentation implementation plan

## Approved design

Replace HeaderLayout with Header. Identity uses heading(), description(), avatar() and initials(); badges, metadata and summary accept native components. The package owns the entire card, responsive layout, external breadcrumbs, square pinned top corners, right-aligned desktop actions, full-width mobile action columns, light/dark styling and compact behavior. Pressiu only declares content and plugin options. Existing labels, permissions, business closures, forms and action ordering remain authoritative. The changes are prepared as a local package commit; no release is part of this work.

## Execution

- [x] Add behavioral regression coverage for the new identity API, escaping, hidden labels, responsive options and per-header overrides. Run the new tests before implementation.
- [x] Implement Header and migrate package fixtures. Integrate native actions once in the upper row; metadata and summary share the lower strip. Retain advanced native schema composition and native/custom fallback.
- [x] Add sticky(), compact(), normal() and compactBelow(int) defaults and per-header overrides. Compact hides description, metadata, summary and extra content by default; retainSummaryWhenCompact() opts metrics back in. Preserve focus safety, cleanup and short-viewport fallback.
- [x] Implement responsive card CSS using Filament gray tokens in both themes. Verify right-aligned wrapping, long text, images, empty slots and one principal heading.
- [x] Migrate only CustomerHeader and TenantPanelProvider API use in Pressiu. Publish package assets locally.
- [x] Run isolated package PHP/JS/browser checks and focused customer integration checks. Inspect real light/dark desktop/mobile screenshots and final diff.
- [x] Update README, current specification, local workflow and verification record. Assess existing end-user articles without adding implementation details.

## Metadata follow-up

- [x] Add optional MetadataEntry field icons with per-field Before/After positions while preserving native TextEntry rendering and behavior.
- [x] Separate visible metadata fields on the same wrapped row without drawing leading lines or clipping native content.
- [x] Configure customer icons through the package API and document both positions.

## Product and icon-size follow-up

- [x] Add fieldIconSize(int|Closure), validate positive pixel sizes, default to 24 px and increase icon-to-field spacing to 16 px.
- [x] Add square image() presentation without cropping and descriptionSchema() for native copyable product codes.
- [x] Add the shared ProductHeader to Pressiu View/Edit pages, using the main image, supplier code, status/stock badges and approved catalogue fields; preserve native actions and keep the code visible while compact.
- [x] Update README examples, current specification and product help in all four locales.
- [x] Verify PHP behavior, browser layout in both themes, focused product/support regressions and final screenshots.

## Typed compact content

- [x] Add HeaderPart and CompactHeader with whenCompact(), show() and only().
- [x] Preserve native field visibility, action instances and focus safety; calculate dividers for expanded and compact selections.
- [x] Migrate ProductHeader and the workbench to the typed API; document direct-field selection, defaults and legacy compatibility.
- [x] Cover selection, hidden fields, invalid identifiers and browser expansion/compaction with unsaved input.

## Verification contract

PHP tests cover photo/initials selection, escaped names, native child component defaults, panel/header configuration isolation, recordless pages and native action behavior. JavaScript tests cover breakpoint boundaries. Browser tests exercise 360/390/768/1024/1440 widths, light/dark, expanded/compact, native actions, no horizontal overflow, no scroll-triggered requests and unchanged unsaved form state. Install only already-declared development dependencies.
