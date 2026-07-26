import { bestFileOriginalUrl, bestFilePreviewUrl, resolveFilePreviewUrl } from '../utils/fileUrl.js';

const DEFAULT_SOURCE_KIND = 'hub_file';

const normalizeReferenceValue = (value = {}) => {
    const raw = (value && typeof value === 'object' && !Array.isArray(value)) ? value : {};
    const fileId = String(raw.file_id ?? raw.fileId ?? '');
    const url = String(raw.url ?? raw.external_url ?? '');
    let sourceKind = String(raw.source_kind ?? raw.sourceKind ?? '').trim().toLowerCase();

    if (sourceKind === '') {
        sourceKind = fileId !== '' ? 'hub_file' : (url !== '' ? 'external_url' : DEFAULT_SOURCE_KIND);
    }

    if (sourceKind === 'external_url') {
        return {
            source_kind: sourceKind,
            file_id: '',
            url,
            preview_url: String(raw.preview_url ?? raw.previewUrl ?? url ?? ''),
        };
    }

    return {
        source_kind: sourceKind,
        file_id: fileId,
        url,
        preview_url: String(raw.preview_url ?? raw.previewUrl ?? resolveFilePreviewUrl(fileId, url)),
    };
};

const snapshotReferenceForKind = (kind, reference = {}) => {
    const normalized = normalizeReferenceValue(reference);

    if (kind === 'external_url') {
        return {
            source_kind: 'external_url',
            file_id: '',
            url: normalized.url,
            preview_url: normalized.preview_url || normalized.url,
        };
    }

    return {
        source_kind: 'hub_file',
        file_id: normalized.file_id,
        url: normalized.url,
        preview_url: normalized.preview_url || resolveFilePreviewUrl(normalized.file_id, normalized.url),
    };
};

const emptyReferenceForKind = (kind) => ({
    source_kind: kind,
    file_id: '',
    url: '',
    preview_url: '',
});

