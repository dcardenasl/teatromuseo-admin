import { describe, expect, it } from 'vitest';
import { fileTypePresentation } from './fileType.js';

describe('fileTypePresentation', () => {
    it('identifies common document formats from their MIME type', () => {
        expect(fileTypePresentation({ mime_type: 'application/pdf', original_name: 'manual.bin' })).toMatchObject({
            kind: 'pdf',
            icon: 'file-text',
            label: 'PDF',
            theme: 'file-type-pdf',
        });

        expect(fileTypePresentation({
            mime_type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            original_name: 'manual.docx',
        })).toMatchObject({ kind: 'word', icon: 'file-text', label: 'DOCX' });

        expect(fileTypePresentation({
            mime_type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            original_name: 'budget.xlsx',
        })).toMatchObject({ kind: 'spreadsheet', icon: 'file-spreadsheet', label: 'XLSX' });
    });

    it('falls back to the extension when MIME metadata is absent', () => {
        expect(fileTypePresentation({ original_name: 'slides.pptx' })).toMatchObject({
            kind: 'presentation',
            icon: 'presentation',
            label: 'PPTX',
        });
        expect(fileTypePresentation({ original_name: 'backup.zip' })).toMatchObject({ kind: 'archive', label: 'ZIP' });
    });

    it('keeps unknown uploads identifiable without trusting their extension as markup', () => {
        expect(fileTypePresentation({ original_name: 'notes.customformat' })).toMatchObject({
            kind: 'generic',
            icon: 'file-type',
            label: 'CUSTOMFORMAT',
            theme: 'file-type-generic',
        });
    });

    it('uses category and MIME families for incomplete legacy records', () => {
        expect(fileTypePresentation({ category: 'audio', mime_type: 'audio/mpeg' })).toMatchObject({ kind: 'audio', label: 'MP3' });
        expect(fileTypePresentation({ category: 'video', mime_type: 'video/mp4' })).toMatchObject({ kind: 'video', label: 'MP4' });
        expect(fileTypePresentation({ category: 'document', mime_type: '' })).toMatchObject({ kind: 'document', label: 'DOCUMENT' });
        expect(fileTypePresentation({ category: 'image', mime_type: '' })).toMatchObject({ kind: 'image', label: 'IMAGE' });
    });
});
