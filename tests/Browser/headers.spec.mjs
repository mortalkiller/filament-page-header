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

test('busy desktop headers share the identity row while metadata remains readable', async ({ page }) => {
    await page.setViewportSize({ width: 1582, height: 900 });
    const errors = await openHeader(page, 'variant=11&mode=normal');

    const collapseSidebar = page.getByRole('button', { name: 'Collapse sidebar', exact: true });
    if (await collapseSidebar.isVisible()) {
        await collapseSidebar.click();
        await expect(page.getByRole('button', { name: 'Expand sidebar', exact: true })).toBeVisible();
    }

    const content = page.locator('.fph-content');
    const main = page.locator('.fph-main');
    const actions = page.locator('.fph-actions');

    for (const label of [
        'Guardar alterações',
        'Cancelar',
        'Criar orçamento',
        'Criar documento',
        'Sincronizar cliente com Moloni',
        'Mais',
    ]) {
        await expect(page.getByRole('button', { name: label, exact: true })).toBeVisible();
    }

    const contentBox = await content.boundingBox();
    const mainBox = await main.boundingBox();
    const actionsBox = await actions.boundingBox();
    expect(contentBox).not.toBeNull();
    expect(mainBox).not.toBeNull();
    expect(actionsBox).not.toBeNull();
    expect(contentBox.width).toBeGreaterThanOrEqual(256);
    expect(mainBox.width).toBeGreaterThanOrEqual(180);
    expect(Math.abs(actionsBox.y - contentBox.y)).toBeLessThanOrEqual(1);
    expect(actionsBox.x).toBeGreaterThanOrEqual(contentBox.x + contentBox.width);

    const metadataCellWidths = await page.locator('.fph-metadata .fi-in-entry-label').evaluateAll(labels => labels.map(label => {
        const cell = label.closest('.fi-sc-component');
        return cell?.getBoundingClientRect().width ?? 0;
    }));
    expect(metadataCellWidths).toHaveLength(6);
    expect(Math.min(...metadataCellWidths)).toBeGreaterThanOrEqual(95);

    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1)).toBe(true);
    expect(errors).toEqual([]);
});

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
    const errors = await openHeader(page);
    await page.evaluate(() => window.scrollTo(0, 700));
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-stuck', 'true');
    await page.getByRole('button', { name: 'More', exact: true }).click();
    await page.getByRole('button', { name: 'Confirm action', exact: true }).click();
    const dialog = page.getByRole('alertdialog', { name: 'Confirm action', exact: true });
    const modalWindow = dialog.locator('.fi-modal-window');
    await expect(modalWindow).toBeVisible();
    await expect(dialog).toHaveAttribute('aria-modal', 'true');
    await expect(dialog).toHaveClass(/fi-modal-open/);
    const confirm = dialog.getByRole('button', { name: 'Confirm', exact: true });
    await expect(confirm).toBeVisible();
    await confirm.click();
    await expect(page.locator('.fph-badges')).toContainText('Confirmed');
    await expect(modalWindow).not.toBeVisible();
    expect(errors).toEqual([]);
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
