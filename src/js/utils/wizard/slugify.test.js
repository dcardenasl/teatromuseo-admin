import { describe, it, expect } from 'vitest';
import { wizardSlugify } from './slugify.js';

describe('wizardSlugify', () => {
    it('lowercases and hyphenates spaces', () => {
        expect(wizardSlugify('Hello World')).toBe('hello-world');
    });

    it('strips accents', () => {
        expect(wizardSlugify('Café Núñez')).toBe('cafe-nunez');
    });

    it('collapses multiple spaces and hyphens', () => {
        expect(wizardSlugify('a   b--c')).toBe('a-b-c');
    });

    it('truncates at the default 100-char limit (index.php behavior)', () => {
        const input = 'a'.repeat(150);
        expect(wizardSlugify(input)).toHaveLength(100);
    });

    it('truncates at a custom limit (structure.php passes 50)', () => {
        const input = 'a'.repeat(80);
        expect(wizardSlugify(input, 50)).toHaveLength(50);
    });

    it('returns an empty string for null/undefined input', () => {
        expect(wizardSlugify(null)).toBe('');
        expect(wizardSlugify(undefined)).toBe('');
    });

    it('strips characters outside [a-z0-9\\s-]', () => {
        expect(wizardSlugify('Título: 100% Épico!')).toBe('titulo-100-epico');
    });
});
