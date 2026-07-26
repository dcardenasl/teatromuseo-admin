<?php /* Wizard — B2: Page layout — hierarchical block tree */ ?>

<!-- ── SCREEN: PAGE LAYOUT (B2) ── -->
<div x-show="screen === 'page-blocks'" x-cloak>

    <!-- Breadcrumb header -->
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <button @click="screen = blocksBackScreen || 'page-select'" class="text-sm text-gray-500 hover:text-gray-700"><?= lang('Wizard.btn_back') ?></button>
        <span class="text-gray-300">/</span>
        <div class="min-w-0">
            <div class="flex items-center gap-2">
        <h2 class="text-lg font-semibold text-gray-900 truncate" x-text="selectedPage?.title || selectedPage?.slug || strings.content_fallback"></h2>
                <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[11px] font-semibold text-gray-500" x-text="ownerTypeLabel()"></span>
            </div>
            <p class="text-xs text-gray-400 truncate" x-text="selectedPage?.slug ? '/' + selectedPage.slug : ''"></p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <a x-show="ownerPreviewUrl() && selectedOwnerType === 'page'"
               :href="ownerPreviewUrl()"
               target="_blank"
               rel="noopener noreferrer"
               class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <?= esc(lang('Pages.blocks_view_page')) ?>
            </a>
            <a x-show="ownerEditUrl() && selectedOwnerType === 'entry'"
               :href="ownerEditUrl()"
               class="rounded-lg border border-brand-200 bg-white px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-50 transition-colors">
                <?= lang('Wizard.btn_edit_entry') ?>
            </a>
        </div>
    </div>

    <!-- Loading state -->
    <div x-show="pageBlocksLoading" class="rounded-xl border border-gray-200 bg-white py-10 text-center text-gray-400 shadow-sm">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600 mx-auto mb-2"></div>
        <p class="text-sm"><?= lang('Wizard.blocks_loading') ?></p>
    </div>

    <!-- Error state -->
    <p x-show="pageBlocksError" class="text-red-600 text-sm mb-4" x-text="pageBlocksError"></p>

    <!-- Empty state -->
    <div x-show="!pageBlocksLoading && pageBlocks.length === 0 && !pageBlocksError"
         class="rounded-xl border border-dashed border-gray-200 bg-white py-12 text-center text-gray-400 text-sm shadow-sm">
        <div class="text-4xl mb-3">📭</div>
        <p x-text="emptyBlocksText()"></p>
        <p class="mt-2 text-xs text-gray-400" x-text="blocksDescription()"></p>
    </div>

    <!-- Block tree -->
    <div class="space-y-3" x-show="!pageBlocksLoading && pageBlocks.length > 0">
        <template x-for="(block, idx) in pageBlocks" :key="block.id">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">

                <!-- ── Top-level block header ── -->
                <div class="flex items-center gap-3 px-4 py-3">
                    <!-- Thumbnail or icon -->
                    <div class="shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                        <img x-show="blockThumbUrl(block)"
                             :src="blockThumbUrl(block)"
                             class="w-full h-full object-cover"
                             alt="">
                        <i x-show="!blockThumbUrl(block)" :data-lucide="blockIcon(block.block_config?.block_key)" class="h-5 w-5 text-gray-500"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm text-gray-800" x-text="blockLabel(block, idx)"></p>
                        <p class="text-xs text-gray-400 mt-0.5 truncate" x-text="blockPreview(block)"></p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1 shrink-0">
                        <!-- Reorder -->
                        <div class="flex flex-col gap-0.5">
                            <button @click="moveBlock(block, -1)"
                                    :disabled="idx === 0"
                                    class="text-gray-300 hover:text-gray-600 disabled:opacity-20 text-xs leading-none px-1 py-0.5 rounded hover:bg-gray-100"
                                    title="Subir">▲</button>
                            <button @click="moveBlock(block, 1)"
                                    :disabled="idx === pageBlocks.length - 1"
                                    class="text-gray-300 hover:text-gray-600 disabled:opacity-20 text-xs leading-none px-1 py-0.5 rounded hover:bg-gray-100"
                                    title="Bajar">▼</button>
                        </div>
                        <!-- Edit -->
                        <button @click="editBlock(block)"
                                class="rounded-lg border border-brand-300 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-50 transition-colors">
                            <?= lang('Wizard.btn_edit_block') ?>
                        </button>
                        <!-- Delete -->
                        <button @click="confirmDeleteBlock(block)"
                                class="rounded-lg border border-red-200 px-2 py-1.5 text-xs text-red-500 hover:bg-red-50 hover:border-red-300 transition-colors"
                                title="Eliminar bloque">🗑</button>
                    </div>
                </div>

                <!-- ── Children section (container blocks) ── -->
                <div x-show="blockIsContainer(block) || (block._children && block._children.length > 0)"
                     class="border-t border-gray-100 bg-gray-50 px-4 py-3">

                    <!-- Child blocks list -->
                    <div class="space-y-2 mb-3" x-show="block._children && block._children.length > 0">
                        <template x-for="(child, cidx) in (block._children ?? [])" :key="child.id">
                            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2.5">
                                <!-- Thumbnail or icon -->
                                <div class="shrink-0 w-10 h-10 rounded-md overflow-hidden bg-gray-100 flex items-center justify-center">
                                    <img x-show="blockThumbUrl(child)"
                                         :src="blockThumbUrl(child)"
                                         class="w-full h-full object-cover"
                                         alt="">
                                    <i x-show="!blockThumbUrl(child)" :data-lucide="blockIcon(child.block_config?.block_key)" class="h-4 w-4 text-gray-500"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 font-medium truncate" x-text="blockLabel(child, cidx)"></p>
                                    <p class="text-xs text-gray-400 truncate" x-text="blockPreview(child)"></p>
                                </div>
                                <!-- Child actions -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <div class="flex flex-col gap-0.5">
                                        <button @click="moveBlock(child, -1, block)"
                                                :disabled="cidx === 0"
                                                class="text-gray-300 hover:text-gray-600 disabled:opacity-20 text-xs leading-none px-1 py-0.5 rounded hover:bg-gray-100"
                                                title="Subir">▲</button>
                                        <button @click="moveBlock(child, 1, block)"
                                                :disabled="cidx === (block._children ?? []).length - 1"
                                                class="text-gray-300 hover:text-gray-600 disabled:opacity-20 text-xs leading-none px-1 py-0.5 rounded hover:bg-gray-100"
                                                title="Bajar">▼</button>
                                    </div>
                                    <button @click="editBlock(child, block)"
                                            class="rounded-lg border border-brand-300 px-3 py-1 text-xs font-medium text-brand-700 hover:bg-brand-50 transition-colors">
                                        <?= lang('Wizard.btn_edit_block') ?>
                                    </button>
                                    <button @click="confirmDeleteBlock(child)"
                                            class="rounded-lg border border-red-200 px-2 py-1 text-xs text-red-500 hover:bg-red-50 hover:border-red-300 transition-colors"
                                            title="Eliminar">🗑</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Add child button -->
                    <button @click="openBlockCatalog(block)"
                            class="w-full rounded-lg border-2 border-dashed border-gray-200 px-3 py-2 text-xs text-gray-400 hover:border-brand-300 hover:text-brand-600 transition-colors text-left"
                            x-text="addChildLabel(block)">
                    </button>
                </div>

            </div>
        </template>
    </div>

    <!-- Add top-level block button -->
    <div class="mt-4" x-show="!pageBlocksLoading">
        <button @click="openBlockCatalog(null)"
                class="w-full rounded-xl border-2 border-dashed border-gray-200 py-3 text-sm text-gray-400 hover:border-brand-400 hover:text-brand-600 transition-colors font-medium">
            + <?= lang('Wizard.add_block') ?>
        </button>
    </div>

    <!-- ── Delete block confirmation modal ── -->
    <div x-show="deleteBlockTarget" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-xl max-w-sm w-full mx-4">
            <h3 class="font-bold mb-2"><?= lang('Wizard.delete_block_title') ?></h3>
            <p class="text-sm text-gray-500 mb-1">
                <span x-text="blockLabel(deleteBlockTarget, 0)"></span>
            </p>
            <p class="text-xs text-gray-400 mb-4"><?= lang('Wizard.delete_block_confirm') ?></p>
            <div class="flex gap-3 justify-end">
                <button @click="deleteBlockTarget = null" class="btn-secondary text-sm"><?= lang('Wizard.btn_cancel') ?></button>
                <button @click="deleteBlock()" class="btn-danger text-sm"><?= lang('Wizard.btn_delete') ?></button>
            </div>
        </div>
    </div>

</div>
