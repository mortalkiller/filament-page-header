import { test, expect } from '@playwright/test';

const stylesheet = 'head link[rel="stylesheet"][href*="/filament-page-header/page-header.css"]';
const cssPattern = /\/css\/mortalkiller\/filament-page-header\/page-header\.css(?:\?.*)?$/;
const jsPattern = /\/js\/mortalkiller\/filament-page-header\/components\/page-header\.js(?:\?.*)?$/;

function deferred() {
    let resolve;
    const promise = new Promise(done => { resolve = done; });
    return { promise, resolve };
}

async function geometry(page) {
    return page.evaluate(() => ({
        height: document.querySelector('[data-fph-header]').getBoundingClientRect().height,
        contentTop: document.querySelector('[data-demo-content]').getBoundingClientRect().top,
    }));
}

test.describe('initial HTML without JavaScript', () => {
    test.use({ javaScriptEnabled: false });

    for (const width of [390, 1440]) {
        test(`styles the header before Alpine at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.goto('/demo/headers?variant=10&mode=compact');

            await expect(page.locator(stylesheet)).toHaveCount(1);
            await expect(page.locator('[data-fph-header]')).toHaveCSS('padding-top', width < 768 ? '16px' : '20px');
            await expect(page.locator('.fph-main')).toHaveCSS('display', 'flex');
            await expect(page.locator('[data-fph-root]')).not.toHaveAttribute('data-fph-ready');
        });
    }
});

for (const mode of ['normal', 'sticky', 'compact']) {
    test(`delayed header JavaScript preserves the initial ${mode} layout after a custom theme`, async ({ page }) => {
        const release = deferred();
        const requested = deferred();
        // An equal-specificity theme rule catches accidental stylesheet reordering.
        await page.route(/\/css\/filament\/filament\/app\.css(?:\?.*)?$/, async route => {
            const response = await route.fetch();
            await route.fulfill({ response, body: `${await response.text()}\n.fph-root .fph-header { padding: 80px; }` });
        });
        await page.route(jsPattern, async route => {
            requested.resolve();
            await release.promise;
            await route.continue();
        });
        try {
            await page.goto(`/demo/headers?variant=10&mode=${mode}`, { waitUntil: 'domcontentloaded' });
            await requested.promise;
            await page.evaluate(() => document.fonts.ready);
            await expect(page.locator('[data-fph-root]')).not.toHaveAttribute('data-fph-ready');
            await expect(page.locator('[data-fph-header]')).toHaveCSS('padding-top', '20px');
            const before = await geometry(page);

            release.resolve();
            await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
            const after = await geometry(page);
            expect(Math.abs(after.height - before.height)).toBeLessThanOrEqual(1);
            expect(Math.abs(after.contentTop - before.contentTop)).toBeLessThanOrEqual(1);
            await expect(page.locator(stylesheet)).toHaveCount(1);
        } finally {
            release.resolve();
        }
    });
}

test('a slow stylesheet blocks the first content paint instead of restyling visible content', async ({ page }) => {
    const release = deferred();
    const requested = deferred();
    await page.route(cssPattern, async route => {
        requested.resolve();
        await release.promise;
        await route.continue();
    });
    try {
        await page.goto('/demo/headers?variant=10&mode=normal', { waitUntil: 'commit' });
        await requested.promise;
        // Deliberately hold CSS over several possible paint frames, as on a slow network.
        await page.waitForTimeout(250);
        expect(await page.evaluate(() => performance.getEntriesByName('first-contentful-paint').length)).toBe(0);

        release.resolve();
        await page.waitForLoadState('load');
        await expect(page.locator('[data-fph-header]')).toHaveCSS('padding-top', '20px');
        await expect.poll(() => page.evaluate(() => performance.getEntriesByName('first-contentful-paint').length)).toBe(1);
    } finally {
        release.resolve();
    }
});

test('entering from a native page through SPA reuses the stylesheet without duplicates', async ({ page }) => {
    const requests = [];
    page.on('request', request => {
        if (request.url().includes('/filament-page-header/page-header.css')) requests.push(request.url());
    });
    await page.goto('/demo/native');
    await expect(page.locator('[data-fph-root]')).toHaveCount(0);
    await expect(page.locator(stylesheet)).toHaveCount(1);
    expect(requests).toHaveLength(1);

    for (let visit = 0; visit < 2; visit++) {
        await page.getByRole('link', { name: 'Return to headers', exact: true }).click();
        await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
        await expect(page.locator(stylesheet)).toHaveCount(1);
        await page.locator('nav[aria-label="Header variants"]').getByRole('link', { name: 'Native page', exact: true }).click();
        await expect(page.locator('[data-fph-root]')).toHaveCount(0);
        await expect(page.locator(stylesheet)).toHaveCount(1);
    }
    expect(requests).toHaveLength(1);
});
