import { beforeEach, describe, expect, it } from 'vitest';

globalThis.window = globalThis;
globalThis.document = { documentElement: { dataset: {} } };
const sessionValues = new Map();
globalThis.sessionStorage = {
    getItem(key) { return sessionValues.get(key) ?? null; },
    setItem(key, value) { sessionValues.set(key, String(value)); },
    removeItem(key) { sessionValues.delete(key); },
    clear() { sessionValues.clear(); },
};

const { remoteTableFactory } = await import('./remoteTable.js');
const createTable = (config = {}) => remoteTableFactory({
    apiUrl: '/data',
    pageUrl: '/list',
    ...config,
});

beforeEach(() => sessionStorage.clear());

describe('remoteTable.translationStatus', () => {
    const table = createTable();

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

describe('remoteTable.viewPreferences', () => {
    it('uses table and medium density defaults without writing storage', () => {
        const table = createTable({ mode: 'users' });

        expect(table.viewMode).toBe('table');
        expect(table.density).toBe('md');
        expect(sessionStorage.getItem('admin_table_view_users')).toBeNull();
        expect(sessionStorage.getItem('admin_table_density_users')).toBeNull();
    });

    it('persists view mode and density under the module-specific keys', () => {
        const table = createTable({ mode: 'users' });

        table.setViewMode('grid');
        table.setDensity('lg');

        expect(table.viewMode).toBe('grid');
        expect(table.density).toBe('lg');
        expect(sessionStorage.getItem('admin_table_view_users')).toBe('grid');
        expect(sessionStorage.getItem('admin_table_density_users')).toBe('lg');
    });

    it('restores preferences only for the same module', () => {
        sessionStorage.setItem('admin_table_view_users', 'grid');
        sessionStorage.setItem('admin_table_density_users', 'sm');

        const usersTable = createTable({ mode: 'users' });
        const auditTable = createTable({ mode: 'audit' });

        expect(usersTable.viewMode).toBe('grid');
        expect(usersTable.density).toBe('sm');
        expect(auditTable.viewMode).toBe('table');
        expect(auditTable.density).toBe('md');
    });

    it('ignores unsupported stored values and unsupported updates', () => {
        sessionStorage.setItem('admin_table_view_users', 'list');
        sessionStorage.setItem('admin_table_density_users', 'xl');
        const table = createTable({ mode: 'users' });

        table.setViewMode('list');
        table.setDensity('xl');

        expect(table.viewMode).toBe('table');
        expect(table.density).toBe('md');
        expect(sessionStorage.getItem('admin_table_view_users')).toBe('list');
        expect(sessionStorage.getItem('admin_table_density_users')).toBe('xl');
    });
});
