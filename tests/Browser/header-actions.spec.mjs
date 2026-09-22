import { test, expect } from '@playwright/test';

const action = (page, name) => page.locator(`[data-fph-header-action="${name}"]`);

async function openHeader(page, options = {}) {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto(`/demo/action-control?${new URLSearchParams(options)}`);
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
    await expect(action(page, 'save')).toHaveCount(1);
    return errors;
}

async function compact(page) {
    await page.evaluate(() => window.scrollTo(0, 700));
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'true');
}

async function noOverflow(page) {
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1)).toBe(true);
}

async function clickAtCurrentPosition(page, locator) {
    const bounds = await locator.boundingBox();

    if (!bounds) {
        throw new Error('Expected the control to be visible before the pointer click.');
    }

    await page.mouse.click(
        bounds.x + bounds.width / 2,
        bounds.y + bounds.height / 2,
    );
}

for (const position of ['start', 'end', 'below']) {
    for (const direction of ['ltr', 'rtl']) {
        test(`${position} uses logical desktop positioning in ${direction}`, async ({ page }) => {
            await page.setViewportSize({ width: 1440, height: 1000 });
            const errors = await openHeader(page, { position, simple: '1' });
            await page.evaluate(direction => document.documentElement.dir = direction, direction);
            const content = await page.locator('.fph-content').boundingBox();
            const toolbar = await page.locator('.fph-actions').boundingBox();
            const details = await page.locator('.fph-details').boundingBox();
            if (position === 'below') {
                expect(toolbar.y).toBeGreaterThanOrEqual(details.y + details.height - 1);
            } else if ((position === 'start') === (direction === 'ltr')) {
                expect(toolbar.x + toolbar.width).toBeLessThanOrEqual(content.x + 1);
            } else {
                expect(toolbar.x).toBeGreaterThanOrEqual(content.x + content.width - 1);
            }
            await compact(page);
            await expect(action(page, 'save')).toBeVisible();
            await expect(action(page, 'delete')).toBeHidden();
            await noOverflow(page);
            expect(errors).toEqual([]);
        });
    }

    test(`${position} preserves full-width mobile actions`, async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 1000 });
        const errors = await openHeader(page, { position, simple: '1' });
        const details = await page.locator('.fph-details').boundingBox();
        const toolbar = await page.locator('.fph-actions').boundingBox();
        expect(toolbar.y).toBeGreaterThanOrEqual(details.y + details.height - 1);
        expect((await action(page, 'save').boundingBox()).width).toBeCloseTo(toolbar.width, 0);
        await compact(page);
        await expect(action(page, 'delete')).toBeHidden();
        await noOverflow(page);
        expect(errors).toEqual([]);
    });
}

for (const mode of ['normal', 'sticky']) {
    test(`${mode} never applies compact action selection`, async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 1000 });
        await openHeader(page, { mode, selection: 'none', simple: '1' });
        await page.evaluate(() => window.scrollTo(0, 700));
        await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'false');
        await expect(action(page, 'save')).toBeVisible();
        await expect(action(page, 'delete')).toBeVisible();
    });
}

for (const teleport of ['0', '1']) {
    for (const selection of ['include', 'exclude']) {
        test(`${selection} keeps native groups intact with teleport=${teleport}`, async ({ page }) => {
            await page.setViewportSize({ width: 1440, height: 1000 });
            const errors = await openHeader(page, { teleport, selection });
            await expect(action(page, 'hidden')).toHaveCount(0);
            await expect(action(page, 'denied')).toHaveCount(0);
            await expect(action(page, 'disabled')).toBeDisabled();
            await page.getByRole('textbox', { name: 'Unsaved note', exact: true }).fill('Pending order changes');
            const contentTop = await page.locator('[data-demo-content]').evaluate(el => el.getBoundingClientRect().top + window.scrollY);
            await page.evaluate(() => window.originalSaveAction = document.querySelector('[data-fph-header-action="save"]'));
            const requests = [];
            page.on('request', request => { if (request.method() !== 'GET') requests.push(request.url()); });
            await compact(page);
            await expect(action(page, 'delete')).toBeHidden();
            await expect(action(page, 'ungrouped')).toBeHidden();
            await expect(page.locator('.fph-actions > .fi-ac > .fi-btn-group')).toBeHidden();
            await expect(action(page, 'disabled')).toBeDisabled();
            const compactScrollTop = await page.evaluate(() => window.scrollY);
            await clickAtCurrentPosition(page, page.getByRole('button', { name: 'More', exact: true }));
            await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-compact', 'true');
            expect(await page.evaluate(() => window.scrollY)).toBeCloseTo(compactScrollTop, 0);
            await expect(action(page, 'approve')).toBeVisible();
            await expect(action(page, 'duplicate')).toBeHidden();
            await expect(action(page, 'duplicate').locator('..')).toHaveAttribute('data-fph-action-hidden', 'true');
            // Native dropdownTeleport() uses fixed positioning, not DOM reparenting.
            const panel = action(page, 'approve').locator('xpath=ancestor::*[contains(concat(" ", normalize-space(@class), " "), " fi-dropdown-panel ")][1]');
            await expect(panel).toHaveCSS('position', teleport === '1' ? 'fixed' : 'absolute');
            await page.getByRole('button', { name: 'Nested', exact: true }).click();
            await expect(action(page, 'archive')).toBeVisible();
            await expect(action(page, 'archive')).toHaveCount(1);
            expect(await page.evaluate(() => window.originalSaveAction === document.querySelector('[data-fph-header-action="save"]'))).toBe(true);
            expect(await page.locator('[data-demo-content]').evaluate(el => el.getBoundingClientRect().top + window.scrollY)).toBeCloseTo(contentTop, 0);
            await expect(page.getByRole('textbox', { name: 'Unsaved note', exact: true })).toHaveValue('Pending order changes');
            await noOverflow(page);
            expect(requests).toEqual([]);
            expect(errors).toEqual([]);
        });
    }
}

