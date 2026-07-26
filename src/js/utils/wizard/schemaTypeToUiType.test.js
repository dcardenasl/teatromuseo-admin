import { describe, it, expect } from 'vitest';
import { schemaTypeToUiType } from './schemaTypeToUiType.js';

describe('schemaTypeToUiType', () => {
    it('returns the explicit primitive override when present', () => {
        expect(schemaTypeToUiType('string', '', 'richtext')).toBe('richtext');
    });

    it('maps file + image accept to image', () => {
        expect(schemaTypeToUiType('file', 'image')).toBe('image');
        expect(schemaTypeToUiType('file', 'image/*')).toBe('image');
        expect(schemaTypeToUiType('file', 'image/png')).toBe('image');
    });

    it('maps file without image accept to file', () => {
        expect(schemaTypeToUiType('file', 'application/pdf')).toBe('file');
        expect(schemaTypeToUiType('file', '')).toBe('file');
    });

    it('maps image schema type directly to image', () => {
        expect(schemaTypeToUiType('image')).toBe('image');
    });

    it('maps media_reference schema type directly to media_reference', () => {
        expect(schemaTypeToUiType('media_reference')).toBe('media_reference');
    });

    it('maps richtext variants', () => {
        expect(schemaTypeToUiType('richtext')).toBe('richtext');
        expect(schemaTypeToUiType('rich_text')).toBe('richtext');
    });

    it('maps string to text and text to textarea', () => {
        expect(schemaTypeToUiType('string')).toBe('text');
        expect(schemaTypeToUiType('text')).toBe('textarea');
    });

    it('maps numeric variants to number', () => {
        expect(schemaTypeToUiType('number')).toBe('number');
        expect(schemaTypeToUiType('integer')).toBe('number');
        expect(schemaTypeToUiType('int')).toBe('number');
    });

    it('maps boolean variants to boolean', () => {
        expect(schemaTypeToUiType('boolean')).toBe('boolean');
        expect(schemaTypeToUiType('bool')).toBe('boolean');
    });

    it('passes date/datetime through unchanged', () => {
        expect(schemaTypeToUiType('date')).toBe('date');
        expect(schemaTypeToUiType('datetime')).toBe('datetime');
    });

    it('maps url and select directly', () => {
        expect(schemaTypeToUiType('url')).toBe('url');
        expect(schemaTypeToUiType('select')).toBe('select');
    });

    it('falls back to unsupported for unknown types', () => {
        expect(schemaTypeToUiType('mystery')).toBe('unsupported');
        expect(schemaTypeToUiType('')).toBe('unsupported');
    });
});
