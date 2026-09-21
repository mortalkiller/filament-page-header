import { test, expect } from '@playwright/test';

async function openNavigation(page, query = '') {
    await page.goto(`/demo/navigation?${query}`);
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
}

for (const width of [390, 1440]) {
    for (const colorScheme of ['light', 'dark']) {
        test(`native navigation is responsive and keeps SPA state at ${width}px in ${colorScheme}`, async ({ page }) => {
            await page.setViewportSize({ width, height: 1000 });
            await page.addInitScript(scheme => localStorage.setItem('theme', scheme), colorScheme);
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await openNavigation(page, 'retain=1');
            await page.evaluate(() => { window.navigationSession = 'survives-SPA'; });
            await page.locator('[data-fph-root]').screenshot({ path: `test-results/header-navigation-${colorScheme}-${width}.png` });
            const nav = page.locator('[data-fph-sub-navigation]');
            await expect(nav).toHaveCount(1);
            await expect(page.locator('.fi-page-sub-navigation-sidebar-ctn')).toHaveCount(0);
            await expect(page.locator('.fph-breadcrumbs')).toHaveCount(1);
            const desktop = nav.locator('.fi-page-sub-navigation-tabs');
            const mobile = nav.locator('.fi-page-sub-navigation-dropdown');
            if (width < 768) {
                await expect(desktop).toBeHidden();
                await expect(mobile).toBeVisible();
                await mobile.getByRole('button', { name: 'Navigation overview', exact: true }).click();
                await expect(mobile.getByRole('link', { name: 'Navigation overview', exact: true })).toHaveAttribute('aria-current', 'page');
                await mobile.getByRole('link', { name: 'Navigation details', exact: true }).click();
            } else {
                await expect(desktop).toBeVisible();
                await expect(mobile).toBeHidden();
                await expect(desktop.getByRole('link', { name: 'Navigation overview', exact: true })).toHaveClass(/fi-active/);
                await desktop.getByRole('link', { name: 'Navigation details', exact: true }).click();
            }
            await expect(page).toHaveURL(/navigation-details/);
            expect(await page.evaluate(() => window.navigationSession)).toBe('survives-SPA');
            await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
            await expect(page.locator('[data-fph-sub-navigation]')).toHaveCount(1);
            await expect(page.getByRole('heading', { name: 'Navigation details', exact: true })).toBeVisible();
            if (width >= 768) await expect(desktop.getByRole('link', { name: 'Navigation details', exact: true })).toHaveClass(/fi-active/);
            else await expect(mobile.getByRole('button', { name: 'Navigation details', exact: true })).toBeVisible();
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1)).toBe(true);
            expect(errors).toEqual([]);
        });
    }
}

for (const breadcrumbs of ['inside', 'outside']) {
    for (const retain of [false, true]) {
        test(`compact ${breadcrumbs} breadcrumbs with retain=${retain} preserve the page footprint`, async ({ page }) => {
            await page.setViewportSize({ width: 1440, height: 1000 });
            await openNavigation(page, `breadcrumbs=${breadcrumbs}&retain=${retain ? 1 : 0}`);
            const root = page.locator('[data-fph-root]');
            const content = page.locator('[data-navigation-content]');
            const documentTop = await content.evaluate(el => el.getBoundingClientRect().top + scrollY);
            const rootHeight = await root.evaluate(el => el.getBoundingClientRect().height);
            if (breadcrumbs === 'outside' && retain) {
                const surfaceHeight = await page.locator('[data-fph-surface]').evaluate(el => el.getBoundingClientRect().height);
                expect(rootHeight).toBeGreaterThanOrEqual(surfaceHeight - 1);
            }
            await page.getByRole('textbox', { name: 'Unsaved navigation note' }).fill('Preserve this note');
            await page.evaluate(() => window.scrollTo(0, 700));
            await expect(root).toHaveAttribute('data-fph-compact', 'true');
            const nav = page.locator('[data-fph-sub-navigation]');
            if (retain) {
                await expect(nav).toBeVisible();
                await expect(page.locator('.fph-breadcrumbs')).toBeInViewport();
            } else {
                await expect(nav).toBeHidden();
                if (breadcrumbs === 'inside') await expect(page.locator('.fph-breadcrumbs')).toBeHidden();
                else await expect(page.locator('.fph-breadcrumbs')).not.toBeInViewport();
            }
            expect(await content.evaluate(el => el.getBoundingClientRect().top + scrollY)).toBeCloseTo(documentTop, 0);
            await page.evaluate(() => window.scrollTo(0, 0));
            await expect(root).toHaveAttribute('data-fph-compact', 'false');
            await expect(page.locator('.fph-breadcrumbs')).toBeVisible();
            await expect(page.getByRole('textbox', { name: 'Unsaved navigation note' })).toHaveValue('Preserve this note');
            expect(await content.evaluate(el => el.getBoundingClientRect().top + scrollY)).toBeCloseTo(documentTop, 0);
        });
    }
}

