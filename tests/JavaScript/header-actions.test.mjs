import { test } from 'node:test';
import assert from 'node:assert/strict';
import { shouldKeepAction } from '../../resources/js/header-actions.js';

test('expanded mode preserves selected and unselected native actions', () => {
    assert.equal(shouldKeepAction(false, false), true);
    assert.equal(shouldKeepAction(false, true), true);
});

test('compact mode applies its additional selection without hiding an active interaction', () => {
    assert.equal(shouldKeepAction(true, true), true);
    assert.equal(shouldKeepAction(true, false), false);
    assert.equal(shouldKeepAction(true, false, true), true);
});
