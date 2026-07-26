<?php
$menu = $menu ?? [];
$item = $item ?? [];
$itemTranslations = [];
if (! empty($item['translations']) && is_array($item['translations'])) {
    foreach ($item['translations'] as $t) {
        if (! is_array($t)) {
            continue;
        }
        $itemTranslations[(int) $t['language_id']] = $t;
    }
}
$resolveItemTranslation = static function (array $lang) use ($itemTranslations): array {
    $langId = (int) ($lang['id'] ?? 0);
    if (isset($itemTranslations[$langId])) {
        return $itemTranslations[$langId];
    }

    return [];
};
$linkType = old('link_type', 'page');
$selectedPageId = old('page_id', '');
$selectedEntryId = old('entry_id', '');
$selectedCollectionId = old('collection_id', '');

$defaultLang = null;
foreach ($languages as $lang) {
    if (!empty($lang['is_default'])) {
        $defaultLang = $lang;
        break;
    }
}
if (!$defaultLang && !empty($languages)) {
    $defaultLang = reset($languages);
}
$defaultLangId = $defaultLang ? (int) $defaultLang['id'] : 0;
$defaultLangCode = $defaultLang ? (string) $defaultLang['code'] : '';
$translateUrl = route_to('admin.cms.translate');

// Build ordered parent options with indentation
if (! function_exists('buildParentOptions')) {
    function buildParentOptions(array $items, ?int $parentId = null, int $depth = 0): array
    {
        $options = [];
        foreach ($items as $item) {
            $pid = isset($item['parent_id']) && $item['parent_id'] !== '' ? (int) $item['parent_id'] : null;
            if ($pid === $parentId) {
                $prefix = str_repeat('  ', $depth) . ($depth > 0 ? '└ ' : '');
                $label = $item['label'] ?? 'Item #' . $item['id'];
                if (! empty($item['translations']) && is_array($item['translations'])) {
                    foreach ($item['translations'] as $translation) {
                        if (is_array($translation) && ! empty($translation['label'])) {
                            $label = (string) $translation['label'];
                            break;
                        }
                    }
                }
                $options[] = ['id' => (string) $item['id'], 'label' => $prefix . $label];
                $children = buildParentOptions($items, (int) $item['id'], $depth + 1);
                foreach ($children as $child) {
                    $options[] = $child;
                }
            }
        }
        return $options;
    }
}
$parentOptions = buildParentOptions($items);
$suggestedSortOrder = count($items);
?>

<div class="mb-4 flex items-center gap-2 text-sm">
    <a href="<?= route_to('admin.cms.menus.show', $menuId) ?>" class="text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('Menus.menus_back_to_detail')) ?></a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-500 font-mono"><?= esc($menu['menu_key'] ?? '') ?></span>
