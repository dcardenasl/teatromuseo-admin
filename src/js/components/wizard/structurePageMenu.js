import { adminFetch } from '../../utils/wizard/adminFetch.js';
import { wizardSlugify } from '../../utils/wizard/slugify.js';

// ── Page & menu creation flows ────────────────────────────────────────────
export const structurePageMenu = {
    pageTypeLabel() {
        return (this.config?.page_types || []).find((option) => option.key === this.page.page_type)?.label || this.page.page_type || '—';
    },

    async submitPage() {
        this.message = ''; this.errorMsg = '';
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
            if (!json.ok) throw new Error(json.message || this.strings.wizard_structure_error_page);
            const id = json.data?.id || '';
            this.message = this.strings.wizard_structure_page_created;
            if (id) setTimeout(() => window.location.href = `${this.routes.pages}/${id}`, 700);
        } catch (e) {
            this.errorMsg = e.message || this.strings.wizard_structure_error_page;
        }
    },

    async submitMenu() {
        this.message = ''; this.errorMsg = '';
        try {
            const payload = {
                menu_key: wizardSlugify(this.menu.menu_key || this.menu.name || this.strings.wizard_structure_menu_default_key, 50),
                location: this.menu.location || 'main',
                is_active: this.menu.is_active ? 1 : 0,
                translations: [{ language_id: this.translation.language_id, name: this.menu.name || this.strings.wizard_structure_menu_default_name }],
            };
            const res = await adminFetch(this.routes.createMenu, { method: 'POST', body: JSON.stringify(payload) }, this.csrf);
            const json = await res.json();
            if (!json.ok) throw new Error(json.message || this.strings.wizard_structure_error_menu);
            const id = json.data?.id || '';
            this.message = this.strings.wizard_structure_menu_created;
            if (id) setTimeout(() => window.location.href = `${this.routes.menus}/${id}`, 700);
        } catch (e) {
            this.errorMsg = e.message || this.strings.wizard_structure_error_menu;
        }
    },
};
