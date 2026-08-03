import { devError } from '../utils/dev.js';
import { uiLabels, localePrefix } from '../utils/labels.js';

export const filePickerStore = {
    open: false,
    activeTab: 'library',
    files: [],
    allFiles: [],
    loading: false,
    error: false,
    errorMessage: '',
    search: '',
    filterType: '',
    showFilterTabs: true,
    thumbSize: 120,
    multiSelect: false,
    selected: [],
    pagination: { current_page: 1, last_page: 1, total_items: 0, per_page: 24 },
    dragging: false,
    uploading: false,
    uploadProgress: 0,
    uploadFileName: '',
    uploadError: '',
    _uploadFile: null,
    inputAccept: '',
    _onSelect: null,
    _onSelectMulti: null,
    _manifestPromise: null,

    show(options = {}) {
        this.open        = true;
        this.multiSelect = Boolean(options.multi);
        this.showFilterTabs = options.showFilterTabs !== false;
        this.filterType  = String(options.filterType || '');
        this.inputAccept = String(options.accept || '');
        this._onSelect      = typeof options.onSelect === 'function' ? options.onSelect : null;
        this._onSelectMulti = typeof options.onSelectMulti === 'function' ? options.onSelectMulti : null;
        this.activeTab   = 'library';
        this.search      = '';
        this.selected    = [];
        this.uploadFileName = '';
        this.uploadError    = '';
        this.uploading      = false;
        this.uploadProgress = 0;
        this._uploadFile    = null;
        this.files = [];
        this.allFiles = [];
        this.pagination = { current_page: 1, last_page: 1, total_items: 0, per_page: 24 };
        void this.loadFiles();
        requestAnimationFrame(() => {
            const panel = document.getElementById('file-picker-panel');
            if (panel instanceof HTMLElement) panel.focus();
        });
    },

    close() {
        this.open = false;
        this._onSelect = null;
        this._onSelectMulti = null;
    },

    switchTab(tab) {
        this.activeTab = tab;
        if (tab === 'library' && this.allFiles.length === 0) void this.loadFiles();
    },

    setSearch(value) {
        this.search = String(value || '');
        this.applyLocalFilters(1);
    },

    setFilterType(type) {
        this.filterType = String(type || '');
        this.applyLocalFilters(1);
    },

    changePage(page) {
        const bounded = Math.max(1, Math.min(this.pagination.last_page || 1, page));
        if (bounded !== this.pagination.current_page) this.applyLocalFilters(bounded);
    },

    _panel() { return document.getElementById('file-picker-panel'); },

    async loadFiles(force = false) {
        if (this._manifestPromise && !force) return this._manifestPromise;
        this.loading = true;
        this.error = false;
        this.errorMessage = '';
        const panel = this._panel();
        const manifestUrl = String(panel?.dataset?.manifestUrl || '/files/picker-manifest');

        this._manifestPromise = (async () => {
          try {
            const resp = await fetch(manifestUrl, {
                credentials: 'include',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const payload = await resp.json();
            const root = payload?.data && typeof payload.data === 'object' ? payload.data : payload;
            this.allFiles = Array.isArray(root?.items) ? root.items : [];
            this.applyLocalFilters(1);
          } catch (err) {
            devError('[filePicker] loadFiles error:', err);
            this.error = true;
            this.errorMessage = (uiLabels[localePrefix()] || uiLabels.es).loadRetry;
            this.files = [];
            this.allFiles = [];
          } finally {
            this.loading = false;
            this._manifestPromise = null;
          }
        })();

        return this._manifestPromise;
    },

    applyLocalFilters(page = 1) {
        const search = this.search.trim().toLowerCase();
        const filtered = this.allFiles.filter((file) => {
            const matchesSearch = search === ''
                || String(file.original_name || '').toLowerCase().includes(search);
            const matchesCategory = this.filterType === '' || String(file.category || '') === this.filterType;
            return matchesSearch && matchesCategory;
        });
        const perPage = this.pagination.per_page || 24;
        const lastPage = Math.max(1, Math.ceil(filtered.length / perPage));
        const currentPage = Math.max(1, Math.min(lastPage, page));
        const start = (currentPage - 1) * perPage;
        this.files = filtered.slice(start, start + perPage);
        this.pagination = {
            current_page: currentPage,
            last_page: lastPage,
            total_items: filtered.length,
            per_page: perPage,
        };
    },

    isSelected(file) { return this.selected.some((f) => String(f.id) === String(file.id)); },

    toggleSelected(file) {
        if (this.isSelected(file)) {
            this.selected = this.selected.filter((f) => String(f.id) !== String(file.id));
        } else {
            this.selected.push(file);
        }
    },

    select(file) {
        if (this.multiSelect) {
            this.toggleSelected(file);
        } else {
            if (typeof this._onSelect === 'function') this._onSelect(file);
            this.close();
        }
    },

    async _enrichFileWithMetadata(file) { return file; },

    async confirm() {
        if (typeof this._onSelectMulti === 'function') {
            const enrichedFiles = await Promise.all(this.selected.map((f) => this._enrichFileWithMetadata(f)));
            this._onSelectMulti(enrichedFiles);
        }
        this.close();
    },

    onUploadFileChange(event) {
        const file = event?.target?.files?.[0] ?? null;
        if (file) {
            this._uploadFile = file;
            this.uploadFileName = file.name;
            this.uploadError = '';
        } else {
            this._uploadFile = null;
            this.uploadFileName = '';
        }
    },

    async submitUpload() {
        if (!this._uploadFile || this.uploading) return;
        this.uploading = true;
        this.uploadProgress = 0;
        this.uploadError = '';
        const panel = this._panel();
        const uploadUrl = String(panel?.dataset?.uploadUrl || '/files/upload');
        const csrfName = String(panel?.dataset?.csrfName || '');
        const csrfHash = String(panel?.dataset?.csrfHash || '');
        const formData = new FormData();
        formData.append('file', this._uploadFile);
        if (csrfName !== '' && csrfHash !== '') formData.append(csrfName, csrfHash);

        try {
            await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) this.uploadProgress = Math.round((e.loaded / e.total) * 90);
                });
                xhr.open('POST', uploadUrl);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onload = () => {
                    let json = null;
                    try { json = JSON.parse(xhr.responseText); } catch { /* ignore */ }
                    if (json?.csrf_name && json?.csrf_hash && panel) {
                        panel.dataset.csrfName = String(json.csrf_name);
                        panel.dataset.csrfHash = String(json.csrf_hash);
                    }
                    if (xhr.status >= 200 && xhr.status < 300 && json?.ok !== false) {
                        resolve(json);
                    } else {
                        const msg = json?.messages?.[0] || json?.message || `HTTP ${xhr.status}`;
                        reject(new Error(String(msg)));
                    }
                };
                xhr.onerror = () => reject(new Error('Network error'));
                xhr.send(formData);
            });
            this.uploadProgress = 100;
            this._uploadFile = null;
            this.uploadFileName = '';
            this.switchTab('library');
            await this.loadFiles(true);
        } catch (err) {
            devError('[filePicker] submitUpload error:', err);
            this.uploadError = err instanceof Error ? err.message : 'Upload failed.';
        } finally {
            this.uploading = false;
            this.uploadProgress = 0;
        }
    },
};
