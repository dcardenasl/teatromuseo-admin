import { structureCollection } from './structureCollection.js';
import { structurePageMenu } from './structurePageMenu.js';

const navigation = {
    async init() {
        try {
            const res = await fetch(this.routes.config, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            this.config = json.data || {};
            const defaultLanguageId = this.resolveDefaultLanguageId();
            this.translation.language_id = defaultLanguageId;
            this.page.translations = [{ language_id: defaultLanguageId, slug: '', title: '', excerpt: '', meta_title: '', meta_description: '' }];
            this.form.collection_type = (this.config.collection_types || [])[0]?.key || 'other';
            this.collectionPreset = this.resolveCollectionPreset(this.form.collection_type);
            this.usePreset = this.collectionPreset !== null;
            this.collectionSlugAvailability = '';
            this.collectionTranslations = this.buildCollectionTranslations();
            this.syncCollectionSlugLanguage();
            this.screen = 'home';
        } catch {
            this.errorMsg = this.strings.wizard_structure_error_load;
            this.screen = 'error';
        }
    },

    resolveDefaultLanguageId() {
        const languages = this.config?.languages || [];
        const defaultLanguage = languages.find((language) => language?.is_default);
        return Number(defaultLanguage?.id || languages[0]?.id || 0);
    },

    resolveDefaultLanguage() {
        const languages = this.config?.languages || [];
        return languages.find((language) => language?.is_default) || languages[0] || this.collectionDefaultLanguage || null;
    },

    resolveDefaultLanguageCode() {
        const language = this.resolveDefaultLanguage();
        return String(language?.code || '').trim().toUpperCase();
    },

    languageLabel(language) {
        return String(language?.label || language?.name || language?.code || '').trim() || '—';
    },

    defaultCollectionLanguageLabel() {
        return this.languageLabel(this.resolveDefaultLanguage());
    },

    start(kind) {
        this.message = '';
        this.errorMsg = '';
        this.createdCollectionId = '';
        this.collectionCompleted = null;
        this.collectionErrors = { step1: '', slug_base: '' };
        this.collectionSlugAvailability = '';
        this.collectionTranslating = false;
        this.screen = kind;
        if (kind === 'collection') {
            this.collectionStep = 1;
            this.form.collection_type = (this.config.collection_types || [])[0]?.key || 'other';
            this.collectionPreset = this.resolveCollectionPreset(this.form.collection_type);
            this.usePreset = this.collectionPreset !== null;
            this.form.slug_base = '';
            this.form.collection_key = '';
            this.form.name = '';
            this.resetCollectionTranslations();
        }
        if (kind === 'page') {
            this.page.title = '';
            this.page.slug = '';
        }
        if (kind === 'menu') {
            this.menu.menu_key = '';
            this.menu.name = '';
        }
    },
};

export function structureWizard(bootConfig = (typeof window !== 'undefined' ? window.__structureWizardBoot : {})) {
    const boot = bootConfig ?? {};
    const translationLanguages = Array.isArray(boot.collectionTranslationLanguages) ? boot.collectionTranslationLanguages : [];

    const instance = {
        // ── Boot config (server → client bridge) ────────────────────────────
        csrf: { name: boot.csrfName ?? '', token: boot.csrfToken ?? '' },
        translateUrl: boot.translateUrl ?? '',
        routes: boot.routes ?? {},
        strings: boot.strings ?? {},
        collectionDefaultLanguage: boot.collectionDefaultLanguage ?? null,

        // ── State ─────────────────────────────────────────────────────────
        screen: 'loading', config: null, errorMsg: '', message: '', saving: false, createdCollectionId: '', collectionCompleted: null,
        collectionErrors: { step1: '', slug_base: '' },
        collectionSlugAvailability: '',
        form: { name: '', slug_base: '', collection_key: '', sort_order: 0, collection_type: 'blog' },
        collectionStep: 1,
        translation: { language_id: 0, description: '' },
        usePreset: true,
        collectionPreset: null,
        collectionTranslations: translationLanguages.map((language) => ({
            language_id: Number(language?.id || 0),
            code: String(language?.code || '').toUpperCase(),
            label: String(language?.label || language?.name || language?.code || '').trim() || '—',
            included: true,
            name: '',
            slug: '',
            translating: false,
            error: '',
        })),
        collectionTranslationError: '',
        collectionTranslating: false,
        collectionTranslationLanguages: translationLanguages,
        page: { page_type: 'generic', parent_id: null, translations: [], title: '', slug: '' },
        menu: { menu_key: '', location: 'main', is_active: true, name: '' },
    };

    // ── Feature modules ───────────────────────────────────────────────────
    // See index.js's assignModule() for why defineProperties (not spread) is used.
    Object.defineProperties(instance, Object.getOwnPropertyDescriptors(navigation));
    Object.defineProperties(instance, Object.getOwnPropertyDescriptors(structureCollection));
    Object.defineProperties(instance, Object.getOwnPropertyDescriptors(structurePageMenu));

    return instance;
}