export const mediaReferenceField = (initialValue = {}, accept = 'image', fieldKey = '') => {
    const normalized = normalizeReferenceValue(initialValue);
    const initialSnapshot = snapshotReferenceForKind(normalized.source_kind, normalized);

    return {
        fieldKey: String(fieldKey || ''),
        sourceKind: normalized.source_kind,
        fileId: normalized.file_id,
        url: normalized.url,
        previewUrl: normalized.preview_url,
        cachedExternalReference: normalized.source_kind === 'external_url'
            ? snapshotReferenceForKind('external_url', normalized)
            : emptyReferenceForKind('external_url'),
        cachedFileReference: normalized.source_kind === 'hub_file'
            ? snapshotReferenceForKind('hub_file', normalized)
            : emptyReferenceForKind('hub_file'),
        accept: String(accept || 'image'),
        pickerLabels: {
            image:    { select: 'Seleccionar imagen',    change: 'Cambiar imagen' },
            video:    { select: 'Seleccionar video',     change: 'Cambiar video' },
            document: { select: 'Seleccionar documento', change: 'Cambiar documento' },
            audio:    { select: 'Seleccionar audio',     change: 'Cambiar audio' },
            any:      { select: 'Seleccionar archivo',    change: 'Cambiar archivo' },
        },

        init() {
            this._applySourceDefaults();
            this._primeReferenceCache(initialSnapshot);
        },

        _applySourceDefaults() {
            const normalizedValues = normalizeReferenceValue({
                source_kind: this.sourceKind,
                file_id: this.fileId,
                url: this.url,
                preview_url: this.previewUrl,
            });
            this.sourceKind = normalizedValues.source_kind;
            this.fileId = normalizedValues.file_id;
            this.url = normalizedValues.url;
            this.previewUrl = normalizedValues.preview_url;
        },

        _primeReferenceCache(reference = {}) {
            const snapshot = snapshotReferenceForKind(this.sourceKind, reference);
            if (snapshot.source_kind === 'external_url') {
                this.cachedExternalReference = snapshot;
            } else {
                this.cachedFileReference = snapshot;
            }
        },

        _snapshotCurrentReference() {
            const snapshot = snapshotReferenceForKind(this.sourceKind, {
                source_kind: this.sourceKind,
                file_id: this.fileId,
                url: this.url,
                preview_url: this.previewUrl,
            });

            if (snapshot.source_kind === 'external_url') {
                if (snapshot.url !== '' || snapshot.preview_url !== '') {
                    this.cachedExternalReference = snapshot;
                }
                return;
            }

            if (snapshot.file_id !== '' || snapshot.url !== '' || snapshot.preview_url !== '') {
                this.cachedFileReference = snapshot;
            }
        },

        _referenceForKind(kind) {
            return kind === 'external_url'
                ? (this.cachedExternalReference ?? emptyReferenceForKind('external_url'))
                : (this.cachedFileReference ?? emptyReferenceForKind('hub_file'));
        },

        _visibleReferenceFallback() {
            const url = this.url.trim();
            const previewUrl = this.previewUrl.trim();

            return {
                url: url !== '' ? url : previewUrl,
                preview_url: previewUrl !== '' ? previewUrl : url,
            };
        },

        isFileSource() {
            return this.sourceKind === 'hub_file';
        },

        isExternalSource() {
            return this.sourceKind === 'external_url';
        },

        sourceKindLabel() {
            return this.isExternalSource() ? 'URL externa' : 'Biblioteca';
        },

        sourceKindHint(kind = this.sourceKind) {
            return String(kind) === 'external_url'
                ? 'Pega un enlace público directo.'
                : 'Selecciona un archivo desde la biblioteca.';
        },

        sourceKindButtonClass(kind) {
            const active = this.sourceKind === kind;
            return active
                ? 'border-brand-200 bg-brand-50 text-brand-800 ring-1 ring-brand-100 shadow-sm'
                : 'border-gray-200 bg-white text-gray-700 hover:border-brand-300 hover:bg-gray-50';
        },

        sourceKindDotClass(kind) {
            return this.sourceKind === kind ? 'bg-brand-500' : 'bg-gray-300';
        },

        pickerButtonLabel() {
            if (this.isExternalSource()) {
                return 'Abrir biblioteca';
            }

            return this.fileId !== ''
                ? (this.pickerLabels[this.accept]?.change || 'Cambiar archivo')
                : (this.pickerLabels[this.accept]?.select || 'Seleccionar archivo');
        },

        setSourceKind(kind) {
            const nextKind = String(kind || DEFAULT_SOURCE_KIND);
            if (nextKind === this.sourceKind) {
                if (nextKind === 'external_url') {
                    this.syncExternalUrl();
                }
                return;
            }

            this._snapshotCurrentReference();
            const visibleFallback = this._visibleReferenceFallback();

            if (nextKind === 'external_url') {
                const externalReference = this._referenceForKind('external_url');
                const fileFallback = this._referenceForKind('hub_file');
                this.applyReference({
                    source_kind: 'external_url',
                    file_id: '',
                    url: externalReference.url || visibleFallback.url || fileFallback.preview_url || fileFallback.url,
                    preview_url: externalReference.preview_url || visibleFallback.preview_url || externalReference.url || visibleFallback.url,
                });
                return;
            }

            const fileReference = this._referenceForKind('hub_file');
            const externalFallback = this._referenceForKind('external_url');
            this.applyReference({
                source_kind: 'hub_file',
                file_id: fileReference.file_id,
                url: fileReference.url || visibleFallback.url || externalFallback.preview_url || externalFallback.url,
                preview_url: fileReference.preview_url || visibleFallback.preview_url || resolveFilePreviewUrl(fileReference.file_id, fileReference.url || visibleFallback.url || externalFallback.preview_url || externalFallback.url),
            });
        },

        applyReference(reference = {}) {
            const normalizedValues = normalizeReferenceValue(reference);
            this.sourceKind = normalizedValues.source_kind;
            this.fileId = normalizedValues.file_id;
            this.url = normalizedValues.url;
            this.previewUrl = normalizedValues.preview_url;

            if (this.sourceKind === 'external_url') {
                this.cachedExternalReference = snapshotReferenceForKind('external_url', normalizedValues);
                return;
            }

            this.cachedFileReference = snapshotReferenceForKind('hub_file', normalizedValues);
        },

        openPicker() {
            const filterTypeMap = { video: 'video', document: 'document', audio: 'audio' };
            const filterType = filterTypeMap[this.accept] ?? 'image';
            const mimeAccept = this.accept === 'any'
                ? ''
                : this.accept.includes('/')
                    ? this.accept
                    : this.accept + '/*';

            Alpine.store('filePicker').show({
                filterType,
                accept: mimeAccept,
                multi: false,
                onSelect: (file) => {
                    this.applyReference({
                        source_kind: 'hub_file',
                        file_id: String(file.id ?? ''),
                        url: String(bestFileOriginalUrl(file) || file.url || ''),
                        preview_url: String(bestFilePreviewUrl(file) || file.url || ''),
                    });
                },
            });
        },

        syncExternalUrl() {
            if (this.isExternalSource()) {
                const trimmedUrl = this.url.trim();
                this.applyReference({
                    source_kind: 'external_url',
                    file_id: '',
                    url: trimmedUrl,
                    preview_url: trimmedUrl,
                });
            }
        },

        clearReference() {
            this.cachedExternalReference = emptyReferenceForKind('external_url');
            this.cachedFileReference = emptyReferenceForKind('hub_file');
            this.applyReference({
                source_kind: DEFAULT_SOURCE_KIND,
                file_id: '',
                url: '',
                preview_url: '',
            });
        },

        copyToAllLanguages() {
            if (this.fieldKey === '' || typeof window.copyLangTabsMediaReferenceFieldToAll !== 'function') {
                return;
            }

            window.copyLangTabsMediaReferenceFieldToAll(this.fieldKey, {
                source_kind: this.sourceKind,
                file_id: this.fileId,
                url: this.url,
                preview_url: this.previewUrl,
            });
        },
    };
};
