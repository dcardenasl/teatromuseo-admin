/* global confirm, alert, Sortable */
import { bootLucideIcons } from '../utils/lucide.js';
import { slugify } from '../utils/slug.js';
import { devError } from '../utils/dev.js';

const CHOICE_TYPES = ['select', 'radio', 'checkbox'];

export const formFieldBuilderFactory = (config = {}) => {
    const readJsonList = (elementId) => {
        const element = elementId ? document.getElementById(elementId) : null;
        if (!element) return [];
        try {
            const parsed = JSON.parse(element.textContent || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            console.warn('[forms] Could not parse field builder JSON data.', error);
            return [];
        }
    };

    const languages = Array.isArray(config.languages) ? config.languages : readJsonList(config.languagesElementId);
    const initialFields = Array.isArray(config.initialFields) ? config.initialFields : readJsonList(config.initialFieldsElementId);
    const toBoolean = (value) => value === true || value === 1 || value === '1';

    const normalizeField = (field) => ({
        ...field,
        is_required: toBoolean(field?.is_required),
        is_active: toBoolean(field?.is_active),
    });

    const emptyOptionLabels = () => {
        const labels = {};
        languages.forEach((lang) => { labels[lang.id] = ''; });
        return labels;
    };

    // Options are stable, language-independent values (field.options: string[]);
    // their display labels live per-language inside each translation's
    // option_labels map (field.translations[n].option_labels: {value: label}).
    // Rebuild the {value, labels: {langId: label}} shape the modal edits from
    // those two separate sources.
    //
    // manualValue: true once the user has typed into Value directly, so
    // further default-language label edits stop overwriting it — same
    // "manual override" behavior as the page/entry slug field's
    // data-slug-source auto-sync (src/js/utils/slug.js). Existing saved
    // options are always treated as manual: their value must never be
    // silently regenerated just because the modal reopened.
    const normalizeOptions = (optionValues, translations) => {
        const values = Array.isArray(optionValues) ? optionValues : [];
        const translationList = Array.isArray(translations) ? translations : [];

        return values.map((value) => {
            const stringValue = String(value);
            const labels = emptyOptionLabels();
            languages.forEach((lang) => {
                const trans = translationList.find((item) => item.language_id == lang.id);
                const optionLabels = trans && typeof trans.option_labels === 'object' && trans.option_labels !== null ? trans.option_labels : {};
                labels[lang.id] = String(optionLabels[stringValue] ?? '');
            });
            return { value: stringValue, manualValue: true, labels };
        });
    };

    const defaultTranslations = () => {
        const translations = {};
        languages.forEach((lang) => { translations[lang.id] = { label: '', placeholder: '', help_text: '' }; });
        return translations;
    };

    return {
        fields: initialFields.map(normalizeField),
        showModal: false,
        editingField: null,
        activeFieldLang: String(languages[0]?.code || 'es'),
        fieldError: '',
        translatingFieldAll: false,
        fieldForm: { field_key: '', field_type: 'text', is_required: false, is_active: true, options: [], translations: defaultTranslations() },

        init() {
            this.$watch('showModal', (value) => { if (!value) this.fieldError = ''; });

            // x-sortable/@sortable:end aren't real Alpine directives/events — nothing
            // provides them, so drag-and-drop silently did nothing. SortableJS is
            // already loaded globally (public/assets/vendor/sortable.min.js, see
            // layouts/partials/head.php) for blockSorter.js; wire it the same way here.
            const list = this.$el.querySelector('[data-fields-list]');
            if (!list || typeof Sortable === 'undefined') {
                devError('[forms] Missing fields list or Sortable library; drag reorder disabled.');
                return;
            }
            Sortable.create(list, {
                handle: '[data-drag-handle]',
                animation: 150,
                ghostClass: 'opacity-40',
                onEnd: () => this.onReorder(list),
            });
        },

        defaultFieldForm() {
            return { field_key: '', field_type: 'text', is_required: false, is_active: true, options: [], translations: defaultTranslations() };
        },

        isChoiceType() {
            return CHOICE_TYPES.includes(this.fieldForm.field_type);
        },

        addOption() {
            this.fieldForm.options.push({ value: '', manualValue: false, labels: emptyOptionLabels() });
            // New row renders raw <i data-lucide> markup — nothing converts it to an
            // SVG until Lucide re-scans the DOM. saveField() does that on success,
            // but that's too late for a field still being edited: the regenerate/
            // remove icons on rows added this session would stay invisible (present
            // and clickable, just not visibly rendered) until the next save.
            this.$nextTick(() => bootLucideIcons());
        },

        removeOption(index) {
            this.fieldForm.options.splice(index, 1);
            // Alpine's x-for is keyed by index, so removing a row re-keys every
            // option after it — the DOM nodes for those rows get rebuilt from raw
            // markup and need their icons re-processed too.
            this.$nextTick(() => bootLucideIcons());
        },

        // Only the default language's label drives the auto-generated value —
        // typing a translation into another language tab must never touch it.
        onOptionLabelInput(option, langId) {
            if (langId != config.defaultLangId) return;
            if (!option.manualValue) {
                option.value = slugify(option.labels[langId]);
            }
        },

        onOptionValueInput(option) {
            const defaultLabel = option.labels[config.defaultLangId] || '';
            const normalized = slugify(option.value);
            option.manualValue = normalized !== '' && normalized !== slugify(defaultLabel);
            option.value = normalized;
        },

        regenerateOptionValue(option) {
            option.manualValue = false;
            option.value = slugify(option.labels[config.defaultLangId] || '');
        },

        openCreate() {
            this.editingField = null;
            this.fieldForm = this.defaultFieldForm();
            this.activeFieldLang = String(languages[0]?.code || 'es');
            this.showModal = true;
        },

        openEdit(field) {
            this.editingField = field;
            const translations = {};
            languages.forEach((lang) => {
                const existing = (field.translations || []).find((item) => item.language_id == lang.id) || {};
                translations[lang.id] = { label: existing.label || '', placeholder: existing.placeholder || '', help_text: existing.help_text || '' };
            });
            this.fieldForm = {
                field_key: field.field_key,
                field_type: field.field_type,
                is_required: toBoolean(field.is_required),
                is_active: toBoolean(field.is_active),
                options: normalizeOptions(field.options, field.translations),
                translations,
            };
            this.activeFieldLang = String(languages[0]?.code || 'es');
            this.showModal = true;
            // Existing options render immediately with raw <i data-lucide> markup —
            // see the comment in addOption() for why this needs its own re-scan.
            this.$nextTick(() => bootLucideIcons());
        },

        closeModal() { this.showModal = false; this.editingField = null; },

        buildTranslationsArray() {
            return Object.entries(this.fieldForm.translations).map(([languageId, translation]) => {
                const optionLabels = {};
                this.fieldForm.options.forEach((option) => {
                    const value = String(option.value || '').trim();
                    const label = String(option.labels[languageId] || '').trim();
                    if (value !== '' && label !== '') optionLabels[value] = label;
                });
                return { language_id: parseInt(languageId, 10), ...translation, option_labels: optionLabels };
            });
        },

        // ── Field-level auto-translate ───────────────────────────────────────
        // Translates label/placeholder/help_text — and each option's label —
        // from the default language into one target language. Unlike
        // langTabs()'s _translatePairs, this reads and writes straight from
        // fieldForm.translations/options (Alpine state, not named DOM inputs)
        // since this modal's fields have no `name` attributes — the field is
        // saved via a JSON fetch(), not a native form submit.
        async translateFieldToLang(targetLangId) {
            const sourceLangId = config.defaultLangId;
            // Loose comparisons: id may arrive as a number (JS config literal) or
            // string (JSON-decoded), matching openEdit()'s `item.language_id == lang.id`.
            if (!config.translateUrl || !sourceLangId || targetLangId == sourceLangId) return;

            const targetLang = languages.find((lang) => lang.id == targetLangId);
            if (!targetLang) return;

            const translateText = async (text) => {
                const url = new URL(config.translateUrl, window.location.origin);
                url.searchParams.set('text', text);
                url.searchParams.set('source_lang', String(config.defaultLangCode || '').toUpperCase());
                url.searchParams.set('target_lang', String(targetLang.code || '').toUpperCase());
                const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                const json = await res.json();
                if (json && typeof json.translated === 'string') return json.translated;
                if (json && json.error) throw new Error(json.error);
                return null;
            };

            const source = this.fieldForm.translations[sourceLangId] || {};
            const target = this.fieldForm.translations[targetLangId] || (this.fieldForm.translations[targetLangId] = { label: '', placeholder: '', help_text: '' });

            for (const key of ['label', 'placeholder', 'help_text']) {
                const text = String(source[key] || '').trim();
                if (text === '') continue;
                const translated = await translateText(text);
                if (translated !== null) target[key] = translated;
            }

            for (const option of this.fieldForm.options) {
                const sourceLabel = String(option.labels[sourceLangId] || '').trim();
                if (sourceLabel === '') continue;
                const translated = await translateText(sourceLabel);
                if (translated !== null) option.labels[targetLangId] = translated;
            }
        },

        async translateFieldAll() {
            if (this.translatingFieldAll) return;
            this.translatingFieldAll = true;
            this.fieldError = '';
            try {
                for (const lang of languages) {
                    if (lang.id == config.defaultLangId) continue;
                    await this.translateFieldToLang(lang.id);
                }
            } catch (error) {
                this.fieldError = error instanceof Error ? error.message : String(error);
            } finally {
                this.translatingFieldAll = false;
            }
        },

        async saveField() {
            this.fieldError = '';
            const fieldKey = String(this.fieldForm.field_key || '').trim();
            if (fieldKey === '') { this.fieldError = config.fieldKeyRequiredMessage || 'The field key is required.'; return; }

            const isChoice = this.isChoiceType();
            const options = isChoice
                ? [...new Set(this.fieldForm.options.map((opt) => String(opt.value || '').trim()).filter((value) => value !== ''))]
                : null;

            if (isChoice && options.length === 0) {
                this.fieldError = config.optionsRequiredMessage || 'Add at least one option for this field type.';
                return;
            }

            const payload = { field_key: fieldKey, field_type: this.fieldForm.field_type, is_required: this.fieldForm.is_required, is_active: this.fieldForm.is_active, options, translations: this.buildTranslationsArray() };
            let url = config.storeUrl;
            if (this.editingField) url = `${config.updateUrlTemplate}/${this.editingField.id}/update`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': config.csrfToken, [config.csrfName]: config.csrfToken },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (data.ok) {
                    const field = normalizeField(data.data);
                    if (this.editingField) {
                        const index = this.fields.findIndex((item) => String(item.id) === String(field.id));
                        if (index >= 0) this.fields[index] = field;
                    } else {
                        this.fields.push(field);
                    }
                    this.closeModal();
                    bootLucideIcons();
                } else {
                    this.fieldError = (data.messages && data.messages[0]) || config.saveFieldFailedMessage || 'Could not save the field.';
                }
            } catch (error) {
                this.fieldError = error.message || config.saveFieldFailedMessage || 'Could not save the field.';
            }
        },

        async deleteField(field) {
            if (!confirm(config.confirmDeleteFieldMessage || 'Delete this field?')) return;
            try {
                const response = await fetch(`${config.deleteUrlTemplate}/${field.id}/delete`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': config.csrfToken, [config.csrfName]: config.csrfToken },
                });
                const data = await response.json();
                if (data.ok) {
                    this.fields = this.fields.filter((item) => String(item.id) !== String(field.id));
                } else {
                    alert((data.messages && data.messages[0]) || config.deleteFailedMessage || 'Could not delete the field.');
                }
            } catch (error) { alert(error.message); }
        },

        async onReorder(list) {
            const ordered = Array.from(list.querySelectorAll('[data-id]'))
                .map((item) => parseInt(item.dataset.id, 10))
                .filter((id) => Number.isFinite(id));
            const sorted = [];
            ordered.forEach((id) => {
                const field = this.fields.find((item) => String(item.id) === String(id));
                if (field) sorted.push(field);
            });
            this.fields = sorted;
            await fetch(config.reorderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': config.csrfToken, [config.csrfName]: config.csrfToken },
                body: JSON.stringify({ ordered_ids: ordered }),
            });
        },
    };
};
