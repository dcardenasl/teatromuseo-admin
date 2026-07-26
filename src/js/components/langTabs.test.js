import { beforeEach, describe, expect, it, vi } from 'vitest';
import { langTabs, copyLangTabsMediaReferenceFieldToAll } from './langTabs.js';

class FakeInput {
    constructor(value = '') {
        this.value = value;
        this.name = '';
        this.events = [];
    }

    dispatchEvent(event) {
        this.events.push(event.type);
    }
}

globalThis.HTMLInputElement = FakeInput;
globalThis.HTMLTextAreaElement = FakeInput;
// Distinct from FakeInput on purpose: `findMediaReferenceFieldComponent` guards
// its `.closest()` call behind `instanceof HTMLElement`, so this only needs to
// exist (not throw) — FakeInput deliberately isn't one, which routes the
// fallback straight to the plain-input branch these tests exercise.
globalThis.HTMLElement = class {};
globalThis.confirm = () => false;
globalThis.window = globalThis;

describe('langTabs copyDefaultToAll', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
    });

    it('delegates to the shared translationCopy utility (full coverage lives in translationCopy.test.js)', () => {
        const source = new FakeInput('Base');
        const target = new FakeInput('Existing');
        globalThis.document = {
            querySelectorAll: vi.fn((selector) => (selector === '#source' ? [source] : [target])),
        };
        vi.spyOn(globalThis, 'confirm').mockReturnValue(true);

        const result = langTabs().copyDefaultToAll([{ source: '#source', targets: ['#target'] }], 'Confirmar');

        expect(result).toBe(true);
        expect(target.value).toBe('Base');
    });
});

describe('copyLangTabsMediaReferenceFieldToAll', () => {
    const inputNamed = (name) => {
        const input = new FakeInput('');
        input.name = name;
        return input;
    };

    it('matches block-nested field names (translations[N][block_data][fieldKey][...])', () => {
        const sourceKind = inputNamed('translations[1][block_data][image][source_kind]');
        const fileId = inputNamed('translations[1][block_data][image][file_id]');
        const url = inputNamed('translations[1][block_data][image][url]');
        globalThis.document = { querySelectorAll: vi.fn(() => [sourceKind, fileId, url]) };

        const result = copyLangTabsMediaReferenceFieldToAll('image', {
            source_kind: 'hub_file',
            file_id: '42',
            url: '/files/42/view',
        });

        expect(result).toBe(true);
        expect(sourceKind.value).toBe('hub_file');
        expect(fileId.value).toBe('42');
        expect(url.value).toBe('/files/42/view');
    });

    it('matches entry/page-level field names (translations[N][fieldKey][...]), not just block_data', () => {
        const sourceKind = inputNamed('translations[2][featured_image][source_kind]');
        const fileId = inputNamed('translations[2][featured_image][file_id]');
        const url = inputNamed('translations[2][featured_image][url]');
        globalThis.document = { querySelectorAll: vi.fn(() => [sourceKind, fileId, url]) };

        const result = copyLangTabsMediaReferenceFieldToAll('featured_image', {
            source_kind: 'external_url',
            url: 'https://example.com/photo.jpg',
        });

        expect(result).toBe(true);
        expect(sourceKind.value).toBe('external_url');
        expect(fileId.value).toBe('');
        expect(url.value).toBe('https://example.com/photo.jpg');
    });

    it('returns false and touches nothing when the field key is blank', () => {
        globalThis.document = { querySelectorAll: vi.fn(() => []) };

        const result = copyLangTabsMediaReferenceFieldToAll('', { url: 'https://example.com' });

        expect(result).toBe(false);
    });
});
