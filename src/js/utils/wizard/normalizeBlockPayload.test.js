import { describe, it, expect } from 'vitest';
import { normalizeBlockPayload } from './normalizeBlockPayload.js';

describe('normalizeBlockPayload', () => {
    it('sets a missing _file_id key to null when its _url sibling has a value', () => {
        const result = normalizeBlockPayload({ image_url: 'https://example.com/a.jpg' });
        expect(result).toEqual({ image_url: 'https://example.com/a.jpg', image_file_id: null });
    });

    it('leaves an existing _file_id value untouched', () => {
        const result = normalizeBlockPayload({ image_url: 'https://example.com/a.jpg', image_file_id: 42 });
        expect(result.image_file_id).toBe(42);
    });

    it('ignores keys that are not *_url', () => {
        const result = normalizeBlockPayload({ title: 'Hello' });
        expect(result).toEqual({ title: 'Hello' });
    });

    it('ignores empty or non-string _url values', () => {
        const result = normalizeBlockPayload({ image_url: '', video_url: null });
        expect(result).toEqual({ image_url: '', video_url: null });
    });

    it('does not mutate the original object', () => {
        const original = { image_url: 'https://example.com/a.jpg' };
        normalizeBlockPayload(original);
        expect(original).toEqual({ image_url: 'https://example.com/a.jpg' });
    });

    it('handles a null/undefined input gracefully', () => {
        expect(normalizeBlockPayload(null)).toEqual({});
        expect(normalizeBlockPayload(undefined)).toEqual({});
    });
});
