import { adminFetch } from '../../utils/wizard/adminFetch.js';
import { humanizeKey } from '../../utils/wizard/humanizeKey.js';
import { defaultSteps } from './bootStrings.js';

// ── Lifecycle, screen navigation, and entry-collection selection ─────────
export const navigation = {
    // ── Computed ──────────────────────────────────────────────────────
    get steps() {
        return this.selectedCollection?.wizard_config?.steps ?? defaultSteps(this.strings);
    },
    get currentStepSchema() {
        return this.steps[this.currentStep] ?? null;
    },
    get totalSteps() {
        return this.steps.length;
    },
    get blockTemplateBlocks() {
        const blocks = this.selectedCollection?.block_template?.blocks;
        return Array.isArray(blocks)
            ? [...blocks].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0))
            : [];
    },
    get blockContentSteps() {
        return this.blockTemplateBlocks.map((blockDef, idx) => ({
            idx,
            block_key: blockDef.block_key,
            label: blockDef.label || this.blockTypeInfo(blockDef.block_key)?.name || humanizeKey(blockDef.block_key),
            help_text: blockDef.help_text || '',
            required: !!blockDef.required,
            locked: !!blockDef.locked,
            fields: this.blockContentFieldsFor(blockDef.block_key),
        }));
    },
    get totalBlockSteps() {
        return this.blockContentSteps.length;
    },
    // ── Unified progress (native fields + block content share one sequence) ──
    get totalUnifiedSteps() {
        return this.totalSteps + this.totalBlockSteps;
    },
    get currentUnifiedIndex() {
        return this.screen === 'block-steps'
            ? this.totalSteps + this.blockContentStepIndex
            : this.currentStep;
    },
    get isLastUnifiedStep() {
        return this.currentUnifiedIndex >= this.totalUnifiedSteps - 1;
    },
    get defaultLanguage() {
        const languages = Array.isArray(this.config?.languages) ? this.config.languages : [];
        return languages.find((language) => language?.is_default) || languages[0] || null;
    },
    get defaultLanguageId() {
        return Number(this.defaultLanguage?.id || this.resolveDefaultLanguageId());
    },
    get defaultLanguageCode() {
        return String(this.defaultLanguage?.code || '').trim().toUpperCase();
    },

    // ── Helpers ───────────────────────────────────────────────────────
    unifiedStepLabel() {
        return this.strings.step_of.replace('%s', this.currentUnifiedIndex + 1).replace('%s', this.totalUnifiedSteps);
    },

    unifiedProgressPercent() {
        if (this.totalUnifiedSteps <= 0) return 0;
        return Math.round(((this.currentUnifiedIndex + 1) / this.totalUnifiedSteps) * 100);
    },

    deleteConfirmText() {
        const label = this.deleteItemTarget?._label ?? '';
        return this.strings.delete_confirm.replace('%s', label);
    },

    addContentDesc() {
        const cols = this.config?.collections ?? [];
        if (cols.length === 0) return this.strings.add_content_desc_empty;
        const names = cols.slice(0, 4).map(c => this.collectionDisplayLabel(c));
        return names.join(', ') + (cols.length > 4 ? '…' : '');
    },

    collectionDisplayLabel(collection) {
        if (!collection) return this.strings.content_fallback;

        const name = String(collection.name ?? '').trim() || this.strings.content_fallback;
        const key = String(collection.collection_key ?? '').trim();
        const prefix = String(collection.url_prefix ?? '').trim();
        const descriptor = key || prefix;

        if (!descriptor || descriptor === name.toLowerCase()) {
            return name;
        }

        return `${name} · ${descriptor}`;
    },

    // ── Lifecycle ─────────────────────────────────────────────────────
    async init() {
        this.screen = 'loading';
        this.draft = this.loadDraft();

        try {
            const res = await adminFetch(this.wizardBase + '/config', {}, this.csrf);
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            this.config = data;
            this.defaultLangId = this.resolveDefaultLanguageId();
            this.screen = 'home';
        } catch {
            this.errorMsg = this.strings.error_load;
            this.screen = 'error';
        }
    },

    resolveDefaultLanguageId() {
        const languages = Array.isArray(this.config?.languages) ? this.config.languages : [];
        const defaultLanguage = languages.find((language) => language?.is_default);
        return Number(defaultLanguage?.id || languages[0]?.id || 1);
    },

    goHome() {
        this.errorMsg = '';
        this.screen = 'home';
    },

    // ── Navigation ────────────────────────────────────────────────────
    goAddContent() {
        this.screen = 'collection-select';
    },

    selectCollection(col) {
        this.selectedCollection = col;
        this.currentStep = 0;
        this.formData = { status: 'published' };
        this.entryReviewLoading = false;
        this.entryReviewError = '';
        this.entryTranslationRows = [];
        this.blockContentDrafts = {};
        this.blockContentSkipped = {};
        this.blockContentStepIndex = 0;
        this.publishBlockWarnings = [];
        this.blockTemplateBlocks.forEach((_, idx) => { this.blockContentDrafts[idx] = {}; });
        for (const step of this.steps) {
            for (const field of step.fields) {
                if (field.default !== undefined && field.default !== null) {
                    this.formData[field.key] = field.default;
                }
            }
        }
        this.publishError = '';
        this.screen = 'steps';
    },

    prevStep() {
        if (this.currentStep > 0) {
            this.currentStep--;
        } else {
            this.screen = 'collection-select';
        }
    },

    async nextStep() {
        if (!this.canAdvance()) return;
        if (this.currentStep < this.steps.length - 1) {
            this.currentStep++;
            this.saveDraft();
        } else if (this.blockContentSteps.length > 0) {
            this.blockContentStepIndex = 0;
            this.screen = 'block-steps';
        } else {
            this.screen = 'confirm';
            await this.prepareEntryReview();
        }
    },

    canAdvance() {
        const step = this.currentStepSchema;
        if (!step) return false;
        return step.fields
            .filter(f => f.required)
            .every(f => {
                if (f.type === 'image') return Boolean(this.formData[f.key + '_id']);
                const val = this.formData[f.key];
                return val !== undefined && val !== null && String(val).trim() !== '';
            });
    },

    restart() {
        this.clearDraft();
        this.selectedCollection = null;
        this.formData = {};
        this.currentStep = 0;
        this.publishedEntry = null;
        this.publishError = '';
        this.entryReviewLoading = false;
        this.entryReviewError = '';
        this.entryTranslationRows = [];
        this.blockContentDrafts = {};
        this.blockContentSkipped = {};
        this.blockContentStepIndex = 0;
        this.publishBlockWarnings = [];
        this.selectedPage = null;
        this.selectedOwnerType = 'page';
        this.pageBlocks = [];
        this.pageBlocksError = '';
        this.pageBlocksLoading = false;
        this.blocksBackScreen = 'page-select';
        this.screen = 'home';
    },
};
