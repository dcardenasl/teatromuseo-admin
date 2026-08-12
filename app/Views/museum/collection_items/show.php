<?php
$collectionItem = $collectionItem ?? [];
$languages = $languages ?? [];
$categories = is_array($categories ?? null) ? $categories : [];
$langCodeMap = [];
foreach ($languages as $language) {
    if (is_array($language) && isset($language['id'], $language['code'])) {
        $langCodeMap[(int) $language['id']] = strtoupper((string) $language['code']);
    }
}
$translationByLang = [];
$translations = is_array($collectionItem['translations'] ?? null) ? $collectionItem['translations'] : [];
foreach ($translations as $translation) {
    if (is_array($translation)) {
        $langId = (int) ($translation['language_id'] ?? 0);
        if ($langId > 0) {
            $translationByLang[$langId] = $translation;
        } else {
            $loc = strtolower((string) ($translation['locale'] ?? $translation['language_code'] ?? ''));
            foreach ($languages as $l) {
                if (is_array($l) && strtolower((string) ($l['code'] ?? '')) === $loc) {
                    $translationByLang[(int) $l['id']] = $translation;
                    break;
                }
            }
        }
    }
}
$collectionName = (string) ($collectionItem['name'] ?? $collectionItem['title'] ?? $collectionItem['id'] ?? lang('Museum.collection_items_details'));
$inventoryCode = trim((string) ($collectionItem['inventory_code'] ?? ''));
$categoryLabel = trim((string) ($categories[(string) ($collectionItem['category_id'] ?? '')] ?? ($collectionItem['category_id'] ?? '')));
$publicationStatus = strtolower((string) ($collectionItem['status'] ?? ''));
$publicationStatusLabels = [
    'draft' => lang('Pages.status_draft'),
    'published' => lang('Pages.status_published'),
    'archived' => lang('Pages.status_archived'),
];
$publicationStatusClasses = [
    'draft' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'published' => 'bg-green-50 text-green-700 ring-green-200',
    'archived' => 'bg-gray-100 text-gray-600 ring-gray-200',
];
$publicSlug = trim((string) ($collectionItem['slug'] ?? ''));
$headerSubtitleParts = [];
if ($inventoryCode !== '') {
    $headerSubtitleParts[] = lang('Museum.field_inventory_code') . ': ' . $inventoryCode;
}
if ($publicSlug !== '') {
    $headerSubtitleParts[] = lang('Museum.field_slug') . ': ' . $publicSlug;
}
if ($categoryLabel !== '') {
    $headerSubtitleParts[] = lang('Museum.field_category_id') . ': ' . $categoryLabel;
}
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($collectionItem)): ?>
    <?php $itemId = (string) ($collectionItem['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.museum.collection_items'),
        'backLabel' => 'Museum.collection_items_title',
        'eyebrow' => 'Museum.collection_items_details',
        'title' => $collectionName,
        'subtitle' => $headerSubtitleParts !== [] ? implode(' · ', $headerSubtitleParts) : null,
    ]) ?>

    <?php if (!empty($languages)): ?>
        <?= view('components/table/translation_status_panel', [
            'languages' => $languages,
            'translations' => $translations,
            'requiredFields' => ['name'],
            'sourceFields' => $collectionItem,
            'sourceUpdatedAt' => $collectionItem['updated_at'] ?? null,
            'editUrlTemplate' => route_to('admin.museum.collection_items.edit', $itemId),
            'canEdit' => has_permission('catalog.collectionItem.update'),
        ]) ?>
    <?php endif; ?>

    <?php ob_start(); ?>
    <div class="space-y-6">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('Museum.collection_items_details')) ?></h3>
            </div>
            <dl class="divide-y divide-gray-100">
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_category_id',
                    'value' => $categoryLabel !== '' ? $categoryLabel : '—'
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_inventory_code',
                    'value' => $inventoryCode !== '' ? '<span class="font-mono text-xs text-gray-700">' . esc($inventoryCode) . '</span>' : '—',
                    'isHtml' => true,
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_status',
                    'value' => isset($publicationStatusLabels[$publicationStatus])
                        ? '<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset ' . esc($publicationStatusClasses[$publicationStatus]) . '">' . esc($publicationStatusLabels[$publicationStatus]) . '</span>'
                        : ($collectionItem['status'] ?? '—'),
                    'isHtml' => true,
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_origin',
                    'value' => $collectionItem['origin'] ?? '—'
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_period',
                    'value' => $collectionItem['period'] ?? '—'
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_creator',
                    'value' => $collectionItem['creator'] ?? '—'
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_materials',
                    'value' => $collectionItem['materials'] ?? '—'
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_dimensions',
                    'value' => $collectionItem['dimensions'] ?? '—'
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_show_in_totem',
                    'value' => view('components/table/boolean_cell', ['value' => $collectionItem['show_in_totem'] ?? false]),
                    'isHtml' => true
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_is_active',
                    'value' => view('components/table/boolean_cell', ['value' => $collectionItem['is_active'] ?? false]),
                    'isHtml' => true
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'TableColumns.created_at',
                    'value' => $collectionItem['created_at'] ?? '—',
                ]) ?>
            </dl>
        </section>

        <!-- Multilingual Content Translations Section -->
        <?php if (!empty($languages)): ?>
            <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700 mb-4"><?= esc(lang('Pages.translations_title')) ?></h3>
                <div class="space-y-4">
                    <?php foreach ($languages as $language):
                        $tLangId = (int) ($language['id'] ?? 0);
                        $langCode = strtoupper((string) ($language['code'] ?? ''));
                        $t = $translationByLang[$tLangId] ?? [];
                        if (empty($t) && !empty($language['is_default'])) {
                            $t = [
                                'name' => $collectionItem['name'] ?? '',
                                'summary' => $collectionItem['summary'] ?? '',
                                'curiosidad' => $collectionItem['curiosidad'] ?? '',
                                'contenido' => $collectionItem['contenido'] ?? '',
                                'physical_description' => $collectionItem['physical_description'] ?? '',
                                'ubicacion' => $collectionItem['ubicacion'] ?? '',
                            ];
                        }
                        ?>
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/50">
                            <div class="text-xs font-semibold text-brand-700 mb-3 flex items-center justify-between gap-2 border-b border-gray-200 pb-2">
                                <span class="flex items-center gap-1.5">
                                    <span class="font-bold text-gray-900"><?= esc($langCode) ?></span>
                                    <span>(<?= esc($language['native_name'] ?? $language['name'] ?? '') ?>)</span>
                                    <?php if (!empty($language['is_default'])): ?>
                                        <span class="text-brand-400">★</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 block text-xs font-medium"><?= esc(lang('Museum.field_name')) ?></span>
                                    <span class="text-gray-900 font-semibold"><?= esc($t['name'] ?? '—') ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block text-xs font-medium"><?= esc(lang('Museum.field_ubicacion')) ?></span>
                                    <span class="text-gray-900"><?= esc($t['ubicacion'] ?? '—') ?></span>
                                </div>
                                <div class="md:col-span-2">
                                    <span class="text-gray-500 block text-xs font-medium"><?= esc(lang('Museum.field_summary')) ?></span>
                                    <span class="text-gray-800"><?= esc($t['summary'] ?? '—') ?></span>
                                </div>
                                <?php if (!empty($t['curiosidad'])): ?>
                                <div class="md:col-span-2">
                                    <span class="text-gray-500 block text-xs font-medium"><?= esc(lang('Museum.field_curiosidad')) ?></span>
                                    <span class="text-gray-800 italic"><?= esc($t['curiosidad']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($t['contenido'])): ?>
                                <div class="md:col-span-2">
                                    <span class="text-gray-500 block text-xs font-medium"><?= esc(lang('Museum.field_contenido')) ?></span>
                                    <div class="text-gray-800 text-xs mt-1 prose max-w-none"><?= $t['contenido'] ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('catalog.collectionItem.update')): ?>
        <a href="<?= route_to('admin.museum.collection_items.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>
    <?php endif; ?>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php $dangerContent = ''; ?>
    <?php if (has_permission('catalog.collectionItem.delete')): ?>
        <?php ob_start(); ?>
        <form method="post" action="<?= route_to('admin.museum.collection_items.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($collectionItem['name'] ?? $collectionItem['title'] ?? $collectionItem['id'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        </form>
        <?php $dangerContent = ob_get_clean(); ?>
    <?php endif; ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]),
    ]) ?>
<?php endif; ?>
