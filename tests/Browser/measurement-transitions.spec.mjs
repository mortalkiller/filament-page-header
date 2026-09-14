import { test, expect } from '@playwright/test';

// Observe beyond the 150ms transition so a delayed restart cannot escape the assertion.
async function expectNoHeaderTransitions(page) {
    await page.waitForTimeout(300);
    expect(await page.evaluate(() => window.headerTransitions)).toEqual([]);
}

async function startObservedTransition(page, scrollTop) {
    await page.evaluate(scrollTop => new Promise(resolve => {
        const header = document.querySelector('[data-fph-header]');
        const started = event => {
            if (event.propertyName !== 'box-shadow') return;
            header.removeEventListener('transitionrun', started);
            const transitions = header.getAnimations();
            window.transitionOutcomes = Promise.all(transitions.map(animation =>
                animation.finished.then(() => 'finished', () => 'cancelled')));
            resolve();
        };
        header.addEventListener('transitionrun', started);
        window.scrollTo(0, scrollTop);
    }), scrollTop);
}

test('content and Livewire updates during animation settle with fresh dimensions', async ({ page }) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.setViewportSize({ width: 1440, height: 1400 });
    await page.goto('/demo/headers?variant=11&mode=compact&selective=1');
    const root = page.locator('[data-fph-root]');
    await expect(root).toHaveAttribute('data-fph-ready', 'true');
    // Keep the overlap deterministic without relying on request/network speed.
    await page.addStyleTag({ content: '.fph-root[data-fph-ready="true"] .fph-header { transition-duration: 1s; }' });
    await startObservedTransition(page, 800);
    await root.evaluate(async root => {
        const component = window.Livewire.find(root.closest('[wire\\:id]').getAttribute('wire:id'));
        await component.$refresh();
        root.parentElement.style.width = '900px';
    });
    const outcomes = await page.evaluate(() => window.transitionOutcomes);
    expect(outcomes.length).toBeGreaterThan(0);
    expect(outcomes.every(outcome => outcome === 'finished')).toBe(true);
    await expect(root).toHaveAttribute('data-fph-compact', 'true');
    await startObservedTransition(page, 0);
    expect(await page.evaluate(() => window.transitionOutcomes)).not.toContain('cancelled');
    await expect.poll(() => root.evaluate(root => Math.abs(
        parseFloat(root.style.height) - root.querySelector('[data-fph-header]').getBoundingClientRect().height,
    ))).toBeLessThanOrEqual(1);
    expect(errors).toEqual([]);
});

test('reversals and reduced motion do not leave a pending measurement stuck', async ({ page }) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.setViewportSize({ width: 1440, height: 1400 });
    await page.goto('/demo/headers?variant=11&mode=compact&selective=1');
    const root = page.locator('[data-fph-root]');
    await expect(root).toHaveAttribute('data-fph-ready', 'true');
    await page.addStyleTag({ content: '.fph-root[data-fph-ready="true"] .fph-header { transition-duration: 1s; }' });
    for (const position of [800, 0, 800]) {
        await startObservedTransition(page, position);
    }
    await page.evaluate(() => window.transitionOutcomes);
    await expect(root).toHaveAttribute('data-fph-compact', 'true');
    await expect.poll(() => page.locator('[data-fph-header]').evaluate(header => header.getAnimations().length)).toBe(0);

    await startObservedTransition(page, 0);
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.evaluate(() => window.transitionOutcomes);
    await expect(root).toHaveAttribute('data-fph-compact', 'false');
    await page.setViewportSize({ width: 390, height: 1400 });
    await expect.poll(() => root.evaluate(root => Math.abs(
        parseFloat(root.style.height) - root.querySelector('[data-fph-header]').getBoundingClientRect().height,
    ))).toBeLessThanOrEqual(1);
    expect(await page.locator('[data-fph-header]').evaluate(header => header.getAnimations().length)).toBe(0);
    expect(errors).toEqual([]);
});

async function expectCompletedTransition(page, scrollTop) {
    await page.evaluate(scrollTop => {
        window.headerTransitions = [];
        window.scrollTo(0, scrollTop);
    }, scrollTop);
    await expect.poll(() => page.evaluate(() => window.headerTransitions
        .filter(event => event.type === 'transitionend').map(event => event.property)))
        .toEqual(expect.arrayContaining(['padding-top', 'padding-bottom', 'box-shadow']));
    const events = await page.evaluate(() => window.headerTransitions);
    expect(events.filter(event => event.type === 'transitioncancel')).toEqual([]);
    for (const event of events.filter(event => event.type === 'transitionend')) {
        expect(event.elapsed).toBeCloseTo(0.15, 2);
    }
}

for (const width of [390, 1440]) {
    for (const theme of ['light', 'dark']) {
        test(`measurement stays invisible through loading and Livewire updates in ${theme} at ${width}px`, async ({ page }) => {
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));
            await page.setViewportSize({ width, height: 1400 });
            await page.emulateMedia({ colorScheme: theme, reducedMotion: 'no-preference' });
            await page.addInitScript(theme => {
                localStorage.setItem('theme', theme);
                window.headerTransitions = [];
                for (const type of ['transitionrun', 'transitionend', 'transitioncancel']) {
                    document.addEventListener(type, event => {
                        if (event.target.matches('[data-fph-header]')) {
                            window.headerTransitions.push({ type, property: event.propertyName, elapsed: event.elapsedTime });
                        }
                    }, true);
                }
            }, theme);

            await page.goto('/demo/headers?variant=11&mode=compact&selective=1');
            const root = page.locator('[data-fph-root]');
            await expect(root).toHaveAttribute('data-fph-ready', 'true');
            await page.evaluate(() => document.fonts.ready);
            await expectNoHeaderTransitions(page);

            await page.locator('[data-demo-content]').evaluate(content => {
                content.style.paddingBottom = '150px';
            });
            await expectNoHeaderTransitions(page);

            for (let refresh = 0; refresh < 3; refresh++) {
                await root.evaluate(async root => {
                    const component = window.Livewire.find(root.closest('[wire\\:id]').getAttribute('wire:id'));
                    await component.$refresh();
                });
                await expectNoHeaderTransitions(page);
                await expect(root).toHaveAttribute('data-fph-compact', 'false');
            }

            await expectCompletedTransition(page, 800);
            await expect(root).toHaveAttribute('data-fph-compact', 'true');
            await expectCompletedTransition(page, 0);
            await expect(root).toHaveAttribute('data-fph-compact', 'false');
            expect(errors).toEqual([]);
        });
    }
}
