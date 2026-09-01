import { describe, expect, it } from 'vitest';
import { cacheElapsed } from './cacheElapsed.js';
import { collectionBlockTemplateBuilder } from './collectionBlockTemplateBuilder.js';
import { listingProjectionEditor } from './listingProjectionEditor.js';

describe('extracted Alpine components', () => {
    it('keeps the cache timer idle when no timestamp is available', () => {
        const cache = cacheElapsed('', { calculating: 'Calculando...' });

        cache.start();

        expect(cache.label).toBe('');
    });

    it('serializes a collection template using the extracted builder', () => {
        const builder = collectionBlockTemplateBuilder([], null, [], null, {});

        builder.init();

        expect(JSON.parse(builder.json)).toEqual({ version: '1.0', blocks: [] });
        expect(builder.valid).toBe(false);
    });

    it('normalizes a listing projection and serializes it consistently', () => {
        const editor = listingProjectionEditor(
            { event_items: [{ value: 'entry.title', type: 'text' }] },
            { version: 2, order: { direction: 'invalid' } },
            'event_items',
        );

        editor.init();

        expect(editor.projection.version).toBe(2);
        expect(editor.projection.order.direction).toBe('desc');
        expect(JSON.parse(editor.serialized()).version).toBe(2);
    });
});
