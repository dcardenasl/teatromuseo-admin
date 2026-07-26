import { describe, expect, it } from 'vitest';

globalThis.window = globalThis;
globalThis.document = { documentElement: { dataset: {} } };

const { remoteTableFactory } = await import('./remoteTable.js');
const table = remoteTableFactory({ apiUrl: '/data', pageUrl: '/list' });

describe('remoteTable.translationStatus', () => {
    it('treats a real translation row for the default language as authoritative (Pages/Collections/Menus/Forms have no canonical title field)', () => {
        const row = { translations: [{ language_id: 1, title: 'Inicio', slug: 'inicio' }] };

        expect(table.translationStatus(row, 1, ['title', 'slug'], true)).toBe('complete');
    });

    it('falls back to row fields only when no translation row exists at all for the default language', () => {
        const row = { title: 'Inicio', slug: 'inicio', translations: [] };

        expect(table.translationStatus(row, 1, ['title', 'slug'], true)).toBe('complete');
    });

    it('fills a blank field in the default-language row from the canonical row field (Category/Tag denormalization)', () => {
        const row = { slug: 'inicio', translations: [{ language_id: 1, title: 'Inicio', slug: '' }] };

        expect(table.translationStatus(row, 1, ['title', 'slug'], true)).toBe('complete');
    });

    it('never treats a non-default language as complete just because canonical row fields are populated', () => {
        const row = { title: 'Inicio', slug: 'inicio', translations: [] };

        expect(table.translationStatus(row, 2, ['title', 'slug'], false)).toBe('missing');
    });

    it('reports incomplete for a secondary language with a partially filled row', () => {
        const row = { translations: [{ language_id: 2, title: 'Start', slug: '' }] };

        expect(table.translationStatus(row, 2, ['title', 'slug'], false)).toBe('incomplete');
    });
});
