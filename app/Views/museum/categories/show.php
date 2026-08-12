<?php
$category = $category ?? [];
$languages = $languages ?? [];
$langCodeMap = [];
foreach ($languages as $language) {
    if (is_array($language) && isset($language['id'], $language['code'])) {
        $langCodeMap[(int) $language['id']] = strtoupper((string) $language['code']);
    }
}
$translationByLang = [];
$translations = is_array($category['translations'] ?? null) ? $category['translations'] : [];
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
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($category)): ?>
    <?php $itemId = (string) ($category['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.museum.categories'),
        'backLabel' => 'Museum.categories_title',
        'eyebrow' => 'Museum.categories_details',
        'title' => (string) ($category['name'] ?? $category['title'] ?? $category['id'] ?? lang('Museum.categories_details')),
    ]) ?>

    <?php if (!empty($languages)): ?>
        <?= view('components/table/translation_status_panel', [
            'languages' => $languages,
            'translations' => $translations,
            'requiredFields' => ['name'],
            'sourceFields' => $category,
            'sourceUpdatedAt' => $category['updated_at'] ?? null,
            'editUrlTemplate' => route_to('admin.museum.categories.edit', $itemId),
            'canEdit' => has_permission('catalog.category.update'),
        ]) ?>
    <?php endif; ?>

    <?php ob_start(); ?>
    <div class="space-y-6">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('Museum.categories_details')) ?></h3>
            </div>
            <dl class="divide-y divide-gray-100">
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_name',
                    'value' => $category['name'] ?? '—'
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_slug',
                    'value' => $category['slug'] ?? '—'
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'Museum.field_icon',
                    'value' => $category['icon'] ?? '—'
                ]) ?>
                <?= view('components/display/field_row', [
                    'label' => 'TableColumns.created_at',
                    'value' => $category['created_at'] ?? '—',
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
                                'name' => $category['name'] ?? '',
                                'short_description' => $category['short_description'] ?? '',
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
                                <div class="md:col-span-2">
                                    <span class="text-gray-500 block text-xs font-medium"><?= esc(lang('Museum.field_short_description')) ?></span>
                                    <span class="text-gray-800"><?= esc($t['short_description'] ?? '—') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('catalog.category.update')): ?>
        <a href="<?= route_to('admin.museum.categories.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>

        <a href="<?= route_to('admin.museum.categories.reorder') ?>" class="<?= esc(action_button_class('neutral')) ?>">
            <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
            <?= esc(lang('Museum.field_sort_order') ?? lang('App.reorder')) ?>
        </a>
    <?php endif; ?>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php $dangerContent = ''; ?>
    <?php if (has_permission('catalog.category.delete')): ?>
        <?php ob_start(); ?>
        <form method="post" action="<?= route_to('admin.museum.categories.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($category['name'] ?? $category['title'] ?? $category['id'] ?? null), 'js') ?>', () => $el.submit())">
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
