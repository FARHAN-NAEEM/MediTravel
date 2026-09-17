import { test } from 'node:test';
import assert from 'node:assert/strict';
import heroSlider from '../../resources/js/hero-slider.js';

function setup(t, count = 3, reducedMotion = false) {
    let tick;
    let delay;
    const media = { matches: reducedMotion, addEventListener() {}, removeEventListener() {} };
    t.mock.method(globalThis, 'setInterval', (callback, interval) => { tick = callback; delay = interval; return 1; });
    t.mock.method(globalThis, 'clearInterval', () => {});
    globalThis.document = { hidden: false };
    globalThis.window = { matchMedia: () => media };
    t.after(() => { delete globalThis.document; delete globalThis.window; });
    const slider = heroSlider(count, 3000);
    slider.init();
    return { slider, tick: () => tick?.(), delay: () => delay };
}

test('autoplay wraps, continues after navigation and waits for hidden tabs', (t) => {
    const { slider, tick, delay } = setup(t);
    assert.equal(delay(), 3000);
    tick(); assert.equal(slider.active, 1);
    tick(); tick(); assert.equal(slider.active, 0);
    for (const state of ['hidden', 'touch']) {
        slider[state] = true;
        tick(); assert.equal(slider.active, 0);
        slider[state] = false;
    }
    slider.go(-1); assert.equal(slider.active, 2);
    slider.go(3); assert.equal(slider.active, 0);
    tick(); assert.equal(slider.active, 1);
    slider.destroy();
});

test('swipe navigates horizontally but does not consume vertical scrolling', (t) => {
    const { slider } = setup(t);
    slider.touchStart({ touches: [{ clientX: 200, clientY: 100 }] });
    slider.touchEnd({ changedTouches: [{ clientX: 100, clientY: 105 }] });
    assert.equal(slider.active, 1);
    slider.touchStart({ touches: [{ clientX: 200, clientY: 100 }] });
    slider.touchEnd({ changedTouches: [{ clientX: 100, clientY: 300 }] });
    assert.equal(slider.active, 1);
    assert.equal(slider.touch, null);
});

test('reduced motion starts paused and zero or one slide never auto-advance', (t) => {
    const { slider, tick } = setup(t, 3, true);
    tick(); assert.equal(slider.active, 0);
    slider.motionListener({ matches: false }); tick(); assert.equal(slider.active, 1);
    for (const count of [0, 1]) {
        const still = heroSlider(count, 5000);
        still.init();
        assert.equal(still.timer, null);
        still.go(1);
        assert.equal(still.active, 0);
    }
});

test('indicator window stays bounded for large slide collections', () => {
    const slider = heroSlider(20, 5000);
    for (const active of [0, 1, 7, 18, 19]) {
        slider.active = active;
        assert.ok(slider.indicatorStart >= 0);
        assert.ok(slider.indicatorStart + 5 <= slider.count);
        assert.ok(active >= slider.indicatorStart && active < slider.indicatorStart + 5);
    }
});
