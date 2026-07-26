import { describe, it, expect } from 'vitest';
import { validateEntryPayload } from './validateEntryPayload.js';

const STRINGS = {
    error_publish: 'Could not publish',
    error_collection_required: 'Collection is required',
    error_title_required: 'Title is required',
};

describe('validateEntryPayload', () => {
    it('returns a single fallback error for a non-object payload', () => {
        expect(validateEntryPayload(null, STRINGS)).toEqual(['Could not publish']);
        expect(validateEntryPayload('x', STRINGS)).toEqual(['Could not publish']);
    });

    it('flags a missing collection_id', () => {
        const errors = validateEntryPayload({ collection_id: 0, title: 'Hi' }, STRINGS);
        expect(errors).toContain('Collection is required');
    });

    it('flags an empty title', () => {
        const errors = validateEntryPayload({ collection_id: 1, title: '  ' }, STRINGS);
        expect(errors).toContain('Title is required');
    });

    it('passes with a valid minimal payload and no translations', () => {
        expect(validateEntryPayload({ collection_id: 1, title: 'Hi' }, STRINGS)).toEqual([]);
    });

    it('validates each translation entry independently', () => {
        const payload = {
            collection_id: 1,
            title: 'Hi',
            translations: [
                { language_id: 1, title: '', slug: 'ok-slug' },
                { language_id: 2, title: 'Ok', slug: '' },
            ],
        };

        const errors = validateEntryPayload(payload, STRINGS);
        expect(errors).toContain('translation[1].title required');
        expect(errors).toContain('translation[2].slug required');
    });

    it('flags translation title/slug over the max length', () => {
        const payload = {
            collection_id: 1,
            title: 'Hi',
            translations: [{ language_id: 1, title: 'a'.repeat(256), slug: 'a'.repeat(151) }],
        };

        const errors = validateEntryPayload(payload, STRINGS);
        expect(errors).toContain('translation[1].title max_length 255');
        expect(errors).toContain('translation[1].slug max_length 150');
    });

    it('uses the generic "translation" prefix when language_id is missing', () => {
        const payload = { collection_id: 1, title: 'Hi', translations: [{ title: '', slug: '' }] };
        const errors = validateEntryPayload(payload, STRINGS);
        expect(errors).toContain('translation.title required');
        expect(errors).toContain('translation.slug required');
    });
});
