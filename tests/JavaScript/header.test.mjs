import { test } from 'node:test';
import assert from 'node:assert/strict';
import { resolveMode, resolveOffset, shouldStick } from '../../resources/js/page-header.js';

test('normal is the default mode', () => assert.equal(resolveMode({}, 1200), 'normal'));
test('the last matching minimum width wins', () => {
  const options = { mode: 'compact', breakpoints: [{ minWidth: 768, mode: 'sticky' }, { minWidth: 1280, mode: 'normal' }] };
  assert.equal(resolveMode(options, 390), 'compact');
  assert.equal(resolveMode(options, 768), 'sticky');
  assert.equal(resolveMode(options, 1500), 'normal');
});
test('zero explicitly disables the automatic offset', () => {
  assert.equal(resolveOffset({ offset: 0 }, [64], 0), 0);
  assert.equal(resolveOffset({ offset: null }, [64, 64], 0), 64);
});
test('automatic offset is relative to the scroll container', () => {
  assert.equal(resolveOffset({}, [80], 30), 50);
  assert.equal(resolveOffset({}, [20], 30), 0);
});
test('sticky does not activate before scrolling', () => {
  assert.equal(shouldStick(64, 64, 0, 'compact'), false);
  assert.equal(shouldStick(64, 64, 1, 'compact'), true);
  assert.equal(shouldStick(70, 64, 100, 'sticky'), false);
  assert.equal(shouldStick(64, 64, 100, 'normal'), false);
});


test('compact below uses an exclusive threshold and preserves the desktop mode', () => {
    const options = { mode: 'sticky', compactBelow: 1024 };
    assert.equal(resolveMode(options, 1023), 'compact');
    assert.equal(resolveMode(options, 1024), 'sticky');
    assert.equal(resolveMode(options, 1440), 'sticky');
});
