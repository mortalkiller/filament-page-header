# Verification record

This record documents package-level validation only. It intentionally excludes private consuming applications, customer data, infrastructure details, and application-specific test suites.

## Continuous integration

The repository CI validates the package independently from any consuming application.

Current automated checks include:

- Composer validation.
- PHP/Pest suites against supported Laravel/Testbench and Filament combinations.
- JavaScript unit tests.
- Chromium/Playwright browser tests.
- Code-format and quality checks.
- Documentation build validation.

The authoritative matrix lives in `.github/workflows/tests.yml`.

## PHP coverage

Package tests cover public API and integration behavior including:

- Plugin registration and panel isolation.
- Header schema rendering.
- Record and recordless contexts.
- Reusable Resource schema discovery and explicit mappings.
- Identity fallbacks.
- Metadata entries and field icons.
- Sticky/compact option resolution.
- Typed compact selection.
- Native action preservation.
- Breadcrumbs and sub-navigation.
- Artisan generator behavior.
- Asset registration and loading.

## JavaScript coverage

JavaScript tests cover browser-owned behavior such as:

- Responsive breakpoint resolution.
- Sticky and compact state transitions.
- Measurements and layout recalculation.
- Cleanup and resize behavior.
- State preservation where visual changes must not trigger application requests.

## Browser coverage

The workbench and Playwright suite exercise real rendered Filament pages rather than isolated HTML mocks.

Scenarios include:

- Desktop and mobile widths.
- Light and dark themes.
- Normal, sticky, and compact modes.
- Product-style and customer-style fictional fixtures.
- Native action groups and menus.
- Navigation tabs/dropdowns.
- SPA transitions.
- Long content and wrapping.
- Unsaved form state.
- Reduced-motion-sensitive transitions.
- No horizontal page overflow in covered fixtures.

Generated browser reports, traces, and temporary screenshots remain build/test artifacts. Selected reviewed screenshots used by the public documentation live under `docs/screenshots/`.

## Compatibility verification

CI covers the minimum supported Filament releases and the latest releases allowed by the declared Composer constraints across the supported Laravel/Testbench lines.

When investigating a version-specific regression, use a clean temporary dependency resolution rather than modifying the package's committed dependency requirements merely for local testing.

## Visual review

Layout changes should be inspected in:

- Light and dark themes.
- Desktop and narrow mobile widths.
- Expanded and compact states.
- Pages with and without images.
- Pages with multiple native actions.
- Pages with navigation.
- Long or wrapping metadata.

Automated browser assertions remain the primary regression contract; visual inspection supplements them.

## Limits

The package test suite verifies the package contract, not every possible consuming application.

Applications may introduce custom themes, scroll containers, actions, policies, navigation structures, long-lived processes, or third-party CSS that require their own integration tests.

A passing package suite is therefore evidence that the reusable package behavior works within its supported fixtures and matrix, not a substitute for application-specific validation.
