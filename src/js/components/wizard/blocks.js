import { adminFetch } from '../../utils/wizard/adminFetch.js';
import { humanizeKey } from '../../utils/wizard/humanizeKey.js';
import { schemaTypeToUiType } from '../../utils/wizard/schemaTypeToUiType.js';
import { buildBlockTree } from '../../utils/wizard/buildBlockTree.js';
import { normalizeBlockPayload } from '../../utils/wizard/normalizeBlockPayload.js';
import { bootLucideIcons } from '../../utils/lucide.js';

// Fallback for block types with no icon configured — matches the domain's own
// default for newly created block types (see block_types/create.php).
const DEFAULT_BLOCK_ICON = 'layout-template';

// ── Owner (page/entry) block-tree editing — screens B ────────────────────
export const blocks = {
    // ── Block type helpers ────────────────────────────────────────────
    // Icons are a Lucide icon name, sourced from the block type's own `icon`
    // field (set in the canonical "Tipos de Bloque" admin module) — never
    // hardcoded here, so newly created block types get a working icon
    // automatically without touching this file.
    blockIcon(blockKey) {
        return this.blockTypeInfo(blockKey)?.icon || DEFAULT_BLOCK_ICON;
    },

    blockTypeInfo(blockKey) {
        if (!blockKey || !this.config?.block_types) return null;
        return this.config.block_types[blockKey] ?? null;
    },

    blockIsContainer(block) {
        const bkey = block?.block_config?.block_key ?? null;
        const typeInfo = this.blockTypeInfo(bkey);
        // A block is a container if its type is flagged OR if it already has children
        return (typeInfo?.is_container === true) || ((block?._children ?? []).length > 0);
    },

    blockAllowedChildren(block) {
        const bkey = block?.block_config?.block_key ?? null;
        const typeInfo = this.blockTypeInfo(bkey);
        return typeInfo?.allowed_children ?? [];
    },

    addChildLabel(block) {
        const allowed = this.blockAllowedChildren(block);
        if (allowed.length === 0) return '+ ' + this.strings.add_child;
        if (allowed.length === 1) {
            const typeInfo = this.blockTypeInfo(allowed[0]);
            const name = typeInfo?.name ?? humanizeKey(allowed[0]);
            return '+ ' + this.strings.add_child + ' (' + name + ')';
        }
        return '+ ' + this.strings.add_child;
    },

    availableBlockTypes() {
        if (!this.config?.block_types) return [];
        const allTypes = Object.entries(this.config.block_types);

        if (this.catalogContext) {
            const allowed = this.blockAllowedChildren(this.catalogContext);

            if (allowed.length > 0) {
                return allTypes
                    .filter(([key]) => allowed.includes(key))
                    .map(([key, info]) => ({ key, ...info }));
            }

            // allowed_children not configured — infer from existing children's block keys
            const existingKeys = [...new Set(
                (this.catalogContext._children ?? [])
                    .map(c => c.block_config?.block_key)
                    .filter(Boolean)
            )];
            if (existingKeys.length > 0) {
                return allTypes
                    .filter(([key]) => existingKeys.includes(key))
                    .map(([key, info]) => ({ key, ...info }));
            }

            // Last resort: all non-container types
            return allTypes
                .filter(([, info]) => !info.is_container)
                .map(([key, info]) => ({ key, ...info }));
        }

        // Top-level: show block types that support the current owner type
        return allTypes
            .filter(([, info]) => this.selectedOwnerType === 'entry'
                ? info.supports_entries !== false && !info.is_child_only
                : info.supports_pages !== false && !info.is_child_only)
            .map(([key, info]) => ({ key, ...info }));
    },

    nextSortOrder(parentBlock = null) {
        const siblings = parentBlock ? (parentBlock._children ?? []) : this.pageBlocks;
        if (siblings.length === 0) return 0;
        return Math.max(...siblings.map(b => b.sort_order ?? 0)) + 1;
    },

    // ── Block display helpers ─────────────────────────────────────────
    blockLabel(block, idx) {
        if (!block) return '';
        const cfg  = block.block_config ?? {};
        const bkey = cfg.block_key ?? cfg.name ?? null;
        if (bkey) return humanizeKey(bkey);
        return this.strings.block_fallback + ' ' + (block.sort_order ?? (idx + 1));
    },

    blockPreview(block) {
        if (!block) return '';
        const t = (block.translations ?? [])[0];
        if (!t?.block_data) return '';
        const vals = Object.values(t.block_data).filter(v => {
            if (typeof v !== 'string' || !v) return false;
            if (v.startsWith('http') || v.startsWith('/') || /^\d+$/.test(v)) return false;
            return true;
        });
        if (vals.length === 0) return '';
        const plain = vals[0].replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        return plain.substring(0, 60) || '';
    },

    blockThumbUrl(block) {
        if (!block) return null;
        const t = (block.translations ?? [])[0];
        if (!t?.block_data) return null;
        const data = t.block_data;
        // Prefer explicit image URLs first so blocks created before the file-id convention
        // still show a useful thumbnail in the wizard.
        const explicitImageEntry = Object.entries(data).find(([k, v]) => {
            if (typeof v !== 'string' || v.length === 0) return false;
            if (!v.startsWith('http') && !v.startsWith('/')) return false;
            return k === 'image_url' || k.endsWith('_image_url') || /(^|_)(photo|picture|thumbnail|thumb|cover|poster|banner)(_url)?$/i.test(k);
        });
        if (explicitImageEntry) {
            return explicitImageEntry[1];
        }

        // Fallback: pick *_url fields that have a matching *_file_id — that confirms it's an image field,
        // not a link/button URL.
        const entry = Object.entries(data).find(([k, v]) => {
            if (!k.endsWith('_url')) return false;
            if (typeof v !== 'string' || v.length === 0) return false;
            if (!v.startsWith('http') && !v.startsWith('/')) return false;
            const fileIdKey = k.replace(/_url$/, '_file_id');
            return fileIdKey in data;
        });
        return entry ? entry[1] : null;
    },

    blockEditTitle() {
        if (this.editMode === 'create') {
            const typeInfo = this.blockTypeInfo(this.editBlockTypeKey);
            return typeInfo?.name ?? humanizeKey(this.editBlockTypeKey ?? '');
        }
        return this.blockLabel(this.selectedBlock, 0);
    },

    // Returns enriched field descriptors for the block editor
    blockFields() {
        let bkey;
        if (this.editMode === 'create') {
            bkey = this.editBlockTypeKey;
        } else {
            if (!this.selectedBlock) return [];
            bkey = this.selectedBlock.block_config?.block_key ?? null;
        }

        const schemaFields = bkey ? (this.config?.block_types?.[bkey]?.fields ?? null) : null;
        const configFields = bkey ? (this.config?.block_types?.[bkey]?.config_fields ?? null) : null;

        // Config fields (block_config) are non-translatable settings — most are
        // technical (css_class, columns...) and stay out of the simplified Wizard
        // UI on purpose. media_reference is the one exception: it's a shared
        // asset the editor still needs to be able to set, so it's surfaced here
        // alongside content fields, tagged with source: 'config' so saveBlock()
        // knows to write it into block_config instead of block_data.
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

        if (schemaFields && Object.keys(schemaFields).length > 0) {
            return Object.entries(schemaFields).map(([k, def]) => ({
                key:      k,
                label:    def.label ?? humanizeKey(k),
                required: def.required ?? false,
                uiType:   schemaTypeToUiType(def.type ?? '', def.accept ?? '', def.primitive ?? ''),
                options:  def.options ?? [],
                source:   'data',
            })).concat(mediaConfigFields);
        }

        if (this.editMode === 'create') return mediaConfigFields;

        // Fallback: derive from existing block_data keys
        const visibleKeys = Object.keys(this.blockEditData)
            .filter(k => !k.endsWith('_file_id') && !k.endsWith('_url'));
        return visibleKeys.map(k => ({
            key:      k,
            label:    humanizeKey(k),
            required: false,
            uiType:   'textarea',
            options:  [],
            source:   'data',
        })).concat(mediaConfigFields);
    },

    syncRichTextFields() {
        const fields = document.querySelectorAll('[data-wizard-richtext-field]');
        fields.forEach((node) => {
            const key = node?.dataset?.fieldKey;
            if (!key) return;
            const input = node.querySelector('input[type="hidden"]');
            if (!input) return;
            this.blockEditData[key] = input.value ?? '';
        });
    },

    ownerTypeLabel() {
        return this.selectedOwnerType === 'entry'
            ? this.strings.owner_label_entry
            : this.strings.owner_label_page;
    },

    blocksDescription() {
        return this.selectedOwnerType === 'entry'
            ? this.strings.blocks_description_entry
            : this.strings.blocks_description_page;
    },

    emptyBlocksText() {
        return this.selectedOwnerType === 'entry'
            ? this.strings.no_blocks_entry
            : this.strings.no_blocks_page;
    },

    ownerPreviewUrl() {
        if (this.selectedOwnerType !== 'page' || !this.publicSiteUrl || !this.selectedPage?.translations?.length) return '';
        const slug = this.selectedPage.translations.find(t => t?.slug)?.slug || '';
        return slug ? `${this.publicSiteUrl}/${String(slug).replace(/^\//, '')}` : '';
    },

    ownerEditUrl() {
        if (!this.selectedPage?.id) return '';
        const segment = this.selectedOwnerType === 'entry' ? 'entries' : 'pages';
        return `${this.adminCmsBase}/${segment}/${this.selectedPage.id}/edit`;
    },

    ownerBlocksUrl(suffix = '') {
        if (!this.selectedPage?.id) return '';
        const segment = this.selectedOwnerType === 'entry' ? 'entries' : 'pages';
        return `${this.wizardBase}/${segment}/${this.selectedPage.id}/blocks${suffix}`;
    },

    setBlockOwner(owner, ownerType, backScreen = 'page-select') {
        this.selectedPage = owner;
        this.selectedOwnerType = ownerType;
        this.blocksBackScreen = backScreen;
        this.pageBlocks = [];
        this.pageBlocksError = '';
    },

    async openOwnerBlocks(owner, ownerType = 'page', backScreen = 'page-select') {
        this.setBlockOwner(owner, ownerType, backScreen);
        this.pageBlocksLoading = true;
        this.screen = 'page-blocks';
        await this.refreshPageBlocks();
    },

    // ── WIZ-007: Edit page ────────────────────────────────────────────
    goEditPage() {
        if (!this.config) return;
        if ((this.config.pages ?? []).length === 0) {
            this.errorMsg = this.strings.error_no_pages;
            this.screen = 'error';
            return;
        }
        this.screen = 'page-select';
    },

    async selectPage(page) {
        await this.openOwnerBlocks(page, 'page', 'page-select');
    },

    async selectPublishedEntry(entry) {
        await this.openOwnerBlocks(entry, 'entry', 'success');
    },

    async refreshPageBlocks() {
        if (!this.selectedPage) return;
        this.pageBlocksLoading = true;
        this.pageBlocksError = '';
        try {
            const res  = await adminFetch(this.ownerBlocksUrl(), {}, this.csrf);
            const data = await res.json();
            if (!res.ok) throw new Error(data?.message ?? this.strings.error_blocks_load);
            const items = data?.items ?? data?.data ?? (Array.isArray(data) ? data : []);
            this.pageBlocks = buildBlockTree(items);
            this.$nextTick(() => { bootLucideIcons(); });
        } catch (e) {
            this.pageBlocksError = e.message ?? this.strings.error_blocks_load;
        } finally {
            this.pageBlocksLoading = false;
        }
    },

    // ── Block editing ─────────────────────────────────────────────────
    editBlock(block, parentBlock = null) {
        this.selectedBlock  = block;
        this.editMode       = 'edit';
        this.editParentBlock = parentBlock;
        this.editBlockTypeKey = null;
        this.blockSaveError = '';
        this.uploadError    = '';
        const t = (block.translations ?? [])[0];
        this.blockEditData   = t?.block_data ? { ...t.block_data } : {};
        this.blockEditConfig = block.block_config ? { ...block.block_config } : {};
        this.screen = 'block-edit';
    },

    // ── Block catalog ─────────────────────────────────────────────────
    openBlockCatalog(parentBlock = null) {
        this.catalogContext = parentBlock;
        const available = this.availableBlockTypes();

        if (available.length === 0) return;

        // If only one option (e.g. hero_slider allows only slide_banner) — skip catalog
        if (available.length === 1) {
            this.selectBlockType(available[0]);
            return;
        }

        this.screen = 'block-catalog';
        this.$nextTick(() => { bootLucideIcons(); });
    },

    selectBlockType(blockType) {
        this.editMode        = 'create';
        this.editBlockTypeKey = blockType.key;
        this.editParentBlock = this.catalogContext;
        this.selectedBlock   = null;
        this.blockEditData   = {};
        this.blockEditConfig = {};
        this.blockSaveError  = '';
        this.uploadError     = '';
        this.screen = 'block-edit';
    },

    // ── Block CRUD ────────────────────────────────────────────────────
    async saveBlock() {
        this.syncRichTextFields();
        if (this.editMode === 'create') {
            await this._createBlock();
            return;
        }
        await this._updateBlock();
    },

    async _updateBlock() {
        if (!this.selectedBlock || !this.selectedPage) return;
        this.blockSaving    = true;
        this.blockSaveError = '';
        try {
            const t = (this.selectedBlock.translations ?? [])[0] ?? {};
            const blockData = normalizeBlockPayload(this.blockEditData);
            // is_active ensures data is never empty after the domain extracts translations,
            // which would otherwise trigger BaseCrudService's noFieldsToUpdate check.
            const payload = {
                is_active:    true,
                block_config: this.blockEditConfig ?? {},
                translations: [{
                    language_id:  t.language_id ?? (this.defaultLangId || this.resolveDefaultLanguageId()),
                    block_data:   blockData,
                    is_published: t.is_published ?? true,
                }],
            };
            const res  = await adminFetch(
                this.ownerBlocksUrl(`/${this.selectedBlock.id}`),
                { method: 'POST', body: JSON.stringify(payload) },
                this.csrf
            );
            const data = await res.json();
            if (!res.ok) throw new Error(data?.message ?? this.strings.error_block_save);
            this.screen = 'block-saved';
        } catch (e) {
            this.blockSaveError = e.message ?? this.strings.error_block_save;
        } finally {
            this.blockSaving = false;
        }
    },

    async _createBlock() {
        if (!this.editBlockTypeKey || !this.selectedPage) return;
        this.blockSaving    = true;
        this.blockSaveError = '';
        try {
            const typeInfo = this.blockTypeInfo(this.editBlockTypeKey);
            if (!typeInfo?.id) throw new Error(this.strings.error_block_type_missing);
            const blockData = normalizeBlockPayload(this.blockEditData);

            const payload = {
                block_id:           typeInfo.id,
                owner_type:         this.selectedOwnerType,
                owner_id:           this.selectedPage.id,
                parent_instance_id: this.editParentBlock?.id ?? null,
                sort_order:         this.nextSortOrder(this.editParentBlock),
                is_active:          true,
                block_config:       this.blockEditConfig ?? {},
                translations: [{
                    language_id:  this.defaultLangId || this.resolveDefaultLanguageId(),
                    block_data:   blockData,
                    is_published: true,
                }],
            };

            const res  = await adminFetch(
                this.ownerBlocksUrl(),
                { method: 'POST', body: JSON.stringify(payload) },
                this.csrf
            );
            const data = await res.json();
            if (!res.ok) throw new Error(data?.message ?? this.strings.error_block_save);

            // Reset create state and refresh tree
            this.editMode        = 'edit';
            this.editBlockTypeKey = null;
            this.editParentBlock = null;
            await this.refreshPageBlocks();
            this.screen = 'block-saved';
        } catch (e) {
            this.blockSaveError = e.message ?? this.strings.error_block_save;
        } finally {
            this.blockSaving = false;
        }
    },

    confirmDeleteBlock(block) {
        this.deleteBlockTarget = block;
    },

    async deleteBlock() {
        const block = this.deleteBlockTarget;
        if (!block || !this.selectedPage) return;
        this.deleteBlockTarget = null;
        try {
            const res = await adminFetch(
                this.ownerBlocksUrl(`/${block.id}/delete`),
                { method: 'POST' },
                this.csrf
            );
            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                this.pageBlocksError = data?.message ?? this.strings.error_block_delete;
                return;
            }
            await this.refreshPageBlocks();
        } catch {
            this.pageBlocksError = this.strings.error_block_delete;
        }
    },

    async moveBlock(block, direction, parentBlock = null) {
        const siblings = parentBlock ? (parentBlock._children ?? []) : this.pageBlocks;
        const idx      = siblings.findIndex(b => b.id === block.id);
        if (idx < 0) return;
        const targetIdx = idx + direction;
        if (targetIdx < 0 || targetIdx >= siblings.length) return;

        const targetBlock = siblings[targetIdx];
        const orderA = targetBlock.sort_order ?? targetIdx;
        const orderB = block.sort_order ?? idx;

        try {
            await Promise.all([
                adminFetch(
                    this.ownerBlocksUrl(`/${block.id}`),
                    { method: 'POST', body: JSON.stringify({ sort_order: orderA }) },
                    this.csrf
                ),
                adminFetch(
                    this.ownerBlocksUrl(`/${targetBlock.id}`),
                    { method: 'POST', body: JSON.stringify({ sort_order: orderB }) },
                    this.csrf
                ),
            ]);
            await this.refreshPageBlocks();
        } catch { /* silent — tree not refreshed, next reload will fix */ }
    },
};
