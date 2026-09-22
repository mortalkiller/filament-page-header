# Development and release flow

## Work on the affected major

The current package line is `2.x`; the historical unsupported `1.x` line stays frozen. Use permanent branches only for package majors. A Standard or template tooling major does not itself require a new package major.

Create a temporary `feature/*`, `fix/*`, `docs/*`, `test/*`, `refactor/*`, or `chore/*` branch from the affected major and open a PR back to that major. Review, run CI, squash focused work and remove the temporary branch.

## Choose a version

A compatible fix increments PATCH. Compatible functionality increments MINOR. Incompatible public API, runtime behavior or supported-platform changes require a MAJOR review and upgrade guidance.

Adopting Package Standard v2 is a tooling migration and does not change this package's `2.x` API by itself.

## Publish a release

Inspect the previous tag → exact release-commit diff. Complete tests, compatibility, documentation, issue acceptance, release notes and the public-content audit. Required push CI must pass on that exact commit, including Larastan and Zizmor.

Create a new immutable `vX.Y.Z` tag at the verified commit and publish the GitHub Release. Never move a published tag. Verify fresh PlumbPHP evidence in all four categories.

## Documentation

PRs and major pushes validate docs without deployment secrets. Stable releases publish from exact tags after required CI. Older majors, prereleases and stale re-runs cannot downgrade newer channels.

## Maintainer setup

Keep `2.x` as the default package branch and protect supported majors. External workflow references and checker/publication tooling must share a reviewed full template SHA.

See the [canonical Package Standard v2](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/package-standard.md) and [migration guide](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/migrating-to-v2.md).