</div>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden" x-data="menuItemForm('<?= esc($linkType) ?>')">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-900"><?= esc(lang('Menus.items_create_title')) ?></h3>
        </div>

        <?php if (session()->has('error')) : ?>
            <div class="mx-6 mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">
                <?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= route_to('admin.cms.menus.items.store', $menuId) ?>" method="post" class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
            <?= csrf_field() ?>
            <input type="hidden" name="menu_id" value="<?= esc($menuId) ?>">

            <div class="space-y-6 lg:col-span-2">
            <!-- Category URL Autogenerator Assistant -->
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/50 p-5 space-y-4" x-show="linkType === 'custom_url'" x-cloak>
                <div class="flex items-center gap-2">
                    <?= ui_icon('sparkles', 'h-4 w-4 text-brand-500') ?>
                    <h4 class="text-sm font-semibold text-gray-700">Asistente: Enlace de Categoría</h4>
                </div>
                <p class="text-xs text-gray-500">Selecciona una colección y una categoría para autocompletar las URLs y etiquetas localizadas.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Colección</label>
                        <select x-model="selectedColId" @change="onCollectionChange" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="">-- Selecciona una Colección --</option>
                            <template x-for="col in categoryOptions" :key="col.id">
                                <option :value="col.id" x-text="col.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Categoría</label>
                        <select x-model="selectedCatId" @change="onCategoryChange" :disabled="!selectedColId" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:opacity-50 disabled:bg-gray-100">
                            <option value="">-- Selecciona una Categoría --</option>
                            <template x-for="cat in (activeCol ? activeCol.categories : [])" :key="cat.id">
                                <option :value="cat.id" x-text="(cat.translations && Object.values(cat.translations).find(t => t.name)?.name) || cat.name || 'Categoría #' + cat.id"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Translations: labels per language -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <?= ui_icon('languages', 'h-4 w-4 text-gray-400') ?>
                    <h4 class="text-sm font-semibold text-gray-700"><?= esc(lang('Menus.items_translations_title')) ?></h4>
                </div>
                <div x-data="langTabs(<?= $defaultLangId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
                    <div class="flex items-center justify-between border-b border-gray-200 mb-4">
                        <div class="flex gap-0.5" role="tablist">
                            <?php foreach ($languages as $lang): ?>
                                <button type="button"
                                    role="tab"
                                    @click="setTab(<?= (int) $lang['id'] ?>)"
                                    :aria-selected="isActive(<?= (int) $lang['id'] ?>)"
                                    :class="isActive(<?= (int) $lang['id'] ?>) ? 'border-brand-600 text-brand-700 bg-brand-50/40' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                                    <?= esc(strtoupper($lang['code'])) ?>
                                    <?php if (!empty($lang['is_default'])): ?>
                                        <span class="ml-1 text-brand-400">★</span>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($translateTargets)): ?>
                        <button type="button"
                            @click="autoTranslateAll(<?= esc(json_encode($translateTargets, JSON_THROW_ON_ERROR), 'attr') ?>)"
                            :disabled="translating || translatingAll"
                            class="mb-px inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-3 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                            <span x-show="!translatingAll"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_all')) ?></span>
                            <span x-show="translatingAll" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <span x-text="translateAllProgress"></span></span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Translate error message -->
                    <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2"></p>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 overflow-hidden divide-y divide-gray-200">
                        <?php foreach ($languages as $key => $lang): ?>
                            <?php
                            $lang = is_array($lang) ? $lang : [];
                            $langId = isset($lang['id']) ? (int) $lang['id'] : (is_numeric($key) ? (int) $key : 0);
                            if ($langId <= 0) {
                                continue;
                            }
                            $isDefault = !empty($lang['is_default']);
                            $langCode  = strtoupper($lang['code'] ?? '');
                            $langName = (string) ($lang['name'] ?? $lang['label'] ?? $langId);
                            $translation = $resolveItemTranslation($lang);
                            $labelVal = old("translations.{$langId}.label", $translation['label'] ?? '');
                            $urlVal = old("translations.{$langId}.custom_url", $translation['custom_url'] ?? '');

                            $fields = [
                                [
                                    'from' => 'input[name="translations[' . $defaultLangId . '][label]"]',
                                    'to' => 'input[name="translations[' . $langId . '][label]"]'
                                ]
                            ];
                            ?>
                            <div x-show="isActive(<?= $langId ?>)" class="p-4 space-y-3" x-cloak>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="inline-flex items-center rounded-md bg-brand-50 text-brand-700 border border-brand-100 px-2 py-0.5 text-xs font-semibold font-mono"><?= esc($langName) ?></span>
                                    <?php if (!$isDefault): ?>
                                        <button type="button"
                                            @click="autoTranslate('<?= esc($langCode, 'attr') ?>', <?= esc(json_encode($fields, JSON_THROW_ON_ERROR), 'attr') ?>)"
                                            :disabled="translating"
                                            class="inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-2.5 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                                            <span x-show="!translating"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_from_default')) ?></span>
                                            <span x-show="translating" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <?= esc(lang('App.translating')) ?></span>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                        <?= esc(lang('Menus.items_label_label')) ?> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="translations[<?= esc($langId) ?>][label]" value="<?= esc($labelVal) ?>" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                        placeholder="<?= esc(lang('Menus.items_label_placeholder')) ?>">
                                </div>

                                <div x-show="linkType === 'custom_url'" x-cloak>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                                        <?= esc(lang('Menus.items_custom_url_label')) ?>
                                    </label>
                                    <input type="text" name="translations[<?= esc($langId) ?>][custom_url]" value="<?= esc($urlVal) ?>"
                                        x-bind:disabled="linkType !== 'custom_url'"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                        placeholder="<?= esc(lang('Menus.items_custom_url_placeholder')) ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            </div>

            <aside class="space-y-6">
            <!-- Link Type -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_link_type_label')) ?></label>
                <select name="link_type" x-model="linkType" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="page"><?= esc(lang('Menus.items_link_type_page')) ?></option>
                    <option value="entry"><?= esc(lang('Menus.items_link_type_entry')) ?></option>
                    <option value="collection_listing"><?= esc(lang('Menus.items_link_type_collection_listing')) ?></option>
                    <option value="custom_url"><?= esc(lang('Menus.items_link_type_custom_url')) ?></option>
                    <option value="no_link"><?= esc(lang('Menus.items_link_type_no_link')) ?></option>
                </select>
            </div>

            <!-- Target selectors (conditional) -->
            <div x-show="linkType === 'page'" x-cloak>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_target_page_label')) ?> <span class="text-red-500">*</span></label>
                <select name="page_id" x-bind:disabled="linkType !== 'page'" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value=""><?= esc(lang('Menus.items_target_page_placeholder')) ?></option>
                    <?php foreach ($pages as $id => $title): ?>
                        <option value="<?= esc($id) ?>" <?= (string) $selectedPageId === (string) $id ? 'selected' : '' ?>><?= esc($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div x-show="linkType === 'entry'" x-cloak>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_target_entry_label')) ?> <span class="text-red-500">*</span></label>
                <select name="entry_id" x-bind:disabled="linkType !== 'entry'" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value=""><?= esc(lang('Menus.items_target_entry_placeholder')) ?></option>
                    <?php foreach ($entries as $id => $title): ?>
                        <option value="<?= esc($id) ?>" <?= (string) $selectedEntryId === (string) $id ? 'selected' : '' ?>><?= esc($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div x-show="linkType === 'collection_listing'" x-cloak>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_target_collection_label')) ?> <span class="text-red-500">*</span></label>
                <select name="collection_id" x-bind:disabled="linkType !== 'collection_listing'" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value=""><?= esc(lang('Menus.items_target_collection_placeholder')) ?></option>
                    <?php foreach ($collections as $id => $title): ?>
                        <option value="<?= esc($id) ?>" <?= (string) $selectedCollectionId === (string) $id ? 'selected' : '' ?>><?= esc($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Structure: parent -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_parent_label')) ?></label>
                <select name="parent_id" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value=""><?= esc(lang('Menus.items_parent_placeholder')) ?></option>
                    <?php foreach ($parentOptions as $opt): ?>
                        <option value="<?= esc($opt['id']) ?>"><?= esc($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Menus.items_parent_help')) ?></p>
            </div>
            <input type="hidden" name="sort_order" value="<?= esc(old('sort_order', (string) $suggestedSortOrder)) ?>">

            <!-- Appearance: link target + icon + css class -->
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Appearance</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_link_target_label')) ?></label>
                        <select name="link_target" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="_self" <?= old('link_target', '_self') === '_self' ? 'selected' : '' ?>><?= esc(lang('Menus.items_link_target_same')) ?></option>
                            <option value="_blank" <?= old('link_target', '_self') === '_blank' ? 'selected' : '' ?>><?= esc(lang('Menus.items_link_target_new')) ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_icon_label')) ?></label>
                        <input type="text" name="icon" value="<?= esc(old('icon', '')) ?>"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                            placeholder="<?= esc(lang('Menus.items_icon_placeholder')) ?>">
                        <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Menus.items_icon_help')) ?></p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1"><?= esc(lang('Menus.items_css_class_label')) ?></label>
                    <input type="text" name="css_class" value="<?= esc(old('css_class', '')) ?>"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                        placeholder="<?= esc(lang('Menus.items_css_class_placeholder')) ?>">
                </div>
            </div>

            <!-- Active toggle -->
            <?= view('components/form/boolean', [
                'name'      => 'is_active',
                'label'     => 'Menus.items_is_active_label',
                'value'     => old('is_active', '1') !== '0',
                'on_label'  => 'Menus.field_is_active_on',
                'off_label' => 'Menus.field_is_active_off',
            ]) ?>

            <!-- Actions -->
            <?= view('components/display/admin_actions_panel', [
                'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('Menus.items_add')) . '</button>'
                    . '<a href="' . esc(route_to('admin.cms.menus.show', $menuId), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('Menus.items_cancel')) . '</a>',
            ]) ?>
            </aside>
        </form>
    </section>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('menuItemForm', (initialLinkType) => ({
        linkType: initialLinkType,
        categoryOptions: [],
        selectedColId: '',
        selectedCatId: '',
        activeCol: null,
        activeCat: null,
        init() {
            fetch('/admin/cms/menus/category-options')
                .then(r => r.json())
                .then(res => {
                    if (res.ok) {
                        this.categoryOptions = res.data;
                    }
                });
        },
        onCollectionChange() {
            this.selectedCatId = '';
            this.activeCat = null;
            this.activeCol = this.categoryOptions.find(c => c.id == this.selectedColId) || null;
        },
        onCategoryChange() {
            if (!this.activeCol) return;
            this.activeCat = this.activeCol.categories.find(cat => cat.id == this.selectedCatId) || null;
            if (this.activeCat) {
                this.autofillUrls();
            }
        },
        autofillUrls() {
            const languages = <?= json_encode($languages) ?>;
            Object.values(languages).forEach(lang => {
                const langId = lang.id;
                const colSlug = this.activeCol.translations[langId]?.slug || this.activeCol.key;
                const catSlug = this.activeCat.translations[langId]?.slug || '';
                if (colSlug && catSlug) {
                    const url = `/${colSlug}?category=${catSlug}`;
                    const urlInput = document.querySelector(`input[name='translations[${langId}][custom_url]']`);
                    if (urlInput) {
                        urlInput.value = url;
                    }
                    const labelInput = document.querySelector(`input[name='translations[${langId}][label]']`);
                    if (labelInput) {
                        labelInput.value = this.activeCat.translations[langId]?.name || '';
                    }
                }
            });
        }
    }));
});
</script>
