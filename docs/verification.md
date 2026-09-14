# Verification record

Verified locally on 2026-09-14 against the Header API redesign based on `418cef9`, prepared on `codex/header-api-redesign`. This record describes the tested implementation; it does not claim a CI run or a published release.

## Executed checks

All runtime commands ran inside the existing Docker environment.

| Check | Result |
| --- | --- |
| Package PHP suite, PHP 8.4 / Testbench 11 / Filament 5.8.1 | Passed: 58 tests, 175 assertions. |
| JavaScript unit tests | Passed: 6 tests. |
| Chromium Playwright integration tests | Passed: 45 tests. |
| Pressiu CustomerFormTest | Passed: 41 tests, 372 assertions. |
| Pressiu CustomerSaveBrowserTest | Passed: 1 test, 3 assertions, including saving through the header action. |
| Pressiu product and support regressions | Passed: 47 tests, 46,885 assertions. |
| Pressiu ProductHeaderBrowserTest | Passed: 4 tests, 96 assertions. |
| Laravel Pint on changed PHP files | Passed. |
| Package discovery, workbench preparation and local asset publication | Passed. |
| Final whitespace diff check | Passed. |

The new PHP and JavaScript regressions were first run against the old API and failed as expected. The passing suite runs against the implementation. Package PHP tests use an isolated database; the browser suite uses a separate workbench with real Filament and Livewire components.

## Current layout and metadata verification

Breadcrumbs are outside the sticky card, pinned top corners are square, and mobile actions occupy full-width rows. Pressiu selects compact() at every viewport width. The workbench also exercises a native button group with a separate dropdown and a standalone icon-only menu.

MetadataEntry now supports decorative field icons before or after the complete native entry. PHP regressions verify both positions, closure injection, omitted icons, hidden entries, escaping, copyable values, date formatting, links and child actions. The new tests first failed against the missing component, then passed after implementation.

The browser suite verifies separators between fields on the same wrapped row, no leading separator on each row, both icon positions, resize and field removal. It also checks native menus, unsaved input and no requests from the measured layout paths. An intermediate run detected that icons reduced text width at 768 and 1024 pixels; the package now reserves additional field width, and the complete 43-test browser suite passed after the correction.

The latest follow-up adds positive integer/closure icon sizes (24 px by default), a 16 px icon-to-field gap, square uncropped product images and native description schemas. Three PHP regressions failed before implementation, then passed. Browser assertions measure 24/32 px icons, their 16 px gap and wrapping in both themes.

Pressiu ProductHeaderBrowserTest covers actual View/Edit pages in light/dark at 1440 and 390 CSS pixels. It verifies the image's contain fit, persistent supplier code while compact, collapsed metadata, no horizontal overflow and no JavaScript errors. The fixtures use a local placeholder image; PHP coverage separately verifies primary-image ordering and variant counts, and conditional fields for imported/manual products. Final product captures were inspected for light desktop and dark mobile.

The current package PHP/JS/browser checks and focused product/support regressions passed after the latest implementation. The customer checks in the table were executed during the preceding metadata work. Pint and local asset publication passed.

## Image size and native theme follow-up

Product images now use 96 px on desktop, 80 px below 768 px and 32 px when compact. Native ImageEntry dimensions inherit the same responsive CSS variable, and the identity row centers the image and text vertically. The header uses Filament's fi-section surface directly; separators use the native light gray-200 / dark white-at-10% colors. Other neutral colors use the runtime --gray-* palette.

After these adjustments, the package PHP suite (52 tests, 147 assertions), Chromium suite (43 tests), ProductHeaderBrowserTest (4 tests, 96 assertions), scoped Pint and asset publication passed. Final light desktop and dark mobile product screenshots were inspected. Earlier product/support, customer and JavaScript results above were not rerun for this styling correction. User help remains accurate: fields, actions and workflows are unchanged, so no additional help translations are needed.

## Divider spacing follow-up

Metadata columns now have a 32 px gap with the separator centered at 16 px from each column, independent of field icons. Mobile column bases account for this gap. All 43 Chromium tests passed after the CSS change; browser measurements confirmed a 32 px column gap and -16 px separator offset in fields with and without icons. The dark mobile capture was inspected and local assets were republished. README and specification were updated. User help requires no change because this only adjusts visual spacing.

## Typed compact content verification

whenCompact() now receives CompactHeader with HeaderPart enum selections. Six focused PHP regressions cover isolated configuration, unchanged scroll mode, native hidden fields, direct field selection, invalid identifiers, replacement/empty configuration and keyed layouts. The initial four tests failed before implementation. Two new Chromium cases exercise partial metadata/badge/summary selection at 390 and 1440 px, correct leading dividers, restoration on expansion, unchanged unsaved input, native actions and no scroll requests.

Final checks for this change: package PHP 58 tests / 175 assertions, JavaScript 6 tests, Chromium 45 tests, and Pressiu product header/action/browser integration 11 tests / 127 assertions. Scoped Pint, asset publication and whitespace checks passed. The full Pressiu suite was not run. ProductHeader now consumes the typed API with the same visible compact content. README and current technical specification describe the contract and compatibility methods. Product/customer help needs no further translations because this API change preserves their existing visible fields and workflows.

## Coverage and visual inspection

- Heading, description, photo/initials, escaped content, rejected avatar URL schemes, native badges and metadata labels.
- Panel/header option isolation, exclusive compactBelow boundary, recordless pages, native/custom fallback and render hooks.
- Right-aligned native actions, wrapping, native submit and schema actions, menus and confirmations, unchanged unsaved input and no requests from tested scroll/resize paths.
- Normal, sticky and compact modes, responsive widths from 360 to 2560 CSS pixels, topbar/nested offsets, short viewports, focus safety and SPA cleanup.
- Light/dark expanded and compact headers at desktop and mobile widths; automated assertions verify theme backgrounds, overflow and action alignment.
- Actual screenshots were visually inspected for light desktop/mobile and dark desktop/mobile, including expanded and compact examples. At narrower desktop content widths, actions and metadata wrap; sufficiently wide headers retain a shared identity/action row and a single metadata/summary strip.

Package screenshots are generated under ignored `test-results/`; Pressiu product captures are under ignored `tests/Browser/Screenshots/`. The workbench uses its own native action colors; the package inherits the consuming panel palette.

## Integration and documentation scope

Pressiu only declares API usage in CustomerHeader, ProductHeader, the Product View/Edit page traits and TenantPanelProvider. Rendering, responsive behavior, styles and native action placement belong to the package. Existing business data closures and action definitions remain in the application. README, specification, implementation plan and local development guidance describe the replacement API and migration.

Existing customer help articles were assessed: action labels, permissions, availability and customer workflows are unchanged, so no customer help translation changes are required. Product help was updated in Portuguese, English, Spanish and French, with existing IDs and contextual associations preserved. Focused corpus, manual-content and search tests passed. Header implementation details belong in package documentation.

## Limits

- Tests used the already-installed Chromium executable through PLAYWRIGHT_CHROMIUM_EXECUTABLE; this run does not verify a fresh browser download or the full PHP compatibility matrix.
- Viewports approximate mobile devices. Physical iOS/Android devices, Safari, Firefox and assistive technologies were not tested.
- Focused Pressiu integration checks passed; the complete application suite was not run.
- No PHPStan result, accessibility certification, public release, merge or deployment is claimed.
