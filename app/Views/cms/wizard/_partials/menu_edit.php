<?php /* Wizard — C screens: Menu selection and item editing */ ?>

<!-- ── SCREEN: MENU SELECT (C1) ── -->
<div x-show="screen === 'menu-select'" x-cloak class="space-y-4">
    <div>
        <h2 class="text-lg font-semibold text-gray-900"><?= lang('Wizard.menu_select_heading') ?></h2>
        <p class="text-sm text-gray-500"><?= lang('Wizard.no_menus') ?></p>
    </div>
    <div x-show="(config?.menus ?? []).length === 0"
         class="rounded-xl border border-dashed border-gray-200 bg-white py-10 text-center text-sm text-gray-400 shadow-sm">
        <?= lang('Wizard.no_menus') ?>
    </div>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <template x-for="menu in (config?.menus ?? [])" :key="menu.id">
            <button @click="selectMenu(menu)"
                    class="flex min-h-[110px] flex-col items-start justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-brand-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-500">
                <span class="text-2xl">🔗</span>
                <span class="text-sm font-semibold text-gray-900" x-text="menu.name || menu.menu_key || strings.menu_fallback"></span>
            </button>
        </template>
    </div>
    <button @click="screen = 'home'" class="btn-secondary text-sm"><?= lang('Wizard.btn_back') ?></button>
</div>

<!-- ── SCREEN: MENU ITEMS (C2) ── -->
<div x-show="screen === 'menu-items'" x-cloak>
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <button @click="screen = 'menu-select'" class="btn-secondary text-sm"><?= lang('Wizard.btn_back') ?></button>
        <div>
            <h2 class="text-lg font-semibold text-gray-900" x-text="selectedMenu?.name || selectedMenu?.menu_key || strings.menu_fallback"></h2>
            <p class="text-sm text-gray-500"><?= lang('Wizard.items_loading') ?></p>
        </div>
    </div>

    <div x-show="menuItemsLoading" class="text-center py-8 text-gray-400">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600 mx-auto mb-2"></div>
        <p class="text-sm"><?= lang('Wizard.items_loading') ?></p>
    </div>

    <p x-show="menuItemsError" class="text-red-600 text-sm mb-4" x-text="menuItemsError"></p>

    <div class="space-y-2" x-show="!menuItemsLoading">
        <template x-for="(item, idx) in menuItems" :key="item.id">
            <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm group">
                <div class="flex flex-col gap-0.5">
                    <button @click="moveItem(idx, -1)" :disabled="idx === 0"
                            class="text-gray-300 hover:text-gray-600 disabled:opacity-30 text-xs leading-none">▲</button>
                    <button @click="moveItem(idx, 1)" :disabled="idx === menuItems.length - 1"
                            class="text-gray-300 hover:text-gray-600 disabled:opacity-30 text-xs leading-none">▼</button>
                </div>
                <div class="flex-1 min-w-0">
                    <input type="text"
                           x-model="item._label"
                           @blur="patchItem(item)"
                           placeholder="<?= lang('Wizard.menu_item_label_placeholder') ?>"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                    <input type="text"
                           x-model="item._url"
                           @blur="patchItem(item)"
                           placeholder="<?= lang('Wizard.menu_item_url_placeholder') ?>"
                           class="mt-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-xs text-gray-700 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                </div>
                <button @click="confirmDeleteItem(item)"
                        class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 text-xs transition-opacity">✕</button>
            </div>
        </template>

        <!-- Add item -->
        <div class="mt-4 border-t pt-4">
            <p class="text-xs font-medium text-gray-500 mb-2"><?= lang('Wizard.add_item_heading') ?></p>
            <div class="grid gap-2 md:grid-cols-[1fr_1fr_auto]">
                <input type="text" x-model="newItemLabel"
                       placeholder="<?= lang('Wizard.menu_item_label_placeholder') ?>"
                       class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                <input type="text" x-model="newItemUrl"
                       placeholder="<?= lang('Wizard.menu_item_url_placeholder') ?>"
                       class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500" />
                <button @click="addItem()" :disabled="!newItemLabel || menuItemsSaving"
                        class="btn-primary text-sm whitespace-nowrap"><?= lang('Wizard.btn_add_item') ?></button>
            </div>
        </div>
    </div>

    <!-- Delete confirm modal -->
    <div x-show="deleteItemTarget" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-xl max-w-sm w-full mx-4">
            <h3 class="font-bold mb-2"><?= lang('Wizard.delete_item_title') ?></h3>
            <p class="text-sm text-gray-500 mb-4" x-text="deleteConfirmText()"></p>
            <div class="flex gap-3 justify-end">
                <button @click="deleteItemTarget = null" class="btn-secondary text-sm"><?= lang('Wizard.btn_cancel') ?></button>
                <button @click="deleteItem()" class="btn-danger text-sm"><?= lang('Wizard.btn_delete') ?></button>
            </div>
        </div>
    </div>

    <p x-show="menuSaveError" class="text-red-600 text-sm mt-3" x-text="menuSaveError"></p>

    <div class="mt-6 flex gap-3">
        <button @click="screen = 'home'" class="btn-secondary"><?= lang('Wizard.btn_back_panel') ?></button>
        <button @click="saveMenuOrder()" :disabled="menuItemsSaving" class="btn-primary">
            <span x-show="!menuItemsSaving"><?= lang('Wizard.btn_save_order') ?></span>
            <span x-show="menuItemsSaving"><?= lang('Wizard.btn_saving') ?></span>
        </button>
    </div>
</div>
