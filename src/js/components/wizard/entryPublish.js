import { adminFetch } from '../../utils/wizard/adminFetch.js';
import { wizardSlugify } from '../../utils/wizard/slugify.js';
import { truncateText } from '../../utils/wizard/truncateText.js';
import { schemaTypeToUiType } from '../../utils/wizard/schemaTypeToUiType.js';
import { humanizeKey } from '../../utils/wizard/humanizeKey.js';
import { normalizeBlockPayload } from '../../utils/wizard/normalizeBlockPayload.js';
import { validateEntryPayload as validateEntryPayloadPure } from '../../utils/wizard/validateEntryPayload.js';

const NATIVE_KEYS = ['title', 'excerpt', 'featured_image', 'body', 'status', 'meta_title', 'meta_description', 'og_image'];

// ── Entry creation flow (screens A) + publish ─────────────────────────────
export const entryPublish = {
    entryReviewLanguages() {
        return (Array.isArray(this.config?.languages) ? this.config.languages : [])
            .filter((language) => Number(language?.id || 0) > 0);
    },

    entryTranslationCount() {
        return this.entryTranslationRows.length;
    },

    entryTranslationBusy() {
        return this.entryReviewLoading || this.entryTranslationRows.some((row) => Boolean(row?.translating));
    },

    entryReviewSourceContent() {
        return {
            title: String(this.formData.title || this.selectedCollection?.name || this.strings.content_fallback || '').trim(),
            excerpt: String(this.formData.excerpt || '').trim(),
            meta_title: String(this.formData.meta_title || '').trim(),
            meta_description: String(this.formData.meta_description || '').trim(),
            featured_file_id: this.formData.featured_image_id ?? null,
            featured_image_url: this.formData.featured_image_url ?? null,
            og_image_file_id: this.formData.og_image_id ?? null,
            og_image_url: this.formData.og_image_url ?? null,
        };
    },

    entryReviewFields() {
        return this.steps.flatMap((step) => Array.isArray(step.fields) ? step.fields : []);
    },

    entryReviewPreviewFields() {
        const values = this.entryReviewSourceContent();
        return this.entryReviewFields()
            .filter((field) => !['date', 'select'].includes(String(field?.type || '')))
            .map((field) => ({
                key: field.key,
                label: field.label,
                value: values[field.key] || this.formData[field.key] || '',
            }))
            .filter((field) => String(field.value || '').trim() !== '');
    },

    entryBaseSlug() {
        return wizardSlugify(this.formData.title || 'entry');
    },

    async translateEntryText(text, sourceLang, targetLang) {
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
        const contentType = String(response.headers.get('content-type') || '').toLowerCase();
        const rawBody = await response.text();
        let json = null;

        if (contentType.includes('application/json')) {
            try {
                json = rawBody ? JSON.parse(rawBody) : null;
            } catch {
                json = null;
            }
        }

        if (response.ok && json && typeof json.translated === 'string' && json.translated.trim() !== '') {
            return json.translated.trim();
        }

        if (rawBody.trim().startsWith('<')) {
            throw new Error(this.strings.wizard_structure_languages_translate_error);
        }

        throw new Error(json?.error || json?.message || this.strings.wizard_structure_languages_translate_error);
    },

    async prepareEntryReview() {
        if (this.entryReviewLoading) {
            return;
        }

        this.entryReviewLoading = true;
        this.entryReviewError = '';
        this.entryTranslationRows = [];

        try {
            const sourceLanguage = this.defaultLanguageCode || 'EN';
            const source = this.entryReviewSourceContent();
            const defaultLanguageId = this.defaultLanguageId;
            const defaultLanguageCode = sourceLanguage;
            const defaultLanguageLabel = String(this.defaultLanguage?.label || this.defaultLanguage?.name || this.defaultLanguage?.code || '—').trim() || '—';
            const translatedRows = [];
            const targets = this.entryReviewLanguages()
                .filter((language) => Number(language?.id || 0) !== defaultLanguageId)
                .map((language) => ({
                    id: Number(language.id || 0),
                    code: String(language.code || '').trim().toUpperCase(),
                    label: String(language.label || language.name || language.code || '').trim() || '—',
                }));

            const tasks = targets.map(async (target) => {
                const row = {
                    language_id: target.id,
                    code: target.code,
                    label: target.label,
                    title: '',
                    slug: '',
                    excerpt: '',
                    meta_title: '',
                    meta_description: '',
                    translating: true,
                    error: '',
                };

                try {
                    row.title = source.title ? await this.translateEntryText(source.title, sourceLanguage, target.code) : '';
                    row.excerpt = source.excerpt ? await this.translateEntryText(source.excerpt, sourceLanguage, target.code) : '';
                    row.meta_title = source.meta_title ? await this.translateEntryText(source.meta_title, sourceLanguage, target.code) : '';
                    row.meta_description = source.meta_description ? await this.translateEntryText(source.meta_description, sourceLanguage, target.code) : '';
                    const slugBase = wizardSlugify(row.title || source.title || this.strings.wizard_structure_page_default_title);
                    row.slug = `${slugBase}-${target.code.toLowerCase()}`;
                } catch (error) {
                    row.error = error instanceof Error ? error.message : String(error);
                    row.title = source.title;
                    row.excerpt = source.excerpt;
                    row.meta_title = source.meta_title;
                    row.meta_description = source.meta_description;
                    row.slug = `${wizardSlugify(source.title || this.strings.wizard_structure_page_default_title)}-${target.code.toLowerCase()}`;
                } finally {
                    row.translating = false;
                    translatedRows.push(row);
                }
            });

            await Promise.all(tasks);
            this.entryTranslationRows = translatedRows.sort((a, b) => a.label.localeCompare(b.label));

            if (translatedRows.some((row) => row.error)) {
                this.entryReviewError = this.strings.wizard_content_review_translation_partial;
            }

            if (defaultLanguageId > 0) {
                this.entryTranslationRows.unshift({
                    language_id: defaultLanguageId,
                    code: defaultLanguageCode,
                    label: defaultLanguageLabel,
                    title: source.title,
                    slug: wizardSlugify(source.title || this.strings.wizard_structure_page_default_title),
                    excerpt: source.excerpt,
                    meta_title: source.meta_title,
                    meta_description: source.meta_description,
                    translating: false,
                    error: '',
                    is_base: true,
                });
            }
        } catch (error) {
            this.entryReviewError = error instanceof Error ? error.message : String(error);
        } finally {
            this.entryReviewLoading = false;
        }
    },

    // Field descriptors for a block-content wizard step, keyed by block_key directly
    // (no editMode/selectedBlock dependency — the entry doesn't exist yet at this point).
    //
    // Mirrors blocks.js's blockFields(): schema `fields` (translatable content,
    // source: 'data') plus `config_fields` filtered to media_reference (shared
    // asset config, source: 'config'). Without the config_fields half, a block
    // type like "image" or "hero_banner" — whose actual image lives in
    // config_fields, not fields — would never expose an image control here at
    // all, even when the collection's block_template marks that block required.
    blockContentFieldsFor(blockKey) {
        const schemaFields = blockKey ? (this.config?.block_types?.[blockKey]?.fields ?? null) : null;
        const configFields = blockKey ? (this.config?.block_types?.[blockKey]?.config_fields ?? null) : null;

        const mediaConfigFields = configFields
            ? Object.entries(configFields)
                .filter(([, def]) => schemaTypeToUiType(def.type ?? '', def.accept ?? '') === 'media_reference')
                .map(([k, def]) => ({
                    key:      k,
                    label:    def.label ?? humanizeKey(k),
                    required: def.required ?? false,
                    uiType:   'media_reference',
                    accept:   def.accept ?? 'image',
                    options:  [],
                    source:   'config',
                }))
            : [];

        if (!schemaFields || Object.keys(schemaFields).length === 0) return mediaConfigFields;

        return Object.entries(schemaFields).map(([k, def]) => ({
            key:      k,
            label:    def.label ?? humanizeKey(k),
            required: def.required ?? false,
            uiType:   schemaTypeToUiType(def.type ?? '', def.accept ?? '', def.primitive ?? ''),
            options:  def.options ?? [],
            source:   'data',
        })).concat(mediaConfigFields);
    },

    // ── Block content steps (collection block_template) ─────────────────
    // Richtext fields don't use x-model (see block_content_richtext binding below) —
    // their live value sits in a hidden input in the DOM, so it must be pulled in
    // before we validate/advance/leave the step, mirroring syncRichTextFields()
    // for the canonical block editor.
    syncBlockContentRichTextFields(stepIdx) {
        const nodes = document.querySelectorAll('[data-wizard-content-richtext-field]');
        nodes.forEach((node) => {
            const key = node?.dataset?.fieldKey;
            if (!key) return;
            const input = node.querySelector('input[type="hidden"]');
            if (!input) return;
            if (!this.blockContentDrafts[stepIdx]) this.blockContentDrafts[stepIdx] = {};
            this.blockContentDrafts[stepIdx][key] = input.value ?? '';
        });
    },

    syncBlockContentRichTextDraft(stepIdx, key, value) {
        if (!key) return;
        if (!this.blockContentDrafts[stepIdx]) this.blockContentDrafts[stepIdx] = {};
        this.blockContentDrafts[stepIdx][key] = value ?? '';
    },

    prevBlockStep() {
        this.syncBlockContentRichTextFields(this.blockContentStepIndex);
        if (this.blockContentStepIndex > 0) {
            this.blockContentStepIndex--;
        } else {
            this.currentStep = this.steps.length - 1;
            this.screen = 'steps';
        }
    },

    async nextBlockStep() {
        this.syncBlockContentRichTextFields(this.blockContentStepIndex);
        if (!this.canAdvanceBlockStep()) return;
        if (this.blockContentStepIndex < this.blockContentSteps.length - 1) {
            this.blockContentStepIndex++;
        } else {
            this.screen = 'confirm';
            await this.prepareEntryReview();
        }
    },

    skipBlockStep() {
        const step = this.blockContentSteps[this.blockContentStepIndex];
        if (!step || step.required) return;
        this.blockContentSkipped[step.idx] = true;
        this.nextBlockStep();
    },

    isBlockFieldFilled(draft, field) {
        if (field.uiType === 'image') return Boolean(draft[field.key + '_file_id']);
        if (field.uiType === 'media_reference') {
            const ref = draft[field.key];
            return Boolean(ref && typeof ref === 'object' && (ref.file_id || ref.url));
        }
        const val = draft[field.key];
        if (field.uiType === 'richtext') {
            const plain = String(val ?? '')
                .replace(/<[^>]*>/g, ' ')
                .replace(/&nbsp;/gi, ' ')
                .trim();
            return plain !== '';
        }
        return val !== undefined && val !== null && String(val).trim() !== '';
    },

    canAdvanceBlockStep() {
        const step = this.blockContentSteps[this.blockContentStepIndex];
        if (!step) return false;
        if (this.blockContentSkipped[step.idx]) return true;
        if ((step.fields || []).some(f => ['unsupported', 'file', 'datetime'].includes(f.uiType))) return false;
        if (!step.required) return true;
        const draft = this.blockContentDrafts[step.idx] ?? {};

        const explicitlyRequired = step.fields.filter(f => f.required);
        if (!explicitlyRequired.every(f => this.isBlockFieldFilled(draft, f))) return false;

        // Some block types (e.g. "image") don't mark any individual field as
        // required in their schema, even though the collection's block_template
        // flags the whole block as required. In that case, fall back to requiring
        // at least one field to be filled so a required block can't be left empty.
        if (explicitlyRequired.length === 0 && step.fields.length > 0) {
            return step.fields.some(f => this.isBlockFieldFilled(draft, f));
        }

        return true;
    },

    // ── Publish (entry wizard) ─────────────────────────────────────────
    async publish() {
        this.publishing = true;
        this.publishError = '';
        try {
            if (this.entryReviewLoading) {
                throw new Error(this.strings.wizard_content_confirm_translations_loading);
            }
            if (this.entryTranslationRows.length === 0) {
                await this.prepareEntryReview();
            }
            const payload = this.buildEntryPayload();
            const clientErrors = this.validateEntryPayload(payload);
            if (clientErrors.length > 0) {
                throw new Error(clientErrors.join(' '));
            }
            const res  = await adminFetch(this.wizardBase + '/publish', { method: 'POST', body: JSON.stringify(payload) }, this.csrf);
            const raw = await res.text();
            let data = {};
            try {
                data = raw ? JSON.parse(raw) : {};
            } catch {
                data = { message: raw || this.strings.error_publish };
            }
            if (!res.ok) {
                const msg = data?.message
                    ?? data?.messages?.[0]
                    ?? (raw ? raw : `${this.strings.error_publish} (HTTP ${res.status})`);
                const fieldErrors = data?.errors && typeof data.errors === 'object'
                    ? Object.entries(data.errors).map(([key, value]) => `${key}: ${Array.isArray(value) ? value.join(', ') : String(value)}`)
                    : [];

                throw new Error([msg, ...fieldErrors].filter(Boolean).join(' '));
            }
            this.publishBlockWarnings = [];
            const entryId = data?.id ?? data?.entry?.id ?? null;
            if (entryId && this.blockContentSteps.length > 0) {
                await this.saveBlockContentDrafts(entryId);
            }
            this.publishedEntry = data;
            this.clearDraft();
            this.screen = 'success';
        } catch (e) {
            this.publishError = e.message ?? this.strings.error_publish;
        } finally {
            this.publishing = false;
        }
    },

    // ── Block content drafts (collection block_template) ─────────────────
    // The entry was just created via POST /cms/entries, which auto-creates
    // one empty block instance per collection.block_template block (Domain's
    // EntryService::initializeBlocksFromTemplate). We now fetch those instances
    // and PUT (via the wizard's POST proxy) the content the user entered during
    // the block-content steps. Failures here are non-blocking: the entry itself
    // already exists, so we just surface which block(s) still need manual content.
    async saveBlockContentDrafts(entryId) {
        let instances;
        try {
            const res  = await adminFetch(`${this.wizardBase}/entries/${entryId}/blocks`, {}, this.csrf);
            const body = await res.json();
            if (!res.ok) throw new Error('HTTP ' + res.status);
            instances = body?.items ?? body?.data ?? (Array.isArray(body) ? body : []);
        } catch {
            this.publishBlockWarnings.push({ label: this.strings.error_blocks_load, blockKey: null });
            return;
        }

        const sortedInstances = [...instances].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
        const templateBlocks  = this.blockTemplateBlocks;
        const defaultLangId   = this.defaultLanguageId;
        const defaultLangCode = this.defaultLanguageCode || 'EN';
        const otherLanguages  = this.entryReviewLanguages().filter((l) => Number(l.id || 0) !== defaultLangId);

        for (let i = 0; i < templateBlocks.length; i++) {
            const blockDef = templateBlocks[i];
            const instance = sortedInstances[i];
            const draft    = this.blockContentDrafts[i];
            const skipped  = !!this.blockContentSkipped[i];
            const label    = blockDef.label || blockDef.block_key;

            if (!instance) {
                this.publishBlockWarnings.push({ label, blockKey: blockDef.block_key });
                continue;
            }
            if (skipped || !draft || Object.keys(draft).length === 0) {
                continue; // nothing entered — leave the auto-created empty block as-is
            }

            const instanceBlockKey = instance?.block_config?.block_key;
            if (instanceBlockKey && instanceBlockKey !== blockDef.block_key) {
                // Defensive: template/instance order mismatch — do not write content to the wrong block.
                this.publishBlockWarnings.push({ label, blockKey: blockDef.block_key });
                continue;
            }

            try {
                const translations = await this.buildBlockTranslations(blockDef.block_key, draft, defaultLangId, defaultLangCode, otherLanguages);
                const blockConfig = this.buildBlockContentConfig(blockDef.block_key, draft);
                const payload = { is_active: true, translations };
                if (Object.keys(blockConfig).length > 0) payload.block_config = blockConfig;
                const res = await adminFetch(`${this.wizardBase}/entries/${entryId}/blocks/${instance.id}`, {
                    method: 'POST',
                    body: JSON.stringify(payload),
                }, this.csrf);
                if (!res.ok) throw new Error('HTTP ' + res.status);
            } catch {
                this.publishBlockWarnings.push({ label, blockKey: blockDef.block_key });
            }
        }
    },

    // Config-field values (media_reference) live in block_config, not block_data —
    // they're a shared asset across languages, not per-language content.
    buildBlockContentConfig(blockKey, draft) {
        const configKeys = this.blockContentFieldsFor(blockKey)
            .filter((field) => field.source === 'config')
            .map((field) => field.key);

        const blockConfig = {};
        for (const key of configKeys) {
            if (draft[key] !== undefined) blockConfig[key] = draft[key];
        }
        return blockConfig;
    },

    async buildBlockTranslations(blockKey, draft, defaultLangId, defaultLangCode, otherLanguages) {
        const schemaFields = this.config?.block_types?.[blockKey]?.fields ?? {};
        const nonTranslatableTypes = Array.isArray(this.config?.non_translatable_types)
            ? this.config.non_translatable_types
            : ['file', 'image', 'media_reference', 'repeater', 'boolean', 'integer', 'select', 'number'];
        const translatableKeys = Object.entries(schemaFields)
            .filter(([, def]) => !nonTranslatableTypes.includes(def?.primitive ?? def?.type ?? 'string'))
            .map(([key]) => key);

        // Config-sourced keys (media_reference) must never leak into block_data —
        // they belong exclusively in block_config (see buildBlockContentConfig()).
        const configFieldKeys = this.blockContentFieldsFor(blockKey)
            .filter((field) => field.source === 'config')
            .map((field) => field.key);
        const draftData = { ...draft };
        for (const key of configFieldKeys) delete draftData[key];

        const baseData = normalizeBlockPayload(draftData);
        const rows = [{ language_id: defaultLangId, block_data: baseData, is_published: true }];

        for (const lang of otherLanguages) {
            const translatedData = { ...baseData };
            for (const key of translatableKeys) {
                const val = draft[key];
                if (typeof val === 'string' && val.trim() !== '') {
                    try {
                        translatedData[key] = await this.translateEntryText(val, defaultLangCode, lang.code);
                    } catch {
                        translatedData[key] = val; // fall back to source text, matching prepareEntryReview()
                    }
                }
            }
            rows.push({ language_id: Number(lang.id || 0), block_data: translatedData, is_published: true });
        }

        return rows;
    },

    buildEntryPayload() {
        const extra = {};
        for (const step of this.steps) {
            for (const field of step.fields) {
                if (NATIVE_KEYS.includes(field.key)) continue;
                if (field.type === 'image') {
                    const fileId = this.formData[field.key + '_id'] ?? null;
                    if (fileId !== null) {
                        extra[field.key + '_file_id'] = fileId;
                        extra[field.key + '_url']     = this.formData[field.key + '_url'] ?? null;
                    }
                } else if (this.formData[field.key] !== undefined) {
                    extra[field.key] = this.formData[field.key];
                }
            }
        }

        const payload = {
            collection_id:   this.selectedCollection.id,
            title:           this.formData.title ?? this.selectedCollection?.name ?? this.strings.content_fallback,
            status:          this.formData.status ?? 'published',
            workflow_status:  this.formData.status ?? 'published',
            sort_order: 0, view_count: 0, is_featured: false, is_in_sitemap: true,
            translations: [],
        };

        if (Object.keys(extra).length > 0) payload.wizard_extra = extra;

        payload.translations = this.buildEntryTranslations();

        return payload;
    },

    buildEntryTranslations() {
        const source = this.entryReviewSourceContent();
        const defaultLanguageId = this.defaultLanguageId || this.resolveDefaultLanguageId();
        const baseSlug = this.entryBaseSlug() + '-' + Date.now();
        const translations = [];

        if (defaultLanguageId > 0) {
            translations.push({
                language_id: defaultLanguageId,
                slug: truncateText(baseSlug, 150),
                title: truncateText(source.title, 255),
                excerpt: truncateText(source.excerpt, 500),
                meta_title: truncateText(source.meta_title, 255),
                meta_description: truncateText(source.meta_description, 500),
                featured_file_id: source.featured_file_id,
                featured_image_url: source.featured_image_url,
                og_image_file_id: source.og_image_file_id,
                og_image_url: source.og_image_url,
            });
        }

        if (Array.isArray(this.entryTranslationRows) && this.entryTranslationRows.length > 0) {
            this.entryTranslationRows
                .filter((row) => !row?.is_base && Number(row?.language_id || 0) > 0)
                .forEach((row) => {
                    const title = truncateText(row.title || source.title, 255);
                    const slugBase = wizardSlugify(title || source.title || 'entry');
                    translations.push({
                        language_id: Number(row.language_id || 0),
                        slug: truncateText(String(row.slug || '').trim() || `${slugBase}-${String(row.code || '').toLowerCase()}`, 150),
                        title,
                        excerpt: truncateText(row.excerpt, 500),
                        meta_title: truncateText(row.meta_title, 255),
                        meta_description: truncateText(row.meta_description, 500),
                        featured_file_id: source.featured_file_id,
                        featured_image_url: source.featured_image_url,
                        og_image_file_id: source.og_image_file_id,
                        og_image_url: source.og_image_url,
                    });
                });
        }

        if (translations.length === 0) {
            translations.push({
                language_id: defaultLanguageId || 1,
                slug: truncateText(baseSlug, 150),
                title: truncateText(source.title, 255),
                excerpt: truncateText(source.excerpt, 500),
                meta_title: truncateText(source.meta_title, 255),
                meta_description: truncateText(source.meta_description, 500),
                featured_file_id: source.featured_file_id,
                featured_image_url: source.featured_image_url,
                og_image_file_id: source.og_image_file_id,
                og_image_url: source.og_image_url,
            });
        }

        return translations;
    },

    validateEntryPayload(payload) {
        return validateEntryPayloadPure(payload, this.strings);
    },

    buildPublishedEntryPreview(payload, response) {
        const translations = Array.isArray(payload?.translations) ? payload.translations : [];
        const defaultLangId = this.defaultLangId || this.resolveDefaultLanguageId();
        const defaultTranslation = translations.find(t => t?.language_id === defaultLangId)
            ?? translations[0]
            ?? null;

        return {
            ...(response ?? {}),
            title: response?.title ?? this.formData.title ?? this.selectedCollection?.name ?? this.strings.content_fallback,
            slug: response?.slug ?? defaultTranslation?.slug ?? '',
            translations: response?.translations ?? translations,
        };
    },
};
