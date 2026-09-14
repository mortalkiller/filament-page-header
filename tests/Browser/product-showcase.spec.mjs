import { test, expect } from '@playwright/test';

for (const theme of ['light', 'dark']) {
    for (const width of [390, 1440]) {
        test(`product showcase in ${theme} at ${width}px`, async ({ page }, testInfo) => {
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.setViewportSize({ width, height: 1000 });
            await page.emulateMedia({ colorScheme: theme, reducedMotion: 'reduce' });
            await page.goto('/demo/headers?variant=12&mode=compact');
            await page.evaluate(theme => document.documentElement.classList.toggle('dark', theme === 'dark'), theme);
            const header = page.locator('[data-fph-header]');
            const image = header.locator('img');
            const actions = header.locator('.fph-actions button');
            await expect(header.getByRole('heading', { name: 'Everyday Runner' })).toBeVisible();
            await expect(image).toBeVisible();
            await expect.poll(() => image.evaluate(element => element.complete && element.naturalWidth > 0)).toBe(true);
            await expect(actions).toHaveCount(2);
            await expect(header.locator('.fph-metadata')).toContainText('128 units');
            await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
            if (width === 390) {
                const preview = await actions.nth(0).boundingBox();
                const save = await actions.nth(1).boundingBox();
                expect(save.y).toBeGreaterThanOrEqual(preview.y + preview.height);
                expect(Math.abs(save.width - preview.width)).toBeLessThan(2);
            }
            await header.screenshot({ path: testInfo.outputPath(`product-${theme}-${width}.png`) });
            await page.getByLabel('Unsaved note', { exact: true }).fill('Product demo note');
            await page.evaluate(() => window.scrollTo(0, 700));
            await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'true');
            await expect(header.locator('.fph-metadata')).toBeHidden();
            await expect(header.locator('.fph-description')).toBeHidden();
            await expect(image).toBeVisible();
            await expect(actions).toHaveCount(2);
            await expect(header).toHaveCSS('border-top-left-radius', '0px');
            await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
            await header.screenshot({ path: testInfo.outputPath(`product-${theme}-${width}-compact.png`) });
            await header.getByRole('button', { name: 'Preview', exact: true }).click();
            await expect(page.getByText('Demo product preview. No catalog data is changed.')).toBeVisible();
            await page.keyboard.press('Escape');
            await expect(page.getByText('Demo product preview. No catalog data is changed.')).toBeHidden();
            await header.getByRole('button', { name: 'Save changes', exact: true }).click();
            await expect(page.locator('[data-save-count]')).toHaveText('Saved 1 times');
            await expect(page.getByLabel('Unsaved note', { exact: true })).toHaveValue('Product demo note');
            expect(errors).toEqual([]);
        });
    }
}
