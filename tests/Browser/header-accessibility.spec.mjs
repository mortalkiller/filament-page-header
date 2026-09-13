import { test, expect } from '@playwright/test';

async function openHeader(page) {
    await page.goto('/demo/headers?variant=10&mode=compact');
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
}

test('focusing the topbar does not unexpectedly scroll the page', async ({ page }) => {
    await openHeader(page);
    await page.evaluate(() => window.scrollTo(0, 700));
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-stuck', 'true');
    const before = await page.evaluate(() => window.scrollY);
    await page.evaluate(() => {
        const topbar = document.querySelector('.fi-topbar');
        if (!topbar) throw new Error('The test requires the native Filament topbar.');
        const button = document.createElement('button');
        button.textContent = 'Topbar focus target';
        topbar.append(button);
        button.focus({ preventScroll: true });
    });
    await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
    expect(await page.evaluate(() => window.scrollY)).toBe(before);
});

test('a field focused in the page is not obscured by the pinned header', async ({ page }) => {
    await openHeader(page);
    const field = page.getByRole('textbox', { name: 'Unsaved note', exact: true });
    await field.evaluate(element => {
        element.scrollIntoView({ block: 'start' });
        element.focus({ preventScroll: true });
    });
    await expect(field).toBeFocused();
    await expect.poll(async () => {
        const fieldBox = await field.boundingBox();
        const headerBox = await page.locator('[data-fph-header]').boundingBox();
        const stuck = await page.locator('[data-fph-root]').getAttribute('data-fph-stuck');
        return stuck !== 'true' || fieldBox.y >= headerBox.y + headerBox.height;
    }).toBe(true);
});

test('orientation changes preserve form input and never overflow the viewport', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await openHeader(page);
    await page.getByRole('textbox', { name: 'Unsaved note', exact: true }).fill('Preserve across orientation changes');
    await page.evaluate(() => window.scrollTo(0, 700));
    for (const viewport of [{ width: 844, height: 390 }, { width: 390, height: 340 }, { width: 390, height: 844 }]) {
        await page.setViewportSize(viewport);
        await expect(page.getByRole('textbox', { name: 'Unsaved note', exact: true })).toHaveValue('Preserve across orientation changes');
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1)).toBe(true);
        await expect(page.getByRole('button', { name: 'Save draft', exact: true })).toHaveCount(1);
    }
});

test('the compact transition has a stable document footprint near its threshold', async ({ page }) => {
    await openHeader(page);
    const root = page.locator('[data-fph-root]');
    const anchor = await root.evaluate(element => element.getBoundingClientRect().top + scrollY);
    const offset = await root.evaluate(element => parseFloat(getComputedStyle(element).getPropertyValue('--fph-offset')));
    const threshold = Math.max(1, Math.ceil(anchor - offset + 4));
    const contentTop = await page.locator('[data-demo-content]').evaluate(element => element.getBoundingClientRect().top + scrollY);
    for (let iteration = 0; iteration < 5; iteration++) {
        await page.evaluate(y => window.scrollTo(0, y), threshold + iteration);
        await expect(root).toHaveAttribute('data-fph-compact', 'true');
        expect(await page.locator('[data-demo-content]').evaluate(element => element.getBoundingClientRect().top + scrollY)).toBeCloseTo(contentTop, 0);
    }
});
