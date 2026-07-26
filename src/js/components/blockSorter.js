/* global Sortable, MutationObserver, AbortController */
import { devError } from '../utils/dev.js';

export const blockSorter = (reorderUrl = '') => ({
    saving: false,
    saved: false,
    dirty: false,
    _list: null,
    _sortable: null,
    _saveTimeoutId: null,
    _abortController: null,

    _getItems() {
        if (!this._list) return [];
        return Array.from(this._list.querySelectorAll('[data-block-id], [data-id]'));
    },

    _getItemId(el) {
        if (!el) return '';
        return String(el.getAttribute('data-block-id') || el.getAttribute('data-id') || '');
    },

    _markDirty() {
        this.dirty = true;
        this.saved = false;
    },

    init() {
        if (!reorderUrl || typeof Sortable === 'undefined') {
            devError('[blockSorter] Missing reorderUrl or Sortable library');
            return;
        }
        const list = this.$el.querySelector('[data-sortable-list]') || this.$el.querySelector('#block-sortable-list');
        if (!list) { devError('[blockSorter] Sortable list element not found'); return; }
        this._list = list;
        this._abortController = new AbortController();
        this._cleanupSortable();
        try {
            this._sortable = Sortable.create(list, {
                handle: '[data-drag-handle]',
                animation: 150,
                ghostClass: 'opacity-40',
                onEnd: () => { this._markDirty(); },
            });
        } catch (err) { devError('[blockSorter] Failed to create Sortable instance:', err); }
        this._registerMutationObserver();
    },

    _registerMutationObserver() {
        if (typeof MutationObserver === 'undefined') return;
        const observer = new MutationObserver(() => {
            if (!document.contains(this.$el)) {
                this.destroy();
                observer.disconnect();
            }
        });
        observer.observe(this.$el.parentNode || document.body, { childList: true, subtree: true });
    },

    _cleanupSortable() {
        if (this._sortable && typeof this._sortable.destroy === 'function') {
            try { this._sortable.destroy(); } catch (err) { devError('[blockSorter] Error destroying Sortable:', err); }
        }
        this._sortable = null;
    },

    destroy() {
        if (this._saveTimeoutId) { clearTimeout(this._saveTimeoutId); this._saveTimeoutId = null; }
        if (this._abortController) { this._abortController.abort(); this._abortController = null; }
        this._cleanupSortable();
        this._list = null;
        this.saving = false;
        this.saved = false;
        this.dirty = false;
    },

    moveBlock(blockId, direction) {
        if (!this._list) {
            devError('[blockSorter] List reference is null');
            return false;
        }

        const delta = Number(direction);
        if (!Number.isInteger(delta) || delta === 0) {
            return false;
        }

        const items = this._getItems();
        const currentIndex = items.findIndex((el) => this._getItemId(el) === String(blockId));
        if (currentIndex < 0) {
            return false;
        }

        const nextIndex = currentIndex + delta;
        if (nextIndex < 0 || nextIndex >= items.length) {
            return false;
        }

        const current = items[currentIndex];
        const target = items[nextIndex];
        if (!current || !target || current === target) {
            return false;
        }

        if (delta < 0) {
            this._list.insertBefore(current, target);
        } else {
            this._list.insertBefore(current, target.nextElementSibling);
        }

        this._markDirty();
        return true;
    },

    moveUp(blockId) {
        return this.moveBlock(blockId, -1);
    },

    moveDown(blockId) {
        return this.moveBlock(blockId, 1);
    },

    saveOrder() {
        if (!this._list) { devError('[blockSorter] List reference is null'); return; }
        if (!reorderUrl) { devError('[blockSorter] Reorder URL is not configured'); return; }
        const items = this._getItems();
        if (items.length === 0) { devError('[blockSorter] No items found to reorder'); return; }

        const orders = {};
        items.forEach((el, index) => {
            const id = this._getItemId(el);
            if (id) orders[id] = index + 1;
        });
        if (Object.keys(orders).length === 0) { devError('[blockSorter] No valid item IDs found'); return; }

        this.saving = true;
        this.saved = false;
        if (this._saveTimeoutId) { clearTimeout(this._saveTimeoutId); this._saveTimeoutId = null; }

        const csrfInput = document.querySelector('input[name^="ci4_"][name$="_csrf_token"]') || document.querySelector('input[name="csrf_token"]');
        const csrfName = csrfInput?.name || 'csrf_token';
        const csrfToken = csrfInput?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const body = new URLSearchParams({ [csrfName]: csrfToken });
        Object.entries(orders).forEach(([id, pos]) => body.append(`orders[${id}]`, String(pos)));

        fetch(reorderUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body, signal: this._abortController?.signal })
            .then((r) => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(() => {
                if (this.saving) {
                    this.saving = false;
                    this.saved = true;
                    this.dirty = false;
                    this._saveTimeoutId = setTimeout(() => { this.saved = false; }, 2500);
                }
            })
            .catch((err) => {
                if (err.name === 'AbortError') return;
                devError('[blockSorter] Save order error:', err);
                if (this.saving) {
                    this.saving = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Error al guardar el orden' } }));
                }
            });
    },
});
