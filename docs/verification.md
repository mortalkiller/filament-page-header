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

## Documentation and maintenance configuration — 2026-09-14

The v2 README now links to dedicated configuration, migration and testing guides, includes six reviewed captures from the existing demo test output, and shows dynamic Plumb badges and the maintainer's funding link. The Composer requirements were not changed; documentation distinguishes declared constraints from evidence of incompatibility with earlier Filament 5 releases.

Added contribution and security policies, Dependabot coverage for Composer/GitHub Actions/npm with a seven-day version-update cooldown, and push coverage for 2.x and codex/** in both existing workflows. The approved security maintenance policy is active 2.x development and security fixes only for 1.x.

Executed static validation inside Docker using installed Symfony YAML and CommonMark libraries: four YAML files parsed; workflow branch triggers, read-only content permissions and full action SHA pins checked; three Dependabot ecosystems and cooldowns checked; 52 local Markdown links/anchors resolved; 12 PHP examples passed syntax parsing; all five Plumb badges and six screenshot references were present. The six selected screenshots were visually inspected and copied without image edits. The final whitespace diff check passed.

GitHub API readback confirmed private vulnerability reporting enabled and Dependabot security updates enabled and not paused. Repository files still need to be published before GitHub can apply the new Dependabot configuration and Plumb can assess it. No new Plumb score or CI result is claimed.

The package's runtime code and consuming application's UI were unchanged. PHP/JavaScript/browser regression suites were not rerun for this documentation and CI-trigger change, and no end-user help translations were needed.

## Simplified product showcase — 2026-09-14

Replaced the six README screenshots with fresh captures of a simple product header: illustrative product image, English labels, two native actions, three metadata entries, and an indigo/gray demo palette. Variant 12 in the standalone gallery reproduces this composition; existing customer variants remain available. The public package renderer and consuming application were not changed.

Executed scoped Pint on the two changed demo PHP files and the complete Chromium browser suite: 49 tests passed. Four new product cases cover light/dark at 390 and 1440 pixels, image loading, mobile button stacking, expanded/compact content, overflow, preview modal, native save and preservation of the entered note. The first product run caught an ambiguous test selector for the two native Close buttons; using Escape and asserting that the modal closed resolved the test issue. All 49 tests passed on the final run.

The six resulting screenshots were visually inspected and copied into docs/screenshots without image edits. The product illustration was generated separately and is stored in workbench/public/product-runner.png; its provenance and generation prompt are recorded in testing.md. Tests reused the installed Chromium revision through PLAYWRIGHT_CHROMIUM_EXECUTABLE. PHP unit tests and the consuming application's suite were not rerun for this demo-only change. No end-user application help changes were needed.

## Filament 4 and 5 compatibility — 2026-09-14

Version 2 now declares Filament `^4.12.6 || ^5.8.1`. No package source change was required: the same PHP suite passed against the supported framework combinations listed below.

| Filament | Laravel / Testbench | Result |
| --- | --- | --- |
| `4.12.6` | 12 / Testbench 10 | Passed: 58 tests, 175 assertions. |
| `^4.12.6` (resolved as `4.13.1`) | 13 / Testbench 11 | Passed: 58 tests, 175 assertions. |
| `5.8.1` | 12 / Testbench 10 | Passed: 58 tests, 175 assertions. |
| `^5.8.1` (resolved as `5.8.1`) | 13 / Testbench 11 | Passed: 58 tests, 175 assertions. |

Composer refused the exact dependency sets for Filament `4.0.0`, `4.12.0`, `4.12.4` and `4.12.5` because of known security advisories. This is why version 2 starts at the earliest exact Filament 4 version that installed and passed cleanly, `4.12.6`; it does not prove that every excluded release is functionally incompatible.

The complete Chromium suite also passed with Filament `4.12.6`: 49 tests in 52 seconds. An earlier concurrent invocation reported two `ENOENT` trace-artifact failures after all browser assertions had run; a single isolated rerun passed, so the failures were caused by concurrent writes to the same temporary Playwright output directory rather than header behavior.

The GitHub Actions workflow now keeps this PHP matrix permanent and runs the complete Chromium suite on the minimum secure release of Filament 4 and 5. Pressiu was not changed: it remains only a consumer of the public package API.

## Initial stylesheet loading — 2026-09-19

The plugin now emits a stylesheet link in the initial head through `STYLES_AFTER`, after the panel theme. Only enabled panels emit it, including their native pages, so entering a header page through SPA does not need to discover the stylesheet. The asset remains registered with `loadedOnRequest()` to prevent a second automatic link; the header no longer uses `x-load-css`. CSS and JavaScript contents were unchanged.

Before the fix, the new PHP tests detected the missing initial link and the deferred Alpine markup. All seven new Chromium cases failed against the original implementation: no JavaScript meant no header stylesheet, delayed header JavaScript left theme-only styling visible, a held CSS response allowed the first content paint, and native pages did not preload the stylesheet. The cases pass with the fix. The JavaScript-delay cases compare header height and the following content position before and after initialization in all three modes, allowing at most one pixel for rounding. An equal-specificity theme rule checks stylesheet precedence.

| Local check | Result |
| --- | --- |
| PHP suite: Filament 4.12.6 / Laravel 12.69.2 | 65 passed, 199 assertions |
| PHP suite: Filament 4.13.2 / Laravel 13.32.0 | 65 passed, 199 assertions |
| PHP suite: Filament 5.8.1 / Laravel 12.69.2 | 65 passed, 199 assertions |
| PHP suite: Filament 5.8.1 / Laravel 13.31.0 | 65 passed, 199 assertions |
| Complete Chromium suite: Filament 4.12.6 and 5.8.1 | 62 passed on each |
| JavaScript unit tests | 6 passed |
| Pint on changed PHP files and whitespace check | Passed |

Compatibility installs used separate temporary copies; the working package's dependency files were unchanged. These runs used local PHP 8.5.4 and the installed Chromium executable, not the CI PHP 8.3/8.4 matrix or a fresh browser download. Desktop and mobile captures were also visually inspected. An independent static review found no concrete issues. Safari, Firefox, production consumer themes and production network conditions were not tested; this verifies the stylesheet loading regression, not a promise of zero layout shift from every possible source. No commit, release or deployment was made.

## Filament 5.8.2 action alignment — 2026-09-19

[PR #7's browser job](https://github.com/mortalkiller/filament-page-header/actions/runs/35405753386/job/105795058321) resolved `^5.8.1` to Filament 5.8.2. The captured stylesheet adds `sm:self-end` to `.fi-header-actions-ctn`; local validation above used 5.8.1, which did not have that rule. The two failed desktop alignment tests reproduced with 5.8.2 in a temporary copy, with the same 28.59375 px and 57.578125 px differences as CI. All seven stylesheet-loading regressions had passed in the failed CI job.

The package now sets `align-self: auto` on its action container, so the parent row controls expanded and compact alignment. The existing mobile `stretch` rule is retained. A new browser regression applies the conflicting native rule even on older Filament versions, then checks both top alignment when expanded and center alignment when compact. It failed before the CSS fix and passes afterward; the existing one-pixel assertions were not relaxed.

A separate intermittent PHP test failure was also diagnosed: the avatar fallback test searched all HTML for `MC`, including random Livewire IDs. Setting a deterministic ID containing `MC` reproduced it. The assertion now inspects avatar text nodes and retains that ID fixture, checking that only the initials fallback contains text.

After these fixes, the complete Chromium suite passed with 63 tests on each of Filament 4.12.6, 5.8.1 and 5.8.2. The PHP suite passed with 65 tests and 198 assertions on all three versions; all six JavaScript tests and scoped Pint passed. One local Filament 4 browser run lost its HTTP server after 56 passing tests, resulting in connection errors; a complete rerun on a dedicated port with server reuse disabled passed all 63 tests. An independent static review found no concrete issues. The runs used local PHP 8.5.4 and the installed Chromium; the remote CI job has not been rerun with this follow-up fix.
