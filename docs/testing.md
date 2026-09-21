# Demo and testing

## Compatibility matrix

The PHP suite is run in CI against both supported Filament lines and Laravel combinations:

| Filament | Laravel / Testbench | Purpose |
| --- | --- | --- |
| `4.12.6` | 12 / Testbench 10 | Minimum supported Filament 4 release |
| `^4.12.6` | 13 / Testbench 11 | Latest Filament 4 release Composer resolves |
| `5.8.1` | 12 / Testbench 10 | Minimum supported Filament 5 release |
| `^5.8.1` | 13 / Testbench 11 | Latest Filament 5 release Composer resolves |

The Chromium CI suite runs against Filament `4.12.6` and the latest version Composer resolves for `^5.8.1`. It covers the rendered header, sticky/compact behavior, native actions, themes and responsive layouts. The matrix is part of [GitHub Actions](../.github/workflows/tests.yml), so every pull request tests the package without changing a consuming application.

Do not alter the package's root requirement to test another combination locally. Use a temporary clean copy, constrain Filament and Testbench there, then run the normal suite. This keeps your working tree and lockfile untouched.

From this repository:

```bash
composer install
php bin/prepare-workbench.php
php workbench/artisan package:discover
php workbench/artisan filament:assets
php workbench/artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000/demo/headers`. The workbench demonstrates twelve compositions and all three scroll modes. It is a **local testing application**, not a production application; do not expose it publicly. It is self-contained and does not require a consuming application's database, theme, or application-specific configuration.

Run checks from the repository root:

```bash
composer test
node --test tests/JavaScript/*.test.mjs
npm install
npx playwright install chromium
npm run test:browser
```

Playwright starts the local workbench automatically. Browser reports and screenshots are also retained as CI artifacts. PHP tests use an isolated in-memory database; browser tests use the separate workbench.

`HeaderAssetsTest` checks initial stylesheet placement after the panel theme, panel isolation and the Alpine markup. `asset-loading.spec.mjs` covers styling without JavaScript, delayed CSS and header JavaScript, theme precedence, layout stability during initialization, and entering from a native page through SPA navigation. These cases inspect the initial load before `data-fph-ready`, which the existing scrolling and action tests wait for.

The navigation demo is `/demo/navigation`. Its URL options include `breadcrumbs=outside|inside|hidden`, `navigation=0|1`, `retain=0|1` and `mode=normal|sticky|compact`. The two demo pages use native generated navigation items and SPA links.

`HeaderNavigationTest` covers configuration, native component placement, visibility, hooks, fallbacks and Cluster entry redirects. `RecordNavigationTest` exercises real Resource records, `ManageRelatedRecords` and independent Relation Managers, including combined content tabs before and after relations. `header-navigation.spec.mjs` covers mobile dropdowns, desktop tabs, light/dark appearance, SPA active state, native/integrated transitions, compact visibility, stable footprints and Livewire updates to retained outside breadcrumbs.

The README uses the product fixture at `/demo/headers?variant=12&mode=compact`: Everyday Runner, a fictional reference, two badges, three metadata fields and two native actions. The demo panel uses `Color::Indigo` for its primary color and the Preview action uses gray. The existing customer fixture remains available for more complex compositions. These palettes belong to the demo and do not change consuming applications.

To regenerate the matching desktop/mobile and light/dark captures:

```bash
npm run test:browser -- tests/Browser/product-showcase.spec.mjs
```

Inspect the output under `test-results/` before copying the selected `product-{theme}-{width}.png` and desktop `-compact.png` header images to `docs/screenshots/`. Keep generated reports, traces and other test output out of Git. See the [verification record](verification.md) for previous executed checks and their limits.

[Local development](local-development.md) · [Back to the README](../README.md)

## Product image provenance

`workbench/public/product-runner.png` is an illustrative asset generated with the built-in image generation tool for this demo. It is not a photograph of an actual catalog item. Screenshot capture uses the real Filament page, without altering its rendered layout or colors afterward.

Generation prompt:

> Use case: product-mockup. Create one square ecommerce catalog photograph of a single unbranded off-white and light-grey running sneaker, subtle dark charcoal details, viewed from a three-quarter side angle. Entire shoe visible, comfortably fills 85 percent of frame, softly lit pale neutral studio background, natural soft ground shadow, crisp mesh and rubber texture. No text, logo, watermark, people, labels, panels, UI or collage. This is a small product photo asset for a real Filament product header demo, not a screenshot or UI mockup. Clean premium minimal catalog photography.
