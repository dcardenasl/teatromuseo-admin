import { beforeEach, describe, expect, it, vi } from 'vitest';
import { copyDefaultToAll, copyFieldToAll } from './translationCopy.js';

class FakeField {
    constructor({ value = '', disabled = false, visible = true, checked = false, type = 'text' } = {}) {
        this.value = value;
        this.disabled = disabled;
        this.offsetParent = visible ? {} : null;
        this.checked = checked;
        this.type = type;
        this.events = [];
    }

    dispatchEvent(event) {
        this.events.push(event.type);
    }
}

class FakeSelect extends FakeField {}

globalThis.HTMLInputElement = FakeField;
globalThis.HTMLTextAreaElement = FakeField;
globalThis.HTMLSelectElement = FakeSelect;
globalThis.confirm = () => true;
globalThis.window = globalThis;

describe('copyFieldToAll', () => {
    it('skips disabled targets so hidden sibling widgets are not overwritten', () => {
        const source = new FakeField({ value: 'Base' });
        const enabledTarget = new FakeField({ value: 'Old', disabled: false });
        const disabledTarget = new FakeField({ value: 'Old', disabled: true });
        globalThis.document = {
            querySelectorAll: vi.fn((selector) => (selector === '#source' ? [source] : [enabledTarget, disabledTarget])),
        };

        copyFieldToAll('#source', ['[name="translations[1]"]']);

        expect(enabledTarget.value).toBe('Base');
        expect(disabledTarget.value).toBe('Old');
    });

    it('writes to a single hidden match (langTabs: an inactive-language tab is still a valid target)', () => {
        // Regression guard: pages/entries/categories/tags/collections/forms/menus
        // give every language its own uniquely-named field (translations[1][name]),
        // hidden via x-show while its tab isn't selected. That single match must
        // still receive the copy — visibility only matters when a selector
        // resolves to *several* sibling widgets sharing one name (Settings).
        const source = new FakeField({ value: 'Base', visible: true });
        const hiddenTabTarget = new FakeField({ value: 'Old', visible: false, disabled: false });
        globalThis.document = {
            querySelectorAll: vi.fn((selector) => (selector === '#source' ? [source] : [hiddenTabTarget])),
        };

        copyFieldToAll('#source', ['[name="translations[1][name]"]']);

        expect(hiddenTabTarget.value).toBe('Base');
    });

    it('picks the visible candidate when a selector matches several sibling widgets sharing a name (Settings source field)', () => {
        // Mirrors Settings: string/int/bool/json widgets all share a selector,
        // but only the one for the active setting_type is visible.
        const hiddenString = new FakeField({ value: 'stale', visible: false });
        const visibleInt = new FakeField({ value: '42', visible: true, type: 'number' });
        const hiddenJson = new FakeField({ value: '{}', visible: false });
        const target = new FakeField({ value: 'old', visible: true });
        globalThis.document = {
            querySelectorAll: vi.fn((selector) => (selector === '#source' ? [hiddenString, visibleInt, hiddenJson] : [target])),
        };

        copyFieldToAll('#source', ['#target']);

        expect(target.value).toBe('42');
    });

    it('reads a checkbox source by its checked state, not its raw value', () => {
        const source = new FakeField({ type: 'checkbox', checked: true, value: 'on' });
        const target = new FakeSelect({ value: '0' });
        globalThis.document = {
            // First call resolves the source, second resolves the target.
            querySelectorAll: vi.fn()
                .mockReturnValueOnce([source])
                .mockReturnValueOnce([target]),
        };

        copyFieldToAll('#source', ['#target']);

        expect(target.value).toBe('1');
        expect(target.events).toEqual(['input', 'change']);
    });
});

describe('copyDefaultToAll', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
    });

    it('does not mutate fields when confirmation is rejected', () => {
        const source = new FakeField({ value: 'Base' });
        const target = new FakeField({ value: 'Existing' });
        globalThis.document = { querySelectorAll: vi.fn((selector) => (selector === '#source' ? [source] : [target])) };
        vi.spyOn(globalThis, 'confirm').mockReturnValue(false);

        const result = copyDefaultToAll([{ source: '#source', targets: ['#target'] }], 'Confirmar');

        expect(result).toBe(false);
        expect(target.value).toBe('Existing');
        expect(target.events).toEqual([]);
    });

    it('copies declared fields only after confirmation', () => {
        const source = new FakeField({ value: 'Base' });
        const target = new FakeField({ value: 'Existing' });
        globalThis.document = {
            querySelectorAll: vi.fn()
                .mockReturnValueOnce([source])
                .mockReturnValueOnce([target]),
        };
        vi.spyOn(globalThis, 'confirm').mockReturnValue(true);

        const result = copyDefaultToAll([{ source: '#source', targets: ['#target'] }], 'Confirmar');

        expect(result).toBe(true);
        expect(target.value).toBe('Base');
        expect(target.events).toContain('input');
    });
});
