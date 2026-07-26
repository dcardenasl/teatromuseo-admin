import { devError } from '../utils/dev.js';
import { isObject } from '../utils/url.js';
import { bestFileOriginalUrl, bestFilePreviewUrl } from '../utils/fileUrl.js';

export const filePickerField = (config = {}) => ({
    fieldName: String(config.name || 'file_id'),
    fileId: String(config.value || ''),
    fileInfo: { original_name: '', mime_type: '', category: '', is_image: false, url: '', previewUrl: '', human_size: '' },
    loading: false,
    _accept: String(config.accept || ''),
    _filterType: String(config.filterType || ''),

    init() {
        if (this.fileId !== '') this._loadFileInfo(this.fileId);
    },

    async _loadFileInfo(id) {
        if (!id) return;
        this.loading = true;
        const panel = document.getElementById('file-picker-panel');
        const baseUrl = panel?.dataset?.dataUrl
            ? String(panel.dataset.dataUrl).replace('/picker-data', '')
            : '/files';
        try {
            const resp = await fetch(`${baseUrl}/${encodeURIComponent(String(id))}/picker-info`, {
                credentials: 'include',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const payload = await resp.json();
            if (payload?.ok && isObject(payload?.data)) {
                const d = payload.data;
                this.fileInfo = {
                    original_name: String(d.original_name || ''),
                    mime_type: String(d.mime_type || ''),
                    category: String(d.category || ''),
                    is_image: Boolean(d.is_image),
                    url: String(bestFileOriginalUrl(d) || d.url || ''),
                    previewUrl: String(bestFilePreviewUrl(d) || d.url || ''),
                    human_size: String(d.human_size || ''),
                };
            }
        } catch (err) {
            devError('[filePickerField] _loadFileInfo error:', err);
        } finally {
            this.loading = false;
        }
    },

    openPicker() {
        Alpine.store('filePicker').show({
            accept: this._accept,
            filterType: this._filterType,
            multi: false,
            onSelect: (file) => {
                this.fileId = String(file.id ?? '');
                this.fileInfo = {
                    original_name: String(file.original_name || ''),
                    mime_type: String(file.mime_type || ''),
                    category: String(file.category || ''),
                    is_image: Boolean(file.is_image),
                    url: String(bestFileOriginalUrl(file) || file.url || ''),
                    previewUrl: String(bestFilePreviewUrl(file) || file.url || ''),
                    human_size: String(file.human_size || ''),
                };
            },
        });
    },

    clearFile() {
        this.fileId = '';
        this.fileInfo = { original_name: '', mime_type: '', category: '', is_image: false, url: '', previewUrl: '', human_size: '' };
    },
});
