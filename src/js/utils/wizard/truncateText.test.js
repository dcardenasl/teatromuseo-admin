import { describe, it, expect } from 'vitest';
import { truncateText } from './truncateText.js';

describe('truncateText', () => {
    it('returns the text unchanged when shorter than the limit', () => {
        expect(truncateText('hello', 10)).toBe('hello');
    });

    it('returns the text unchanged when exactly at the limit', () => {
        expect(truncateText('hello', 5)).toBe('hello');
    });

    it('truncates and trims trailing whitespace when over the limit', () => {
        expect(truncateText('hello world', 7)).toBe('hello w');
        expect(truncateText('hello  world', 6)).toBe('hello');
    });

    it('trims the input before measuring length', () => {
        expect(truncateText('  hello  ', 10)).toBe('hello');
    });

    it('returns an empty string for whitespace-only or nullish input', () => {
        expect(truncateText('   ', 10)).toBe('');
        expect(truncateText(null, 10)).toBe('');
        expect(truncateText(undefined, 10)).toBe('');
    });
});
