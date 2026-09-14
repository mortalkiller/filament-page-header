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


for (const theme of ['light', 'dark']) {
    for (const width of [390, 1440]) {
        test(`approved customer composition keeps right-aligned actions in ${theme} at ${width}px`, async ({ page }, testInfo) => {
            await page.setViewportSize({ width, height: 1000 });
            const errors = await openCustomerHeader(page, 'compact');
            await page.evaluate(theme => document.documentElement.classList.toggle('dark', theme === 'dark'), theme);
            const header = page.locator('[data-fph-header]');
            await expect(page.locator('.fph-avatar')).toContainText('MC');
            await expect(page.locator('.fph-summary')).toContainText('67%');
            await expect(page.locator('.fph-metadata')).toContainText('C000042');
            await expectNativeActions(page);
            await expectNoOverflow(page);

            const background = await header.evaluate(element => getComputedStyle(element).backgroundColor);
            expect(background).not.toBe('rgba(0, 0, 0, 0)');
            const lightBackground = await header.evaluate(element => {
                const value = getComputedStyle(element).backgroundColor;
                const canvas = document.createElement('canvas');
                canvas.width = canvas.height = 1;
                const context = canvas.getContext('2d');
                context.fillStyle = value;
                context.fillRect(0, 0, 1, 1);
                const rgba = context.getImageData(0, 0, 1, 1).data;
                return (rgba[0] + rgba[1] + rgba[2]) / 3 > 128;
            });
            expect(lightBackground).toBe(theme === 'light');

            if (width >= 768) await expect.poll(() => page.locator('.fph-actions > .fi-ac').evaluate(container => {
                const children = [...container.children].filter(element => element.getClientRects().length);
                const rows = new Map();
                for (const element of children) {
                    const rect = element.getBoundingClientRect();
                    const y = Math.round(rect.top + rect.height / 2);
                    rows.set(y, Math.max(rows.get(y) ?? 0, rect.right));
                }
                const right = container.getBoundingClientRect().right;
                return [...rows.values()].every(edge => Math.abs(edge - right) <= 2);
            })).toBe(true);
            await expectMetadataDecorations(page);
            const breadcrumbs = page.locator('.fph-breadcrumbs');
            await expect(breadcrumbs).toBeVisible();
            expect(await breadcrumbs.evaluate(element => element.closest('[data-fph-root]'))).toBeNull();
            expect((await breadcrumbs.boundingBox()).y + (await breadcrumbs.boundingBox()).height).toBeLessThan((await header.boundingBox()).y);
            await expect(header).toHaveCSS('border-top-left-radius', '12px');
            if (width < 768) await expectMobileActionColumn(page);
            await page.screenshot({ path: testInfo.outputPath(`customer-${theme}-${width}-page.png`) });
            await header.screenshot({ path: testInfo.outputPath(`customer-${theme}-${width}.png`) });
            await page.evaluate(() => window.scrollTo(0, 700));
            await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'true');
            await expect(page.locator('.fph-summary')).toBeHidden();
            await expect(page.locator('.fph-metadata')).toBeHidden();
            await expect(page.locator('.fph-description')).toBeHidden();
            await expectNativeActions(page);
            await expectNoOverflow(page);
            await expect(header).toHaveCSS('border-top-left-radius', '0px');
            await expect(header).toHaveCSS('border-top-right-radius', '0px');
            await expect(header).toHaveCSS('border-bottom-left-radius', '12px');
            expect(await breadcrumbs.evaluate(element => element.getBoundingClientRect().bottom)).toBeLessThan(0);
            if (width < 768) {
                await expectMobileActionColumn(page);
                await page.getByRole('button', { name: 'Outros documentos', exact: true }).click();
                await expect(page.getByRole('button', { name: 'Criar fatura', exact: true })).toBeVisible();
                await page.keyboard.press('Escape');
                await page.getByRole('button', { name: 'Mais', exact: true }).click();
                await expect(page.getByRole('button', { name: 'Unir cliente', exact: true })).toBeVisible();
                await page.keyboard.press('Escape');
            }
            await header.screenshot({ path: testInfo.outputPath(`customer-${theme}-${width}-compact.png`) });
            await page.evaluate(() => window.scrollTo(0, 0));
            await expect(header).toHaveCSS('border-top-left-radius', '12px');
            expect(errors).toEqual([]);
        });
    }
}

async function expectMobileActionColumn(page) {
    const container = await page.locator('.fph-actions > .fi-ac').boundingBox();
    let previousBottom = 0;
    for (const label of actionLabels.slice(0, -1)) {
        const button = page.locator('.fph-actions').getByRole('button', { name: label, exact: true });
        const row = label === 'Criar documento' ? page.locator('.fph-actions .fi-btn-group') : button;
        const rect = await row.boundingBox();
        expect(rect.width).toBeCloseTo(container.width, 0);
        expect(rect.x).toBeCloseTo(container.x, 0);
        expect(rect.y).toBeGreaterThanOrEqual(previousBottom);
        previousBottom = rect.y + rect.height;
    }
    const arrow = await page.getByRole('button', { name: 'Outros documentos', exact: true }).boundingBox();
    expect(arrow.width).toBeGreaterThanOrEqual(44);
    expect(arrow.width).toBeLessThan(60);
    expect(arrow.x + arrow.width).toBeCloseTo(container.x + container.width, 0);
    const more = await page.getByRole('button', { name: 'Mais', exact: true }).boundingBox();
    expect(more.y).toBeGreaterThanOrEqual(previousBottom);
    expect(more.x + more.width / 2).toBeCloseTo(container.x + container.width / 2, 0);
    expect(more.width).toBeLessThan(60);
}


