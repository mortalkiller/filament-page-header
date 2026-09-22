# Contributing

Bug reports, documentation improvements and focused pull requests are welcome.

## Reporting a problem

Use [GitHub Issues](https://github.com/mortalkiller/filament-page-header/issues) for ordinary bugs and feature requests. Include the package, PHP, Laravel and Filament versions, a minimal header schema, reproduction steps and expected/actual behavior. For visual issues, include viewport size and theme. Remove credentials and real customer data from examples and screenshots.

Report vulnerabilities privately using [SECURITY.md](SECURITY.md).

## Working on a change

1. Create a focused feature or fix branch from `2.x`, and target your pull request at `2.x`.
2. Use the independent [workbench and testing setup](docs/testing.md), or the [local path repository workflow](docs/local-development.md).
3. Keep reusable behavior inside the package. Consumer applications should configure its public API.
4. Add meaningful regression coverage for behavior changes and update relevant documentation. For visual changes, inspect desktop/mobile and light/dark layouts.
5. Run the checks relevant to the change and describe them in the pull request.

Read the [Package Standard v2](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/package-standard.md), [Standard v2 migration guide](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/migrating-to-v2.md), and [Development and release flow](docs/development-flow.md). Standard/tooling upgrades do not determine package SemVer. Keep the historical unsupported `1.x` line frozen.

Use English for code, comments and test descriptions. Keep public API changes explicit and include migration guidance for breaking changes. Avoid unrelated formatting and refactoring.

## Agent skill

The repository includes `mortalkiller/filament-package-standard:^2.0` as a direct development dependency. After `composer install`, read and use:

```text
vendor/mortalkiller/filament-package-standard/resources/boost/skills/developing-filament-packages/SKILL.md
```

## Checks

```bash
composer check
node --test tests/JavaScript/*.test.mjs
npm run test:browser
```

`composer check` runs Pint, Larastan and Pest. Browser tests require the setup described in the testing guide. CI runs PHP, JavaScript, Chromium, static-analysis and Zizmor checks. The PHP matrix covers the supported Filament 4 and 5/Laravel 12 and 13 combinations.

Do not commit dependencies, generated environment files, test reports or lockfiles produced by local library development. Selected reviewed documentation screenshots belong in `docs/screenshots/`.

## Dependency maintenance

Dependabot checks Composer, GitHub Actions and npm weekly. Version updates use an explicit seven-day cooldown and open pull requests for review; there is no automatic merge configured by this package. Review compatibility changes and keep GitHub Actions pinned to full commit SHAs. Pin shared workflow tooling through the matching `standard-ref`.

## Pull requests and releases

Explain the problem, resulting behavior and executed checks. Attach before/after screenshots for layout changes. Changes must be published to GitHub before external checks such as Plumb can observe them.

After successful checks on the exact major commit, create a new immutable `vX.Y.Z` tag and publish its GitHub Release. Stable releases publish exact-tag documentation; branch pushes and prereleases do not replace stable docs. Never move existing tags.
