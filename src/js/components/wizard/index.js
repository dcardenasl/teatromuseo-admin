import { humanizeKey } from '../../utils/wizard/humanizeKey.js';
import { navigation } from './navigation.js';
import { entryPublish } from './entryPublish.js';
import { blocks } from './blocks.js';
import { menu } from './menu.js';
import { uploads } from './uploads.js';
import { draft } from './draft.js';

export function wizard(bootConfig = (typeof window !== 'undefined' ? window.__wizardBoot : {})) {
    const boot = bootConfig ?? {};

    const instance = {
        // ── Boot config (server → client bridge) ────────────────────────────
        csrf: { name: boot.csrfName ?? '', token: boot.csrfToken ?? '' },
        wizardBase: boot.wizardBase ?? '',
        adminCmsBase: boot.adminCmsBase ?? '',
        publicSiteUrl: boot.publicSiteUrl ?? '',
        translateUrl: boot.translateUrl ?? '',
        strings: boot.strings ?? {},

        // ── State ─────────────────────────────────────────────────────────
        screen: 'loading',
        config: null,
        defaultLangId: 0,
        errorMsg: '',

        // Add-content flow
        selectedCollection: null,
        currentStep: 0,
        formData: {},
        publishedEntry: null,
        publishing: false,
        publishError: '',
        entryReviewLoading: false,
        entryReviewError: '',
        entryTranslationRows: [],

        // Block content steps (collection block_template → per-block content prompts)
        blockContentDrafts: {},
        blockContentStepIndex: 0,
        blockContentSkipped: {},
        publishBlockWarnings: [],

        // Image upload (shared)
        uploading: false,
        uploadError: '',

        // Draft
        draft: null,

        // Edit page flow (B screens)
        selectedPage: null,
        selectedOwnerType: 'page',
        pageBlocks: [],          // tree-structured (built from flat API response)
        pageBlocksLoading: false,
        pageBlocksError: '',
        blocksBackScreen: 'page-select',

        // Block editing / creating
        selectedBlock: null,
        blockEditData: {},
        blockEditConfig: {},
        blockSaving: false,
        blockSaveError: '',
        editMode: 'edit',        // 'edit' | 'create'
        editParentBlock: null,   // parent block when adding a child
        editBlockTypeKey: null,  // block_key selected from catalog

        // Block delete confirmation
        deleteBlockTarget: null,

        // Block catalog (picker)
        catalogContext: null,    // null = top-level, block = adding child

        // Edit menu flow (C screens)
        selectedMenu: null,
        menuItems: [],
        menuItemsLoading: false,
        menuItemsError: '',
        menuItemsSaving: false,
        menuSaveError: '',
        newItemLabel: '',
        newItemUrl: '',
        deleteItemTarget: null,

        // ── Expose utility for partials ───────────────────────────────────
        humanizeKey,
    };

    // ── Feature modules ───────────────────────────────────────────────────
    assignModule(instance, navigation);
    assignModule(instance, entryPublish);
    assignModule(instance, blocks);
    assignModule(instance, menu);
    assignModule(instance, uploads);
    assignModule(instance, draft);

    return instance;
}

// `navigation` exposes getters (steps, currentStepSchema, ...). Object spread /
// Object.assign both invoke getters immediately and copy their *value*, which
// breaks reactivity (and throws, since `this` isn't bound yet at spread time).
// Object.defineProperties + getOwnPropertyDescriptors copies the accessor
// descriptors themselves, so they keep working once merged into the instance.
function assignModule(target, source) {
    Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));
    return target;
}
