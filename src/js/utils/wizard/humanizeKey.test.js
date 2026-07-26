import { describe, it, expect } from 'vitest';
import { humanizeKey } from './humanizeKey.js';

describe('humanizeKey', () => {
    it('replaces underscores with spaces and title-cases', () => {
        expect(humanizeKey('featured_image')).toBe('Featured Image');
    });

    it('splits camelCase boundaries', () => {
        expect(humanizeKey('featuredImage')).toBe('Featured Image');
    });

    it('handles mixed snake_case and camelCase', () => {
        expect(humanizeKey('block_typeKey')).toBe('Block Type Key');
    });

    it('returns an empty string for falsy input', () => {
        expect(humanizeKey('')).toBe('');
        expect(humanizeKey(null)).toBe('');
        expect(humanizeKey(undefined)).toBe('');
    });

    it('title-cases a single word', () => {
        expect(humanizeKey('title')).toBe('Title');
    });
});