for (const hooks of ['0', '1']) {
    test(`empty selection removes empty controls but preserves hooks=${hooks}`, async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 1000 });
        await openHeader(page, { selection: 'none', action_hooks: hooks });
        await compact(page);
        await expect(page.locator('.fph-actions > .fi-ac')).toBeHidden();
        if (hooks === '1') {
            await expect(page.locator('[data-action-test-hook]')).toHaveCount(1);
            await expect(page.locator('[data-action-test-hook]')).toBeVisible();
            await expect(page.locator('.fph-actions')).toBeVisible();
        } else {
            await expect(page.locator('.fph-actions')).toBeHidden();
        }
        await page.evaluate(() => window.scrollTo(0, 0));
        await expect(action(page, 'delete')).toBeVisible();
    });
}

test('focused actions remain available until focus moves to another native control', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await openHeader(page, { selection: 'save', simple: '1' });
    await action(page, 'delete').focus();
    await compact(page);
    await expect(action(page, 'delete')).toBeFocused();
    await expect(action(page, 'delete')).toBeVisible();
    await page.keyboard.press('Shift+Tab');
    await expect(action(page, 'save')).toBeFocused();
    await expect(action(page, 'delete')).toBeHidden();
});

test('a teleported menu already open at the transition remains usable until closed', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await openHeader(page, { teleport: '1' });
    await page.getByRole('button', { name: 'More', exact: true }).click();
    await expect(action(page, 'duplicate')).toBeVisible();
    await compact(page);
    await expect(action(page, 'duplicate')).toBeVisible();
    await page.keyboard.press('Escape');
    await clickAtCurrentPosition(page, page.getByRole('button', { name: 'More', exact: true }));
    await expect(action(page, 'approve')).toBeVisible();
    await expect(action(page, 'duplicate')).toBeHidden();
});

test('native action forms and page form submissions retain their state and handlers', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    const errors = await openHeader(page, { selection: 'save' });
    const note = page.getByRole('textbox', { name: 'Unsaved note', exact: true });
    await note.fill('Unsaved form draft');
    await action(page, 'form').click();
    const dialog = page.getByRole('dialog', { name: 'Edit reason', exact: true });
    const modalWindow = dialog.locator('.fi-modal-window');
    await expect(modalWindow).toBeVisible();
    await expect(dialog).toHaveAttribute('aria-modal', 'true');
    const reason = dialog.getByRole('textbox', { name: /Reason/ });
    await reason.fill('Reviewed');
    await compact(page);
    await expect(modalWindow).toBeVisible();
    await expect(reason).toHaveValue('Reviewed');
    await expect(action(page, 'form')).toBeHidden();
    await dialog.getByRole('button', { name: 'Apply reason', exact: true }).click();
    await expect(modalWindow).toBeHidden();
    await expect(page.locator('.fph-badges')).toContainText('Reviewed');
    await expect(note).toHaveValue('Unsaved form draft');
    await action(page, 'save').click();
    await expect(page.locator('[data-save-count]')).toHaveText('Saved 1 times');
    expect(errors).toEqual([]);
});

test('Livewire visibility changes remove newly empty native dropdowns', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    await openHeader(page, { selection: 'approve', teleport: '1' });
    await compact(page);
    await clickAtCurrentPosition(page, page.getByRole('button', { name: 'More', exact: true }));
    await action(page, 'approve').click();
    const dialog = page.getByRole('alertdialog', { name: 'Approve', exact: true });
    await expect(dialog.locator('.fi-modal-window')).toBeVisible();
    await dialog.getByRole('button', { name: 'Approve order', exact: true }).click();
    await expect(dialog.locator('.fi-modal-window')).toBeHidden();
    await expect(action(page, 'approve')).toHaveCount(0);
    await page.evaluate(() => document.activeElement?.blur());
    await compact(page);
    await expect(page.locator('.fph-actions')).toBeHidden();
});

test('SPA navigation cleans up action presentation and initializes exactly one new header', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1000 });
    const errors = await openHeader(page, { teleport: '1' });
    await compact(page);
    await clickAtCurrentPosition(page, page.getByRole('button', { name: 'More', exact: true }));
    await page.locator('[data-demo-content]').getByRole('link', { name: 'Native page', exact: true }).evaluate(el => el.click());
    await expect(page).toHaveURL(/\/demo\/native$/);
    await expect(page.locator('[data-fph-root]')).toHaveCount(0);
    await expect(page.locator('[data-fph-action-hidden]')).toHaveCount(0);
    await page.goBack();
    await expect(page.locator('[data-fph-root]')).toHaveCount(1);
    await expect(page.locator('[data-fph-root]')).toHaveAttribute('data-fph-ready', 'true');
    await expect(action(page, 'save')).toHaveCount(1);
    expect(errors).toEqual([]);
});
