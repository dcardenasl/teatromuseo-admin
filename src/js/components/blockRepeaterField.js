import { bestFilePreviewUrl } from '../utils/fileUrl.js';

const isImageAccept = (accept) => {
    const normalized = String(accept || '').trim().toLowerCase();
    return normalized === 'image'
        || normalized === 'image/*'
        || normalized.startsWith('image/');
};

const normalizeMediaReferenceValue = (value = {}) => {
    const raw = (value && typeof value === 'object' && !Array.isArray(value)) ? value : {};
    const fileId = String(raw.file_id ?? '');
    const url = String(raw.url ?? '');
    let sourceKind = String(raw.source_kind ?? raw.sourceKind ?? '').trim().toLowerCase();

    if (sourceKind === '') {
        sourceKind = fileId !== ''
            ? 'hub_file'
            : (url !== '' ? 'external_url' : 'hub_file');
    }

    return {
        source_kind: sourceKind,
        file_id: sourceKind === 'external_url' ? '' : fileId,
        url,
    };
};

const isMediaReferenceSubField = (subField = {}) => {
    return subField.type === 'media_reference'
        || (subField.type === 'file' && isImageAccept(subField.accept));
};

export const blockRepeaterField = (existingItems = [], itemFields = {}, fieldKey = '', langIdx = 0) => {
    const initItems = (existingItems || []).map((item) => {
        const out = {};
        Object.keys(itemFields || {}).forEach((subKey) => {
            const subField = itemFields[subKey] || {};
            if (isMediaReferenceSubField(subField)) {
                out[subKey] = normalizeMediaReferenceValue(
                    item[subKey] || {
                        source_kind: item[subKey + '_source_kind'] || '',
                        file_id: item[subKey + '_file_id'] || '',
                        url: item[subKey + '_url'] || '',
                    }
                );
            } else if (subField.type === 'file') {
                out[subKey + '_file_id'] = String(item[subKey + '_file_id'] || '');
                out[subKey + '_preview_url'] = '';
                out[subKey + '_url'] = String(item[subKey + '_url'] || '');
            } else {
                out[subKey] = item[subKey] ?? '';
            }
        });
        return out;
    });

    return {
        items: initItems,
        itemFields: itemFields || {},
        fieldKey,
        langIdx,

        addItem() {
            const item = {};
            Object.keys(this.itemFields).forEach((subKey) => {
                const subField = this.itemFields[subKey] || {};
                if (isMediaReferenceSubField(subField)) {
                    item[subKey] = normalizeMediaReferenceValue();
                } else if (subField.type === 'file') {
                    item[subKey + '_file_id'] = '';
                    item[subKey + '_preview_url'] = '';
                    item[subKey + '_url'] = '';
                } else {
                    item[subKey] = '';
                }
            });
            this.items.push(item);
        },

        removeItem(idx) { this.items.splice(idx, 1); },

        openPickerForItem(itemIdx, subKey, accept) {
            const filterTypeMap = { video: 'video', document: 'document', audio: 'audio' };
            const filterType = filterTypeMap[accept] ?? 'image';
            const mimeAccept = accept === 'any' ? ''
                : accept.includes('/') ? accept
                : accept + '/*';
            Alpine.store('filePicker').show({
                filterType, accept: mimeAccept, multi: false,
                onSelect: (file) => {
                    const fileId = String(file.id ?? '');
                    const canonicalUrl = String(file.url || '');
                    const previewUrl = String(bestFilePreviewUrl(file) || file.url || '');
                    if (this.items[itemIdx] && this.items[itemIdx][subKey] && typeof this.items[itemIdx][subKey] === 'object') {
                        this.items[itemIdx][subKey] = {
                            source_kind: 'hub_file',
                            file_id: fileId,
                            url: canonicalUrl,
                            preview_url: previewUrl,
                        };
                        return;
                    }

                    this.items[itemIdx][subKey + '_file_id']     = fileId;
                    this.items[itemIdx][subKey + '_url']         = canonicalUrl;
                    this.items[itemIdx][subKey + '_preview_url'] = previewUrl;
                },
            });
        },
    };
};