async function expectMetadataDecorations(page) {
    await expect.poll(() => page.locator('.fph-metadata > .fi-sc').evaluate(schema => {
        const fields = [...schema.children].filter(field => field.getBoundingClientRect().width > 0);
        return fields.every(field => {
            const rect = field.getBoundingClientRect();
            const hasPrecedingField = fields.some(other => other !== field
                && Math.abs(other.getBoundingClientRect().top - rect.top) <= 1
                && other.getBoundingClientRect().right <= rect.left);
            const divider = getComputedStyle(field, '::before');
            return hasPrecedingField
                ? divider.content !== 'none' && divider.borderInlineStartWidth === '1px'
                : divider.content === 'none';
        });
    })).toBe(true);
    for (const position of ['before', 'after']) {
        const field = page.locator(`.fph-field[data-fph-icon-position="${position}"]`).first();
        const icon = await field.locator('.fph-field-icon').boundingBox();
        const entry = await field.locator('.fi-in-entry').boundingBox();
        if (position === 'before') expect(icon.x + icon.width).toBeLessThan(entry.x);
        else expect(icon.x).toBeGreaterThan(entry.x + entry.width);
        expect(icon.y + icon.height / 2).toBeCloseTo(entry.y + entry.height / 2, 0);
        expect(icon.width).toBe(position === 'after' ? 32 : 24);
        const gap = position === 'before' ? entry.x - icon.x - icon.width : icon.x - entry.x - entry.width;
        expect(gap).toBeCloseTo(16, 0);
        await expect(field.locator('.fph-field-icon')).toHaveAttribute('aria-hidden', 'true');
    }
}

test('metadata dividers follow wrapping and removal without scroll requests', async ({ page }) => {
    await page.setViewportSize({ width: 1920, height: 1000 });
    const errors = await openCustomerHeader(page);
    const requests = [];
    page.on('request', request => { if (request.method() !== 'GET') requests.push(request.url()); });
    for (const width of [1920, 390, 1280]) {
        await page.setViewportSize({ width, height: 1000 });
        await expectMetadataDecorations(page);
        await expectNoOverflow(page);
    }
    await page.locator('.fph-metadata > .fi-sc > .fi-grid-col').first().evaluate(field => field.remove());
    await expectMetadataDecorations(page);
    expect(requests).toEqual([]);
    expect(errors).toEqual([]);
});

for (const width of [390, 1440]) {
    test(`typed compact selection preserves selected fields and native actions at ${width}px`, async ({ page }) => {
        const errors = [];
        const requests = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.setViewportSize({ width, height: 1400 });
        await page.goto('/demo/headers?variant=11&mode=compact&selective=1');
        await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
        const root = page.locator('[data-fph-root]');
        const header = page.locator('[data-fph-header]');
        const restingShadow = await header.evaluate(element => getComputedStyle(element).boxShadow);
        await expect.poll(() => header.evaluate(element => getComputedStyle(element).transitionDuration)).toContain('0.15s');
        const fields = page.locator('.fph-metadata > .fi-sc > .fi-grid-col');
        await expect(fields).toHaveCount(6);
        await expect(fields.nth(0)).toBeVisible();
        await page.getByLabel('Unsaved note').fill('Keep this unsaved value');
        page.on('request', request => { if (request.method() !== 'GET') requests.push(request.url()); });
        await page.evaluate(() => window.scrollTo(0, 850));
        await expect(root).toHaveAttribute('data-fph-compact', 'true');
        await expect(page.locator('.fph-description')).toBeVisible();
        await expect.poll(async () => {
            const [heading, description] = await Promise.all([
                page.locator('.fph-heading').boundingBox(),
                page.locator('.fph-description').boundingBox(),
            ]);

            return heading !== null && description !== null
                && description.y >= heading.y + heading.height
                && Math.abs(description.x - heading.x) <= 1;
        }).toBe(true);
        await expect.poll(() => header.evaluate(element => getComputedStyle(element).boxShadow)).not.toBe(restingShadow);
        await expect(fields.nth(0)).toBeHidden();
        await expect(fields.nth(1)).toBeVisible();
        await expect(fields.nth(2)).toBeHidden();
        await expect(fields.nth(3)).toBeVisible();
        await expect(fields.nth(4)).toBeHidden();
        await expect(fields.nth(1)).toHaveAttribute('data-fph-divider-compact', 'false');
        await expect.poll(() => fields.nth(1).evaluate(field => getComputedStyle(field, '::before').content)).toBe('none');
        await expect(page.locator('.fph-summary > .fi-sc > .fi-grid-col').nth(0)).toBeHidden();
        await expect(page.locator('.fph-summary > .fi-sc > .fi-grid-col').nth(1)).toBeVisible();
        await expect(page.locator('.fph-badges').getByText('Não sincronizado')).toBeHidden();
        await expectNativeActions(page);
        await expectNoOverflow(page);
        await page.evaluate(() => window.scrollTo(0, 0));
        await expect(fields.nth(0)).toBeVisible();
        await expect(fields.nth(2)).toBeVisible();
        await expect(fields).toHaveCount(6);
        await expect(page.getByLabel('Unsaved note')).toHaveValue('Keep this unsaved value');
        await page.evaluate(() => window.scrollTo(0, 0));
        await expect(root).toHaveAttribute('data-fph-stuck', 'false');
        await expect.poll(() => header.evaluate(element => getComputedStyle(element).boxShadow)).toBe(restingShadow);
        expect(requests).toEqual([]);
        expect(errors).toEqual([]);
    });
}
