import { test, expect } from '@playwright/test';

async function open(page, query = 'variant=10&mode=compact') {
    await page.goto(`/demo/headers?${query}`);
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
    await page.evaluate(() => document.fonts.ready);
}

async function configure(page, changes) {
    await page.locator('[data-fph-root]').evaluate((root, values) => {
        root.dataset.fphOptions = JSON.stringify({ ...JSON.parse(root.dataset.fphOptions), ...values });
    }, changes);
}

test('responsive modes change without replacing inputs or sending requests', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 900 });
    await open(page);
    await page.getByRole('textbox', { name: 'Unsaved note', exact: true }).fill('Keep this value');
    await configure(page, { mode: 'compact', breakpoints: [{ minWidth: 768, mode: 'sticky' }, { minWidth: 1200, mode: 'normal' }] });
    const root = page.locator('[data-fph-root]');
    await page.evaluate(() => window.scrollTo(0, 600));
    await expect(root).toHaveAttribute('data-fph-compact', 'true');
    const requests = [];
    page.on('request', request => { if (['fetch', 'xhr'].includes(request.resourceType())) requests.push(request.url()); });
    await page.setViewportSize({ width: 1024, height: 900 });
    await expect(root).toHaveAttribute('data-fph-mode', 'sticky');
    await expect(root).toHaveAttribute('data-fph-compact', 'false');
    await page.setViewportSize({ width: 1440, height: 900 });
    await expect(root).toHaveAttribute('data-fph-mode', 'normal');
    await page.setViewportSize({ width: 390, height: 900 });
    await expect(root).toHaveAttribute('data-fph-compact', 'true');
    await expect(page.getByRole('textbox', { name: 'Unsaved note', exact: true })).toHaveValue('Keep this value');
    expect(requests).toEqual([]);
});

test('explicit offsets and missing topbars are supported', async ({ page }) => {
    await open(page);
    const root = page.locator('[data-fph-root]');
    await configure(page, { offset: 96 });
    await page.evaluate(() => window.scrollTo(0, 600));
    await expect(root).toHaveAttribute('data-fph-stuck', 'true');
    await expect.poll(async () => (await root.boundingBox()).y).toBeCloseTo(96, 0);
    await configure(page, { offset: null, topbarSelector: '.topbar-that-does-not-exist' });
    await expect.poll(async () => (await root.boundingBox()).y).toBeCloseTo(0, 0);
});

test('automatic offset follows the current topbar height', async ({ page }) => {
    await open(page);
    await page.evaluate(() => window.scrollTo(0, 600));
    await page.locator('.fi-topbar').first().evaluate(topbar => { topbar.style.height = '104px'; });
    await expect.poll(() => page.locator('[data-fph-root]').evaluate(root => parseFloat(root.style.getPropertyValue('--fph-offset')))).toBeGreaterThanOrEqual(104);
    const geometry = await page.evaluate(() => ({
        header: document.querySelector('[data-fph-header]').getBoundingClientRect().top,
        topbar: document.querySelector('.fi-topbar').getBoundingClientRect().bottom,
    }));
    expect(geometry.header).toBeGreaterThanOrEqual(geometry.topbar - 1);
});

test('a short viewport releases the header instead of trapping the form', async ({ page }) => {
    await open(page);
    await page.evaluate(() => window.scrollTo(0, 600));
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'true');
    await page.setViewportSize({ width: 390, height: 240 });
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-mode', 'normal');
    await page.getByRole('textbox', { name: 'Unsaved note', exact: true }).fill('Still editable');
    await expect(page.getByRole('textbox', { name: 'Unsaved note', exact: true })).toHaveValue('Still editable');
    await page.setViewportSize({ width: 390, height: 900 });
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-mode', 'compact');
});

test('focused fields and anchors are not covered by a compact header', async ({ page }) => {
    await open(page);
    await page.evaluate(() => {
        const field = document.createElement('input');
        field.id = 'focus-target';
        field.setAttribute('aria-label', 'Focus target');
        document.querySelector('[data-demo-content]').append(field);
        const top = field.getBoundingClientRect().top + window.scrollY;
        window.scrollTo(0, top - 80);
    });
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'true');
    await page.locator('#focus-target').evaluate(field => field.focus({ preventScroll: true }));
    await expect.poll(() => page.evaluate(() => {
        const field = document.querySelector('#focus-target').getBoundingClientRect();
        const header = document.querySelector('[data-fph-header]').getBoundingClientRect();
        return field.top >= header.bottom;
    })).toBe(true);
    await page.evaluate(() => { window.location.hash = 'focus-target'; });
    await expect.poll(() => page.evaluate(() => document.querySelector('#focus-target').getBoundingClientRect().top >= document.querySelector('[data-fph-header]').getBoundingClientRect().bottom)).toBe(true);
});

test('compact threshold is stable across repeated crossings', async ({ page }) => {
    await open(page);
    const threshold = await page.locator('[data-fph-root]').evaluate(root => root.getBoundingClientRect().top + window.scrollY - parseFloat(root.style.getPropertyValue('--fph-offset')));
    const root = page.locator('[data-fph-root]');
    for (let index = 0; index < 6; index++) {
        await page.evaluate(y => window.scrollTo(0, y), threshold + 4);
        await expect(root).toHaveAttribute('data-fph-compact', 'true');
        await page.evaluate(y => window.scrollTo(0, y), Math.max(0, threshold - 4));
        await expect(root).toHaveAttribute('data-fph-compact', 'false');
    }
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.getByRole('button', { name: 'Save draft', exact: true })).toHaveCount(1);
});

test('nested scroll containers retain one controller and restore layout on disposal', async ({ page }) => {
    await open(page, 'variant=1&mode=normal');
    await page.evaluate(async () => {
        const root = document.querySelector('[data-fph-root]');
        const module = await import(root.getAttribute('x-load-src'));
        const parent = root.parentElement;
        parent.style.height = '520px';
        parent.style.overflowY = 'auto';
        parent.style.position = 'relative';
        parent.dataset.nestedScroller = 'true';
        const options = { ...JSON.parse(root.dataset.fphOptions), mode: 'sticky', offset: 0 };
        window.testHeaderController = new module.HeaderController(root, options);
        window.testHeaderController.start();
    });
    await page.locator('[data-nested-scroller]').evaluate(parent => { parent.scrollTop = 400; });
    const root = page.locator('[data-fph-root]');
    await expect(root).toHaveAttribute('data-fph-stuck', 'true');
    const geometry = await page.evaluate(() => ({
        root: document.querySelector('[data-fph-root]').getBoundingClientRect().top,
        parent: document.querySelector('[data-nested-scroller]').getBoundingClientRect().top,
    }));
    expect(geometry.root).toBeCloseTo(geometry.parent, 0);
    await page.evaluate(() => { window.testHeaderController.destroy(); window.testHeaderController.destroy(); });
    await expect(root).not.toHaveAttribute('data-fph-ready', 'true');
    await expect(root).not.toHaveAttribute('data-fph-stuck', 'true');
    expect(await root.evaluate(element => element.style.height)).toBe('');
});
