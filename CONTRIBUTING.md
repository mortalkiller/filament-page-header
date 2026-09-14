# Contributing

Bug reports, documentation improvements and focused pull requests are welcome.

## Reporting a problem

Use [GitHub Issues](https://github.com/mortalkiller/filament-page-header/issues) for ordinary bugs and feature requests. Include the package, PHP, Laravel and Filament versions, a minimal header schema, reproduction steps and expected/actual behavior. For visual issues, include viewport size and theme. Remove credentials and real customer data from examples and screenshots.

Report vulnerabilities privately using [SECURITY.md](SECURITY.md).

## Working on a change

1. Create a focused feature or fix branch from `2.x`, and target your pull request at `2.x`.
2. Use the independent [workbench and testing setup](docs/testing.md), or the [local path repository workflow](docs/local-development.md).
3. Keep reusable behavior inside the package. Consumer applications should configure its public API.
4. Add meaningful regression coverage for behavior changes and update the relevant documentation. For visual changes, inspect desktop/mobile and light/dark layouts.
5. Run the checks relevant to the change. Describe what passed and what you could not run in the pull request.

Use English for code, comments and test descriptions. Keep public API changes explicit and include migration guidance for breaking changes. Avoid unrelated formatting and refactoring.

## Checks

```bash
composer test
composer format:check
node --test tests/JavaScript/*.test.mjs
npm run test:browser
```

Run `composer format` to format PHP changes. Browser tests require the setup described in the testing guide. CI runs PHP, JavaScript and Chromium checks on pull requests; the PHP matrix covers the supported Filament 4 and 5/Laravel 12 and 13 combinations, while Chromium runs on the minimum secure release of both Filament majors. Push checks include the `1.x`, `2.x`, `feature/**` and `codex/**` branches.

Do not commit dependencies, generated environment files, test reports or lockfiles produced by local library development. Selected, reviewed documentation screenshots belong in `docs/screenshots/`.

## Dependency maintenance

Dependabot checks Composer, GitHub Actions and npm weekly. Version updates use an explicit seven-day cooldown and open pull requests for review; there is no automatic merge configured by this package. Review compatibility changes and keep GitHub Actions pinned to full commit SHAs. Security updates are separate from ordinary version updates and are not delayed by the cooldown.

## Pull requests and releases

Explain the problem, resulting behavior and executed checks. Attach before/after screenshots for layout changes. Changes must be published to GitHub before external checks such as Plumb can observe them; do not claim a new score from a local configuration alone.

Maintainers publish release notes through [GitHub Releases](https://github.com/mortalkiller/filament-page-header/releases). Contributors do not need to create a release to test a change locally.
