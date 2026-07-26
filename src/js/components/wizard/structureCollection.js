/* global Event */
import { adminFetch } from '../../utils/wizard/adminFetch.js';
import { wizardSlugify } from '../../utils/wizard/slugify.js';

// ── Collection creation flow (step 1: fields, step 2: translations) ──────
export const structureCollection = {
    collectionPresetBlocks() {
        return Array.isArray(this.collectionPreset?.block_template?.blocks) ? this.collectionPreset.block_template.blocks : [];
    },

    // COL-003: block types the selected preset originally referenced but that don't exist in
    // this deployment's active block catalog — CmsPresetCatalog::filterAvailablePresets() drops
    // just those blocks (not the whole preset) and reports them here instead of silently hiding
    // the preset with no explanation.
    collectionPresetMissingBlocks() {
        return Array.isArray(this.collectionPreset?.missing_blocks) ? this.collectionPreset.missing_blocks : [];
    },

    resolveCollectionPreset(type) {
        const presets = this.config?.collection_presets || {};
        return presets?.[type] || presets?.other || null;
    },

    selectCollectionType(type) {
        this.form.collection_type = type || 'other';
        this.collectionPreset = this.resolveCollectionPreset(this.form.collection_type);
        this.usePreset = this.collectionPreset !== null;
    },

    collectionTypeLabel(type = null) {
        const lookupType = type || this.form.collection_type;
        return (this.config?.collection_types || []).find((option) => option.key === lookupType)?.label || lookupType || '—';
    },

    syncCollectionSlugLanguage() {
        const slugInput = this.collectionSlugInput();
        if (!(slugInput && slugInput.tagName === 'INPUT')) {
            return;
        }
        const languageId = Number(this.resolveDefaultLanguageId() || this.translation.language_id || 0);
        slugInput.dataset.slugLanguageId = String(languageId > 0 ? languageId : '');
        slugInput.dispatchEvent(new Event('input', { bubbles: true }));
    },

    collectionTranslationBusy() {
        return this.collectionTranslating || this.collectionTranslations.some((row) => Boolean(row?.translating));
    },

    buildCollectionTranslations() {
        return (this.collectionTranslationLanguages || [])
            .filter((language) => Number(language?.id || 0) > 0)
            .map((language) => ({
                language_id: Number(language.id || 0),
                code: String(language.code || '').toUpperCase(),
                label: this.languageLabel(language),
                included: true,
                name: '',
                slug: '',
                translating: false,
                error: '',
            }));
    },

    resetCollectionTranslations() {
        this.collectionTranslations = this.buildCollectionTranslations();
        this.collectionTranslationError = '';
    },

    collectionTranslationNameInput(index) {
        return this.$el.querySelector(`#collection_translation_name_${index}`);
    },

    collectionTranslationSlugInput(index) {
        return this.$el.querySelector(`#collection_translation_slug_${index}`);
    },

    clearCollectionTranslationError(index) {
        if (this.collectionTranslations[index]) {
            this.collectionTranslations[index].error = '';
        }
        this.collectionTranslationError = '';
    },

    async translateText(text, sourceLang, targetLang) {
        const value = String(text || '').trim();
        const source = String(sourceLang || '').trim().toUpperCase();
        const target = String(targetLang || '').trim().toUpperCase();
        if (value === '' || source === '' || target === '') {
            return '';
        }

        const url = new URL(this.translateUrl, window.location.origin);
        url.searchParams.set('text', value);
        url.searchParams.set('source_lang', source);
        url.searchParams.set('target_lang', target);

        const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
        const json = await response.json();
        if (response.ok && json && typeof json.translated === 'string' && json.translated.trim() !== '') {
            return json.translated.trim();
        }

        throw new Error(json?.error || json?.message || this.strings.wizard_structure_languages_translate_error);
    },

    async _translateCollectionLanguage(index) {
        const row = this.collectionTranslations[index];
        if (!row || !row.included) {
            return;
        }

        const sourceLang = this.resolveDefaultLanguageCode();
        const sourceText = String(this.form.name || '').trim();
        if (sourceText === '') {
            row.error = this.strings.wizard_structure_translation_source_missing;
            return;
        }

        row.translating = true;
        row.error = '';
        this.collectionTranslationError = '';
        try {
            const translatedName = await this.translateText(sourceText, sourceLang, row.code);
            const nameInput = this.collectionTranslationNameInput(index);
            if (nameInput instanceof HTMLInputElement) {
                nameInput.value = translatedName;
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            } else {
                row.name = translatedName;
            }

            const slugInput = this.collectionTranslationSlugInput(index);
            if (slugInput instanceof HTMLInputElement) {
                slugInput.dispatchEvent(new Event('input', { bubbles: true }));
            } else if (row.slug.trim() === '') {
                row.slug = wizardSlugify(translatedName, 50);
            }
        } catch (error) {
            row.error = error instanceof Error ? error.message : String(error);
        } finally {
            row.translating = false;
        }
    },

    async translateCollectionLanguage(index) {
        if (this.collectionTranslating) {
            return;
        }

        this.collectionTranslating = true;
        try {
            await this._translateCollectionLanguage(index);
        } finally {
            this.collectionTranslating = false;
        }
    },

    async translateAllCollectionLanguages() {
        if (this.collectionTranslating) {
            return;
        }

        this.collectionTranslating = true;
        this.collectionTranslationError = '';
        try {
            for (let index = 0; index < this.collectionTranslations.length; index += 1) {
                const row = this.collectionTranslations[index];
                if (!row || !row.included) {
                    continue;
                }

                await this._translateCollectionLanguage(index);
            }
        } finally {
            this.collectionTranslating = false;
        }
    },

    collectionTranslationsValid(announce = false) {
        let valid = true;
        this.collectionTranslations.forEach((row) => {
            if (!row || !row.included) {
                if (row) {
                    row.error = '';
                }
                return;
            }

            const name = String(row.name || '').trim();
            const slug = String(row.slug || '').trim();
            if (name === '' || slug === '') {
                valid = false;
                if (announce) {
                    row.error = this.strings.wizard_structure_translation_required;
                }
                return;
            }

            if (announce) {
                row.error = '';
            }
        });

        if (! valid && announce) {
            this.collectionTranslationError = this.strings.wizard_structure_translation_review;
        }

        return valid;
    },

    resetCollectionFlow() {
        this.createdCollectionId = '';
        this.collectionCompleted = null;
        this.message = '';
        this.errorMsg = '';
        this.collectionErrors = { step1: '', slug_base: '' };
        this.collectionSlugAvailability = '';
        this.collectionTranslating = false;
        this.collectionStep = 1;
        this.form.collection_type = (this.config.collection_types || [])[0]?.key || 'other';
        this.collectionPreset = this.resolveCollectionPreset(this.form.collection_type);
        this.usePreset = this.collectionPreset !== null;
        this.form.slug_base = '';
        this.form.collection_key = '';
        this.form.name = '';
        this.resetCollectionTranslations();
        this.screen = 'collection';
    },

    collectionDetailUrl() {
        return this.collectionCompleted?.id ? `${this.routes.collections}/${this.collectionCompleted.id}` : this.routes.collections;
    },

    collectionEntryCreateUrl() {
        return this.collectionCompleted?.id
            ? `${this.routes.entriesCreate}?collection_id=${this.collectionCompleted.id}`
            : this.routes.entriesCreate;
    },

    collectionSlugInput() { return this.$el.querySelector('#collection_slug_base'); },

    onSlugAvailabilityChanged(event) {
        const detail = event?.detail || {};
        if (detail.id !== 'collection_slug_base') {
            return;
        }
        this.collectionSlugAvailability = String(detail.status || '');
    },

    collectionSlugStatus() { return String(this.collectionSlugAvailability || this.collectionSlugInput()?.dataset?.slugAvailability || ''); },

    isCollectionSlugValid() {
        const slugInput = this.collectionSlugInput();
        const status = this.collectionSlugStatus();
        return Boolean(slugInput && slugInput.tagName === 'INPUT' && status === 'available');
    },

    validateCollectionSlug(announce = false) {
        const slugInput = this.collectionSlugInput();
        if (!(slugInput && slugInput.tagName === 'INPUT')) {
            return true;
        }

        const status = this.collectionSlugStatus();
        if (status === 'checking') {
            if (announce) {
                this.collectionErrors.slug_base = this.strings.wizard_structure_slug_checking;
            }
            return false;
        }

        if (status === 'available') {
            this.collectionErrors.slug_base = '';
            return true;
        }

        if (slugInput.value.trim() === '') {
            this.collectionErrors.slug_base = announce ? this.strings.wizard_structure_step1_error : '';
            return false;
        }

        this.collectionErrors.slug_base = slugInput.validationMessage || this.strings.wizard_structure_slug_unavailable;
        return false;
    },

    validateCollectionStep1(announce = false) {
        this.collectionErrors.step1 = this.form.name ? '' : this.strings.wizard_structure_step1_error;
        const slugOk = this.validateCollectionSlug(announce);
        return Boolean(this.form.name) && slugOk;
    },

    canAdvanceCollectionStep() { return this.collectionStep === 1 ? Boolean(this.form.name) && this.collectionSlugStatus() === 'available' : true; },

    async nextCollectionStep() {
        if (!this.validateCollectionStep1(true)) {
            return;
        }
        this.collectionErrors = { step1: '', slug_base: '' };
        if (this.collectionStep < 2) {
            this.collectionStep += 1;
            this.resetCollectionTranslations();
            await this.translateAllCollectionLanguages();
        }
    },

    prevCollectionStep() { if (this.collectionStep > 1) this.collectionStep -= 1; },

    canSubmitCollection() {
        return Boolean(this.form.name && this.form.collection_key && this.form.collection_type)
            && this.collectionTranslationsValid(false)
            && !this.collectionTranslationBusy();
    },

    stepLabel() { return this.strings.step_of.replace('%s', this.collectionStep).replace('%s', '2'); },

    async submitCollection() {
        this.saving = true; this.message = ''; this.errorMsg = '';
        try {
            if (!this.collectionTranslationsValid(true)) {
                throw new Error(this.strings.wizard_structure_translation_review);
            }

            const defaultLanguageId = this.resolveDefaultLanguageId();
            const translations = [];
            if (defaultLanguageId > 0) {
                translations.push({
                    language_id: defaultLanguageId,
                    slug: this.form.slug_base || this.form.collection_key,
                    name: this.form.name,
                    description: '',
                });
            }
            this.collectionTranslations.forEach((row) => {
                if (!row || !row.included) {
                    return;
                }

                translations.push({
                    language_id: Number(row.language_id || 0),
                    slug: String(row.slug || '').trim() || wizardSlugify(row.name || '', 50),
                    name: String(row.name || '').trim(),
                    description: '',
                });
            });

            const selectedPreset = this.usePreset && this.collectionPreset ? this.collectionPreset : null;
            const blockTemplate = selectedPreset?.block_template || null;
            const wizardConfig = selectedPreset?.wizard_config || null;

            const payload = {
                collection_type: this.form.collection_type,
                collection_key: this.form.collection_key || this.form.slug_base,
                default_sitemap_priority: '0.5',
                default_changefreq: 'weekly',
                sort_order: this.form.sort_order ?? 0,
                is_active: '1',
                requires_approval: '0',
                enables_categories: '1',
                enables_tags: '1',
                block_template: blockTemplate ? JSON.stringify(blockTemplate) : null,
                wizard_config: wizardConfig ? JSON.stringify(wizardConfig) : null,
                translations,
            };
            const res = await adminFetch(this.routes.createCollection, { method: 'POST', body: JSON.stringify(payload) }, this.csrf);
            const json = await res.json();
            if (!json.ok) {
                const fieldErrors = json.fieldErrors && typeof json.fieldErrors === 'object' ? Object.values(json.fieldErrors).filter(Boolean) : [];
                const detail = typeof json.detail === 'string' ? json.detail : '';
                const errors = json.errors && typeof json.errors === 'object' ? Object.values(json.errors).filter(Boolean) : [];
                const generalError = json.errors && typeof json.errors === 'object' && typeof json.errors.general === 'string' ? json.errors.general : '';
                const message = fieldErrors.length > 0
                    ? String(fieldErrors[0])
                    : (json.message || detail || (errors.length > 0 ? String(errors[0]) : '') || generalError || this.strings.wizard_structure_error_collection);
                if (message) {
                    this.collectionErrors.step1 = '';
                    this.collectionErrors.slug_base = message;
                    this.collectionStep = 1;
                    const slugInput = this.collectionSlugInput();
                    if (slugInput && slugInput.tagName === 'INPUT') {
                        slugInput.setCustomValidity(message);
                        slugInput.focus();
                    }
                    return;
                }
                throw new Error(this.strings.wizard_structure_error_collection);
            }
            const id = json.data?.id || '';
            if (!id) {
                throw new Error(json.message || this.strings.wizard_structure_error_collection_missing_id);
            }
            this.collectionCompleted = {
                id: String(id),
                name: this.form.name || '',
                slug: this.form.collection_key || this.form.slug_base || '',
                type: this.form.collection_type || '',
            };
            this.createdCollectionId = this.collectionCompleted.id;
            this.screen = 'collection-success';
            this.message = '';
        } catch (e) {
            this.errorMsg = e.message || this.strings.wizard_structure_error_collection;
        } finally { this.saving = false; }
    },
};
