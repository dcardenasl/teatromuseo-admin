import { describe, expect, it } from 'vitest';
import { slugify } from './slug.js';

describe('canonical slugify', () => {
    it('removes accents without introducing separators', () => {
        expect(slugify('Función')).toBe('funcion');
        expect(slugify('Súbete al escenario')).toBe('subete-al-escenario');
    });

    it('normalizes punctuation and repeated separators', () => {
        expect(slugify("Festival d'hiver — 2026!")).toBe('festival-d-hiver-2026');
    });
});
