import { test, expect } from '@playwright/test';

const actionLabels = [
    'Guardar alterações',
    'Cancelar',
    'Criar orçamento',
    'Criar documento',
    'Sincronizar cliente com Moloni',
    'Mais',
];

async function openCustomerHeader(page, mode = 'normal') {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto(`/demo/headers?variant=11&mode=${mode}`);
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
    await expect(page.locator('h1')).toHaveCount(1);
    return errors;
}

async function expectReadableMetadata(page) {
    const entries = page.locator('.fph-metadata .fi-in-entry');
    await expect(entries).toHaveCount(6);
    await expect.poll(async () => {
        const widths = await entries.evaluateAll(elements => elements.map(element => element.getBoundingClientRect().width));
        return Math.min(...widths);
    }, { message: 'Metadata cells must not be crushed by actions or trailing metrics' }).toBeGreaterThanOrEqual(95);
}

async function expectNativeActions(page) {
    for (const label of actionLabels) {
        const action = page.locator('.fph-actions').getByRole('button', { name: label, exact: true });
        await expect(action).toHaveCount(1);
        await expect(action).toBeVisible();
    }
}

async function expectNoOverflow(page) {
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1)).toBe(true);
}

for (const width of [360, 390, 768, 1024, 1280, 1440, 1582, 1600, 1920, 2560]) {
    test(`rich full-width header remains readable at ${width}px`, async ({ page }, testInfo) => {
        await page.setViewportSize({ width, height: 1000 });
        const errors = await openCustomerHeader(page);
        await expectReadableMetadata(page);
        await expectNativeActions(page);
        await expectNoOverflow(page);

        if (width >= 1024) {
            await page.getByRole('button', { name: 'Collapse sidebar', exact: true }).click();
            await expect(page.getByRole('button', { name: 'Expand sidebar', exact: true })).toBeVisible();
            await expectReadableMetadata(page);
            await expectNativeActions(page);
            await expectNoOverflow(page);
        }

        await page.locator('[data-fph-header]').screenshot({ path: testInfo.outputPath('header.png') });
        expect(errors).toEqual([]);
    });
}

for (const mode of ['sticky', 'compact']) {
    for (const width of [1024, 1582]) {
        test(`rich ${mode} header preserves actions and unsaved input at ${width}px`, async ({ page }, testInfo) => {
            await page.setViewportSize({ width, height: 1000 });
            const errors = await openCustomerHeader(page, mode);
            const root = page.locator('[data-fph-root]');
            const header = page.locator('[data-fph-header]');
            const note = page.getByRole('textbox', { name: 'Unsaved note', exact: true });
            await note.fill('Unsaved customer changes');
            await page.evaluate(() => window.scrollTo(0, 0));
            await expect(root).toHaveAttribute('data-fph-stuck', 'false');
            await expectReadableMetadata(page);
            const expandedHeight = (await header.boundingBox()).height;
            const contentTop = await page.locator('[data-demo-content]').evaluate(element => element.getBoundingClientRect().top + window.scrollY);
            const requests = [];
            page.on('request', request => { if (request.method() !== 'GET') requests.push(request.url()); });

            await page.evaluate(() => window.scrollTo(0, 700));
            await expect(root).toHaveAttribute('data-fph-stuck', 'true');
            await expect(root).toHaveAttribute('data-fph-compact', mode === 'compact' ? 'true' : 'false');
            await expectNativeActions(page);
            await expect(note).toHaveValue('Unsaved customer changes');
            await expectNoOverflow(page);
            expect(await page.locator('[data-demo-content]').evaluate(element => element.getBoundingClientRect().top + window.scrollY)).toBeCloseTo(contentTop, 0);

            if (mode === 'compact') {
                await expect(page.locator('.fph-metadata')).toBeHidden();
                expect((await header.boundingBox()).height).toBeLessThan(expandedHeight);
            } else {
                await expectReadableMetadata(page);
                expect((await header.boundingBox()).height).toBeCloseTo(expandedHeight, 0);
            }

            await page.getByRole('button', { name: 'Mais', exact: true }).click();
            await expect(page.getByRole('button', { name: 'Unir cliente', exact: true })).toBeVisible();
            await page.keyboard.press('Escape');
            await header.screenshot({ path: testInfo.outputPath(`header-${mode}.png`) });
            await page.evaluate(() => window.scrollTo(0, 0));
            await expect(root).toHaveAttribute('data-fph-stuck', 'false');
            await expectReadableMetadata(page);
            await expect(note).toHaveValue('Unsaved customer changes');
            expect(requests).toEqual([]);
            expect(errors).toEqual([]);
        });
    }
}

test('simple headers retain side-by-side actions when there is enough room', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1000 });
    await page.goto('/demo/headers?variant=1&mode=normal');
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
    const content = await page.locator('.fph-content').boundingBox();
    const actions = await page.locator('.fph-actions').boundingBox();
    expect(Math.abs(actions.y - content.y)).toBeLessThanOrEqual(1);
    expect(actions.x).toBeGreaterThanOrEqual(content.x + content.width);
    await expectNoOverflow(page);
});
