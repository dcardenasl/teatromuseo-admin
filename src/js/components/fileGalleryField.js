import { devError } from '../utils/dev.js';
import { isObject } from '../utils/url.js';
import { bestFileOriginalUrl, bestFilePreviewUrl } from '../utils/fileUrl.js';

const emptyInfo = () => ({ original_name: '', mime_type: '', category: '', is_image: false, url: '', previewUrl: '', human_size: '' });

export const fileGalleryField = (config = {}) => ({
    fieldName: String(config.name || 'gallery_file_ids'),
    ids: String(config.value || '')
        .split(',')
        .map((id) => id.trim())
        .filter((id) => id !== ''),
    filesInfo: {},
    loading: false,
    _accept: String(config.accept || ''),
    _filterType: String(config.filterType || ''),

    get csvValue() {
        return this.ids.join(',');
    },

    init() {
        this.ids.forEach((id) => this._loadFileInfo(id));
    },

    async _loadFileInfo(id) {
        if (!id || this.filesInfo[id]) return;
        this.loading = true;
        const panel = document.getElementById('file-picker-panel');
        const baseUrl = String(panel?.dataset?.baseUrl || '/files');
        try {
            const resp = await fetch(`${baseUrl}/${encodeURIComponent(String(id))}/picker-info`, {
                credentials: 'include',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const payload = await resp.json();
            if (payload?.ok && isObject(payload?.data)) {
                const d = payload.data;
                this.filesInfo[id] = {
                    original_name: String(d.original_name || ''),
                    mime_type: String(d.mime_type || ''),
                    category: String(d.category || ''),
                    is_image: Boolean(d.is_image),
                    url: String(bestFileOriginalUrl(d) || d.url || ''),
                    previewUrl: String(bestFilePreviewUrl(d) || d.url || ''),
                    human_size: String(d.human_size || ''),
                };
            } else {
                this.filesInfo[id] = emptyInfo();
            }
        } catch (err) {
            devError('[fileGalleryField] _loadFileInfo error:', err);
            this.filesInfo[id] = emptyInfo();
        } finally {
            this.loading = false;
        }
    },

    fileInfo(id) {
        return this.filesInfo[id] || emptyInfo();
    },

    openPicker() {
        Alpine.store('filePicker').show({
            accept: this._accept,
            filterType: this._filterType,
            multi: true,
            onSelectMulti: (files) => {
                files.forEach((file) => {
                    const id = String(file.id ?? '');
                    if (id === '' || this.ids.includes(id)) return;
                    this.ids.push(id);
                    this.filesInfo[id] = {
                        original_name: String(file.original_name || ''),
                        mime_type: String(file.mime_type || ''),
                        category: String(file.category || ''),
                        is_image: Boolean(file.is_image),
                        url: String(bestFileOriginalUrl(file) || file.url || ''),
                        previewUrl: String(bestFilePreviewUrl(file) || file.url || ''),
                        human_size: String(file.human_size || ''),
                    };
                });
            },
        });
    },

    removeFile(id) {
        this.ids = this.ids.filter((existing) => existing !== String(id));
    },
});
