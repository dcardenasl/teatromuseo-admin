import { resolveTranslatableFilePreviewUrl, bestFileOriginalUrl, bestFilePreviewUrl } from '../utils/fileUrl.js';

export const translatableFileField = (initialId = '', initialUrl = '', accept = 'image') => ({
    fileId: String(initialId),
    fileUrl: resolveTranslatableFilePreviewUrl(initialId, initialUrl),
    previewUrl: resolveTranslatableFilePreviewUrl(initialId, initialUrl),
    accept: String(accept),
    pickerLabels: {
        image:    { select: 'Seleccionar imagen',    change: 'Cambiar imagen' },
        video:    { select: 'Seleccionar video',     change: 'Cambiar video' },
        document: { select: 'Seleccionar documento', change: 'Cambiar documento' },
        audio:    { select: 'Seleccionar audio',     change: 'Cambiar audio' },
        any:      { select: 'Seleccionar archivo',   change: 'Cambiar archivo' },
    },

    openPicker() {
        const filterTypeMap = { video: 'video', document: 'document', audio: 'audio' };
        const filterType = filterTypeMap[this.accept] ?? 'image';
        const mimeAccept = this.accept === 'any' ? ''
            : this.accept.includes('/') ? this.accept
            : this.accept + '/*';
        Alpine.store('filePicker').show({
            filterType, accept: mimeAccept, multi: false,
            onSelect: (file) => {
                const fileId = String(file.id ?? '');
                this.applyFile(fileId, bestFileOriginalUrl(file) || file.url || '');
                this.previewUrl = String(bestFilePreviewUrl(file) || file.url || '');
            },
        });
    },

    applyFile(fileId = '', fileUrl = '') {
        const normalizedId = String(fileId || '');
        const normalizedUrl = resolveTranslatableFilePreviewUrl(normalizedId, fileUrl);
        this.fileId = normalizedId;
        this.fileUrl = normalizedUrl;
        this.previewUrl = normalizedUrl;
    },

    clearFile() { this.applyFile('', ''); },
});
