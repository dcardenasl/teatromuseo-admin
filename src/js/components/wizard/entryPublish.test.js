import { describe, it, expect } from 'vitest';
import { entryPublish } from './entryPublish.js';

describe('entryPublish.isBlockFieldFilled', () => {
    it('treats an "image" field as filled only when a file id is present', () => {
        const field = { key: 'cover', uiType: 'image' };
        expect(entryPublish.isBlockFieldFilled({}, field)).toBe(false);
        expect(entryPublish.isBlockFieldFilled({ cover_file_id: null }, field)).toBe(false);
        expect(entryPublish.isBlockFieldFilled({ cover_file_id: 7 }, field)).toBe(true);
    });

    it('treats a "media_reference" field as unfilled when the value is missing or empty', () => {
        const field = { key: 'image', uiType: 'media_reference' };
        expect(entryPublish.isBlockFieldFilled({}, field)).toBe(false);
        expect(entryPublish.isBlockFieldFilled({ image: null }, field)).toBe(false);
        expect(entryPublish.isBlockFieldFilled({ image: { source_kind: 'hub_file', file_id: null, url: null } }, field)).toBe(false);
    });

    it('treats a "media_reference" field as filled once a file_id or url is set', () => {
        const field = { key: 'image', uiType: 'media_reference' };
        expect(entryPublish.isBlockFieldFilled({ image: { source_kind: 'hub_file', file_id: 42, url: '/files/42/view' } }, field)).toBe(true);
        expect(entryPublish.isBlockFieldFilled({ image: { source_kind: 'external_url', file_id: null, url: 'https://cdn.example.com/a.jpg' } }, field)).toBe(true);
    });

    it('falls back to string-truthiness for other field types', () => {
        const field = { key: 'title', uiType: 'text' };
        expect(entryPublish.isBlockFieldFilled({}, field)).toBe(false);
        expect(entryPublish.isBlockFieldFilled({ title: '' }, field)).toBe(false);
        expect(entryPublish.isBlockFieldFilled({ title: 'Hello' }, field)).toBe(true);
    });
});