test('hidden breadcrumbs are absent and normal and sticky modes preserve navigation', async ({ page }) => {
    for (const mode of ['normal', 'sticky']) {
        await openNavigation(page, `breadcrumbs=hidden&mode=${mode}`);
        await expect(page.locator('.fph-breadcrumbs')).toHaveCount(0);
        await page.evaluate(() => window.scrollTo(0, 700));
        await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'false');
        await expect(page.locator('[data-fph-sub-navigation]')).toBeVisible();
        if (mode === 'sticky') await expect(page.locator('[data-fph-sub-navigation]')).toBeInViewport();
        else await expect(page.locator('[data-fph-sub-navigation]')).not.toBeInViewport();
    }
});

test('SPA switches between native and integrated sub-navigation without duplication', async ({ page }) => {
    await openNavigation(page, 'navigation=0');
    await expect(page.locator('[data-fph-sub-navigation]')).toHaveCount(0);
    await expect(page.locator('.fi-page-sub-navigation-sidebar-ctn')).toHaveCount(1);
    await page.locator('.fi-sidebar').getByRole('link', { name: 'Navigation details', exact: true }).click();
    await expect(page).toHaveURL(/navigation-details/);
    await expect(page.locator('[data-fph-sub-navigation]')).toHaveCount(1);
    await expect(page.locator('.fi-page-sub-navigation-sidebar-ctn')).toHaveCount(0);
    await page.goBack();
    await expect(page.locator('[data-fph-sub-navigation]')).toHaveCount(0);
    await expect(page.locator('.fi-page-sub-navigation-sidebar-ctn')).toHaveCount(1);
});

for (const width of [390, 1440]) {
    test(`Livewire updates remeasure retained outside breadcrumbs at ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 1000 });
        await openNavigation(page, 'breadcrumbs=outside&retain=1');
        const root = page.locator('[data-fph-root]');
        const surface = page.locator('[data-fph-surface]');
        const content = page.locator('[data-navigation-content]');
        await page.getByRole('textbox', { name: 'Unsaved navigation note' }).fill('Keep on morph');
        await page.getByRole('button', { name: 'Toggle compact navigation' }).click();
        await expect(root.locator('.fph-breadcrumbs')).toHaveCount(0);
        await page.getByRole('button', { name: 'Toggle compact navigation' }).click();
        await expect(root.locator('.fph-breadcrumbs')).toHaveCount(1);
        await expect.poll(async () => Math.abs((await root.boundingBox()).height - (await surface.boundingBox()).height)).toBeLessThanOrEqual(1);
        const contentTop = await content.evaluate(el => el.getBoundingClientRect().top + scrollY);
        const threshold = await root.evaluate(el => Math.ceil(el.getBoundingClientRect().top + scrollY - parseFloat(getComputedStyle(el).getPropertyValue('--fph-offset')) + 3));
        for (let i = 0; i < 4; i++) {
            await page.evaluate(y => window.scrollTo(0, y), threshold + i);
            await expect(root).toHaveAttribute('data-fph-compact', 'true');
            expect(await content.evaluate(el => el.getBoundingClientRect().top + scrollY)).toBeCloseTo(contentTop, 0);
            await expect(root.locator('.fph-breadcrumbs')).toBeInViewport();
        }
        await expect(page.getByRole('textbox', { name: 'Unsaved navigation note' })).toHaveValue('Keep on morph');
    });
}
