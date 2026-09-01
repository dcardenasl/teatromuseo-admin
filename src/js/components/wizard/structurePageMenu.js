import { adminFetch } from '../../utils/wizard/adminFetch.js';
import { wizardSlugify } from '../../utils/wizard/slugify.js';

// ── Page & menu creation flows ────────────────────────────────────────────
export const structurePageMenu = {
    normalizeFieldErrorKey(key) {
        return String(key || '').replace(/\[([^\]]*)\]/g, '.$1').replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
    },

    setFieldErrors(errors, aliases = {}) {
        const normalized = {};
        Object.entries(errors && typeof errors === 'object' ? errors : {}).forEach(([key, message]) => {
            const value = String(message || '');
            if (value !== '') normalized[this.normalizeFieldErrorKey(key)] = value;
        });
        Object.entries(aliases).forEach(([source, target]) => {
            const message = normalized[this.normalizeFieldErrorKey(source)];
            if (message) normalized[target] = message;
        });
        this.fieldErrors = normalized;
        return normalized;
    },

    fieldErrorFor(field) {
        const key = this.normalizeFieldErrorKey(field);
        return String(this.fieldErrors?.[key] || '');
    },

    fieldHasError(field) {
        return this.fieldErrorFor(field) !== '';
    },

    firstFieldError() {
        return Object.values(this.fieldErrors || {}).find((message) => String(message || '') !== '') || '';
    },

    pageTypeLabel() {
        return (this.config?.page_types || []).find((option) => option.key === this.page.page_type)?.label || this.page.page_type || '—';
    },

    async submitPage() {
        this.message = ''; this.errorMsg = ''; this.fieldErrors = {};
        try {
            const payload = {
                page_type: this.page.page_type,
                parent_id: null,
                translations: [{
                    language_id: this.translation.language_id,
                    slug: wizardSlugify(this.page.slug || this.page.title || this.strings.wizard_structure_page_default_title, 50),
                    title: this.page.title || this.strings.wizard_structure_page_default_title,
                    excerpt: '', meta_title: '', meta_description: '',
                }],
            };
            const res = await adminFetch(this.routes.createPage, { method: 'POST', body: JSON.stringify(payload) }, this.csrf);
            const json = await res.json();
            if (!json.ok) {
                const rawFieldErrors = json.fieldErrors && Object.keys(json.fieldErrors).length > 0 ? json.fieldErrors : json.errors;
                this.setFieldErrors(rawFieldErrors, {
                    'translations.0.title': 'page_title',
                    'translations.0.slug': 'page_slug',
                });
                throw new Error(this.firstFieldError() || json.message || this.strings.wizard_structure_error_page);
            }
            const id = json.data?.id || '';
            this.message = this.strings.wizard_structure_page_created;
            if (id) setTimeout(() => window.location.href = `${this.routes.pages}/${id}`, 700);
        } catch (e) {
            this.errorMsg = e.message || this.strings.wizard_structure_error_page;
        }
    },

    async submitMenu() {
        this.message = ''; this.errorMsg = ''; this.fieldErrors = {};
        try {
            const payload = {
                menu_key: wizardSlugify(this.menu.menu_key || this.menu.name || this.strings.wizard_structure_menu_default_key, 50),
                location: this.menu.location || 'main',
                is_active: this.menu.is_active ? 1 : 0,
                translations: [{ language_id: this.translation.language_id, name: this.menu.name || this.strings.wizard_structure_menu_default_name }],
            };
            const res = await adminFetch(this.routes.createMenu, { method: 'POST', body: JSON.stringify(payload) }, this.csrf);
            const json = await res.json();
            if (!json.ok) {
                const rawFieldErrors = json.fieldErrors && Object.keys(json.fieldErrors).length > 0 ? json.fieldErrors : json.errors;
                this.setFieldErrors(rawFieldErrors, {
                    'translations.0.name': 'menu_name',
                });
                throw new Error(this.firstFieldError() || json.message || this.strings.wizard_structure_error_menu);
            }
            const id = json.data?.id || '';
            this.message = this.strings.wizard_structure_menu_created;
            if (id) setTimeout(() => window.location.href = `${this.routes.menus}/${id}`, 700);
        } catch (e) {
            this.errorMsg = e.message || this.strings.wizard_structure_error_menu;
        }
    },
};
