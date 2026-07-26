import { describe, expect, it } from 'vitest';
import { resolveFilePreviewUrl, resolveTranslatableFilePreviewUrl } from './fileUrl.js';
import { mediaReferenceField } from '../components/mediaReferenceField.js';
import { translatableFileField } from '../components/translatableFileField.js';

describe('fileUrl helpers', () => {
    it('uses the provided URL when present', () => {
        expect(resolveFilePreviewUrl(42, 'https://example.com/file.jpg')).toBe('https://example.com/file.jpg');
    });

    it('falls back to the library view URL when only the file ID is known', () => {
        expect(resolveFilePreviewUrl(42, '')).toBe('/files/42/view');
    });

    it('keeps translatable file previews aligned with the shared helper', () => {
        expect(resolveTranslatableFilePreviewUrl(42, '')).toBe('/files/42/view');
    });
});

describe('media reference fields', () => {
    it('derives a preview URL from a hub file id even when the URL is empty', () => {
        expect(mediaReferenceField({ source_kind: 'hub_file', file_id: 42 }).previewUrl).toBe('/files/42/view');
    });

    it('derives a preview URL for translated file fields from a hub file id', () => {
        expect(translatableFileField('42', '').previewUrl).toBe('/files/42/view');
    });

    it('owns its picker labels without relying on a parent Alpine scope', () => {
        const emptyDocument = mediaReferenceField({}, 'document');
        expect(emptyDocument.pickerButtonLabel()).toBe('Seleccionar documento');

        const selectedDocument = mediaReferenceField({ source_kind: 'hub_file', file_id: 42 }, 'document');
        expect(selectedDocument.pickerButtonLabel()).toBe('Cambiar documento');
    });

    it('preserves cached external and hub references when toggling between sources', () => {
        const field = mediaReferenceField({
            source_kind: 'external_url',
            url: 'https://example.com/external.jpg',
            preview_url: 'https://example.com/external.jpg',
        });

        field.setSourceKind('hub_file');
        expect(field.sourceKind).toBe('hub_file');
        expect(field.fileId).toBe('');
        expect(field.url).toBe('https://example.com/external.jpg');
        expect(field.previewUrl).toBe('https://example.com/external.jpg');

        field.applyReference({
            source_kind: 'hub_file',
            file_id: '99',
            url: '/files/99/original.jpg',
            preview_url: '/files/99/view',
        });

        field.setSourceKind('external_url');
        expect(field.sourceKind).toBe('external_url');
        expect(field.url).toBe('https://example.com/external.jpg');
        expect(field.previewUrl).toBe('https://example.com/external.jpg');

        field.setSourceKind('hub_file');
        expect(field.sourceKind).toBe('hub_file');
        expect(field.fileId).toBe('99');
        expect(field.url).toBe('/files/99/original.jpg');
        expect(field.previewUrl).toBe('/files/99/view');
    });

    it('keeps the visible URL and preview when switching from library to external URL', () => {
        const field = mediaReferenceField({
            source_kind: 'hub_file',
            file_id: '42',
            url: '/files/42/view',
            preview_url: '/files/42/view',
        });

        field.setSourceKind('external_url');

        expect(field.sourceKind).toBe('external_url');
        expect(field.fileId).toBe('');
        expect(field.url).toBe('/files/42/view');
        expect(field.previewUrl).toBe('/files/42/view');
    });

    it('keeps a typed external URL cached after syncing the field', () => {
        const field = mediaReferenceField({});

        field.setSourceKind('external_url');
        field.url = 'https://example.com/typed.jpg';
        field.syncExternalUrl();

        field.setSourceKind('hub_file');
        field.setSourceKind('external_url');

        expect(field.url).toBe('https://example.com/typed.jpg');
        expect(field.previewUrl).toBe('https://example.com/typed.jpg');
    });
});
