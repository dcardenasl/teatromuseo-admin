import { adminFetch } from '../../utils/wizard/adminFetch.js';

// ── WIZ-008: Edit menu ────────────────────────────────────────────
export const menu = {
    goEditMenu() {
        if (!this.config) return;
        if ((this.config.menus ?? []).length === 0) {
            this.errorMsg = this.strings.error_no_menus;
            this.screen = 'error';
            return;
        }
        if (this.config.menus.length === 1) {
            this.selectMenu(this.config.menus[0]);
            return;
        }
        this.screen = 'menu-select';
    },

    async selectMenu(targetMenu) {
        this.selectedMenu = targetMenu;
        this.menuItems = [];
        this.menuItemsError = '';
        this.menuItemsLoading = true;
        this.screen = 'menu-items';
        try {
            const res  = await adminFetch(`${this.wizardBase}/menus/${targetMenu.id}/items`, {}, this.csrf);
            const data = await res.json();
            if (!res.ok) throw new Error(data?.message ?? this.strings.error_items_load);
            const items = data?.items ?? data?.data ?? (Array.isArray(data) ? data : []);
            this.menuItems = items.map(item => ({
                ...item,
                _label: this.itemLabel(item),
                _url:   this.itemUrl(item),
            }));
        } catch (e) {
            this.menuItemsError = e.message ?? this.strings.error_items_load;
        } finally {
            this.menuItemsLoading = false;
        }
    },

    itemLabel(item) {
        if (!item) return '';
        const t = (item.translations ?? [])[0];
        return t?.label ?? t?.title ?? '';
    },

    itemUrl(item) {
        if (!item) return '';
        const t = (item.translations ?? [])[0];
        return t?.custom_url ?? t?.url ?? '';
    },

    moveItem(idx, delta) {
        const target = idx + delta;
        if (target < 0 || target >= this.menuItems.length) return;
        const temp = this.menuItems[idx];
        this.menuItems[idx] = this.menuItems[target];
        this.menuItems[target] = temp;
    },

    async patchItem(item) {
        try {
            const t   = (item.translations ?? [])[0] ?? {};
            const res = await adminFetch(
                `${this.wizardBase}/menus/items/${item.id}`,
                { method: 'POST', body: JSON.stringify({
                    // is_active ensures the payload is never empty after the domain extracts
                    // translations, which would otherwise trigger BaseCrudService's
                    // noFieldsToUpdate check (same pattern as blocks._updateBlock()).
                    is_active: true,
                    translations: [{ language_id: t.language_id ?? (this.defaultLangId || this.resolveDefaultLanguageId()), label: item._label, custom_url: item._url }],
                }) },
                this.csrf
            );
            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                this.menuSaveError = data?.message ?? this.strings.error_item_save;
            }
        } catch { /* network error — non-blocking */ }
    },

    async saveMenuOrder() {
        this.menuItemsSaving = true;
        this.menuSaveError   = '';
        try {
            const updates = this.menuItems.map((item, idx) =>
                adminFetch(
                    `${this.wizardBase}/menus/items/${item.id}`,
                    { method: 'POST', body: JSON.stringify({ sort_order: idx }) },
                    this.csrf
                )
            );
            await Promise.all(updates);
        } catch {
            this.menuSaveError = this.strings.error_item_save;
        } finally {
            this.menuItemsSaving = false;
        }
    },

    async addItem() {
        if (!this.newItemLabel || !this.selectedMenu) return;
        this.menuItemsSaving = true;
        try {
            const res  = await adminFetch(
                `${this.wizardBase}/menus/${this.selectedMenu.id}/items`,
                { method: 'POST', body: JSON.stringify({
                    link_type: 'custom_url', link_target: '_self',
                    sort_order: this.menuItems.length, is_active: true,
                    translations: [{ language_id: this.defaultLangId || this.resolveDefaultLanguageId(), label: this.newItemLabel, custom_url: this.newItemUrl || '#' }],
                }) },
                this.csrf
            );
            const data = await res.json();
            if (!res.ok) throw new Error(data?.message ?? this.strings.error_item_save);
            const newItem = data?.data ?? data;
            this.menuItems.push({ ...newItem, _label: this.newItemLabel, _url: this.newItemUrl || '#' });
            this.newItemLabel = '';
            this.newItemUrl   = '';
        } catch (e) {
            this.menuSaveError = e.message ?? this.strings.error_item_save;
        } finally {
            this.menuItemsSaving = false;
        }
    },

    confirmDeleteItem(item) {
        this.deleteItemTarget = item;
    },

    async deleteItem() {
        if (!this.deleteItemTarget) return;
        const item = this.deleteItemTarget;
        this.deleteItemTarget = null;
        try {
            await adminFetch(`${this.wizardBase}/menus/items/${item.id}/delete`, { method: 'POST' }, this.csrf);
            this.menuItems = this.menuItems.filter(i => i.id !== item.id);
        } catch {
            this.menuSaveError = this.strings.error_item_delete;
        }
    },
};
