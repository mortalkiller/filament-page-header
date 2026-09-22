# Verification record

This record documents package-level validation only. It intentionally excludes private consuming applications, customer data, infrastructure details, and application-specific test suites.

## Continuous integration

The repository CI validates the package independently from any consuming application.

Current automated checks include:

- Composer validation.
- Pint formatting checks.
- Larastan level 6 analysis.
- Strict Pest/PHPUnit behavior.
- PHP/Pest suites against supported Laravel/Testbench and Filament combinations.
- JavaScript unit tests.
- Chromium/Playwright browser tests.
- Zizmor GitHub Actions auditing.
- Documentation build validation.

The authoritative matrix lives in `.github/workflows/tests.yml`.

## PHP coverage

Package tests cover public API and integration behavior including plugin registration and panel isolation, schema rendering, resource mappings, identity fallbacks, metadata, sticky/compact configuration, native action preservation, navigation, generator behavior, and asset registration.

## JavaScript and browser coverage

JavaScript tests cover responsive/sticky state and cleanup. The Workbench/Playwright suite exercises real Filament pages across desktop/mobile, light/dark, navigation, actions, SPA transitions and long-content scenarios.

## Compatibility verification

CI covers the minimum supported Filament 4 and 5 releases plus latest allowed releases across Laravel 12 and 13. Minimum jobs use lowest compatible dependency resolution without disabling Composer security-advisory blocking.

## Limits

The package suite verifies the reusable package contract, not every possible consuming application. Consumer-specific themes, scroll containers, policies, third-party CSS and long-lived processes may require their own integration tests.
