# Demo and testing

From this repository:

```bash
composer install
php bin/prepare-workbench.php
php workbench/artisan package:discover
php workbench/artisan filament:assets
php workbench/artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000/demo/headers`. The workbench demonstrates twelve compositions and all three scroll modes. It is a **local testing application**, not a production application; do not expose it publicly. It does not need Pressiu's database or theme.

Run checks from the repository root:

```bash
composer test
node --test tests/JavaScript/*.test.mjs
npm install
npx playwright install chromium
npm run test:browser
```

Playwright starts the local workbench automatically. Browser reports and screenshots are also retained as CI artifacts. PHP tests use an isolated in-memory database; browser tests use the separate workbench.

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
