import { test, expect } from '@playwright/test';

async function openHeader(page, query = 'variant=10&mode=compact') {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto(`/demo/headers?${query}`);
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
    await expect(page.locator('h1')).toHaveCount(1);
    return errors;
}

for (const width of [360, 390, 768, 1024, 1440]) {
    test(`renders every header variant without overflow at ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 900 });
        for (let variant = 1; variant <= 10; variant++) {
            const errors = await openHeader(page, `variant=${variant}&mode=normal`);
            await expect(page.getByRole('button', { name: 'Save draft', exact: true })).toBeVisible();
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1)).toBe(true);
            expect(errors).toEqual([]);
        }
        await page.locator('[data-fph-header]').screenshot({ path: `test-results/header-normal-${width}.png` });
    });
}

test('compact scroll preserves content position, inputs and one set of actions without requests', async ({ page }) => {
    const errors = await openHeader(page);
    await page.getByRole('textbox', { name: 'Unsaved note', exact: true }).fill('An unsaved note');
    const root = page.locator('[data-fph-root]');
    const header = page.locator('[data-fph-header]');
    const expandedHeight = (await header.boundingBox()).height;
    const documentTop = await page.locator('[data-demo-content]').evaluate(el => el.getBoundingClientRect().top + window.scrollY);
    const requests = [];
    page.on('request', request => { if (request.method() !== 'GET') requests.push(request.url()); });
    await page.evaluate(() => window.scrollTo(0, 700));
    await expect(root).toHaveAttribute('data-fph-compact', 'true');
    expect((await header.boundingBox()).height).toBeLessThan(expandedHeight);
    expect(await page.locator('[data-demo-content]').evaluate(el => el.getBoundingClientRect().top + window.scrollY)).toBeCloseTo(documentTop, 0);
    await expect(page.getByRole('button', { name: 'Save draft', exact: true })).toHaveCount(1);
    await expect(page.getByRole('textbox', { name: 'Unsaved note', exact: true })).toHaveValue('An unsaved note');
    await header.screenshot({ path: 'test-results/header-compact.png' });
    await page.evaluate(() => window.scrollTo(0, 0));
    await expect(root).toHaveAttribute('data-fph-compact', 'false');
    expect(requests).toEqual([]);
    expect(errors).toEqual([]);
});

test('normal mode scrolls away and sticky mode retains full content', async ({ page }) => {
    await openHeader(page, 'variant=10&mode=normal');
    await page.evaluate(() => window.scrollTo(0, 700));
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-stuck', 'false');
    expect((await page.locator('[data-fph-header]').boundingBox()).y).toBeLessThan(0);
    await openHeader(page, 'variant=10&mode=sticky');
    const height = (await page.locator('[data-fph-header]').boundingBox()).height;
    await page.evaluate(() => window.scrollTo(0, 700));
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-stuck', 'true');
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'false');
    expect((await page.locator('[data-fph-header]').boundingBox()).height).toBeCloseTo(height, 0);
});

test('native submit and schema actions execute once and update the header', async ({ page }) => {
    const errors = await openHeader(page);
    await page.getByRole('textbox', { name: 'Unsaved note', exact: true }).fill('Draft to save');
    await page.getByRole('button', { name: 'Save draft', exact: true }).click();
    await expect(page.locator('[data-save-count]')).toHaveText('Saved 1 times');
    await page.getByRole('button', { name: 'Toggle status', exact: true }).click();
    await expect(page.locator('[data-inline-count]')).toHaveText('Inline action 1 times');
    await expect(page.locator('.fph-badges')).toContainText('Approved');
    await expect(page.getByRole('textbox', { name: 'Unsaved note', exact: true })).toHaveValue('Draft to save');
    expect(errors).toEqual([]);
});

test('menu and confirmation remain above the sticky header', async ({ page }) => {
    await openHeader(page);
    await page.evaluate(() => window.scrollTo(0, 700));
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-stuck', 'true');
    await page.getByRole('button', { name: 'More', exact: true }).click();
    await page.getByRole('button', { name: 'Confirm action', exact: true }).click();
    const dialog = page.getByRole('dialog').filter({ hasText: 'Confirm action' });
    await expect(dialog).toBeVisible();
    await dialog.getByRole('button', { name: 'Confirm', exact: true }).click();
    await expect(page.locator('.fph-badges')).toContainText('Confirmed');
});

test('repeated SPA navigation does not leave duplicate controllers or headers', async ({ page }) => {
    const errors = await openHeader(page);
    for (let i = 0; i < 3; i++) {
        await page.locator('nav[aria-label="Header variants"]').getByRole('link', { name: 'Native page', exact: true }).click();
        await expect(page.getByRole('heading', { name: 'Native header', exact: true })).toBeVisible();
        await expect(page.locator('[data-fph-root]')).toHaveCount(0);
        await page.getByRole('link', { name: 'Return to headers', exact: true }).click();
        await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
        await expect(page.locator('[data-fph-root]')).toHaveCount(1);
        await page.evaluate(() => window.scrollTo(0, 700));
        await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'true');
        await page.evaluate(() => window.scrollTo(0, 0));
    }
    expect(errors).toEqual([]);
});

test('dark mode and reduced motion retain readable compact mobile headers', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.emulateMedia({ colorScheme: 'dark', reducedMotion: 'reduce' });
    await openHeader(page);
    await page.evaluate(() => { document.documentElement.classList.add('dark'); window.scrollTo(0, 700); });
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'true');
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1)).toBe(true);
    await expect(page.getByRole('button', { name: 'Save draft', exact: true })).toBeVisible();
    await page.locator('[data-fph-header]').screenshot({ path: 'test-results/header-dark-mobile.png' });
});
