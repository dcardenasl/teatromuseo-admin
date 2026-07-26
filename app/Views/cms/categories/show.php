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
foreach (($category['translations'] ?? []) as $translation) {
    $translationByLang[(int) ($translation['language_id'] ?? 0)] = $translation;
}
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($category)): ?>
    <?php $itemId = (string) ($category['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.cms.categories'),
        'backLabel' => 'Categories.categories_title',
        'eyebrow' => 'Categories.categories_details',
        'title' => (string) (($category['translations'][0]['name'] ?? null) ?: ($category['name'] ?? $category['slug'] ?? '—')),
        'badge' => view('components/table/boolean_cell', ['value' => $category['is_active'] ?? false]),
    ]) ?>

    <?= view('components/table/translation_status_panel', ['languages' => $languages, 'translations' => $category['translations'] ?? [], 'requiredFields' => ['name', 'slug'], 'sourceFields' => $category, 'sourceUpdatedAt' => $category['updated_at'] ?? null, 'editUrlTemplate' => route_to('admin.cms.categories.edit', $itemId)]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('Categories.translations_title') ?></h3>

        <?php if (! empty($category['translations']) && is_array($category['translations'])): ?>
            <div class="mt-4 space-y-4">
                <?php foreach ($languages as $language):
                    $tLangId = (int) ($language['id'] ?? 0);
                    $t = $translationByLang[$tLangId] ?? [];
                    $tState = \App\Modules\Cms\Support\TranslationStatus::evaluate(array_merge($language, ['_source' => $category]), $category['translations'] ?? [], ['name', 'slug'], $category['updated_at'] ?? null)['status'];
                    ?>
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                        <div class="font-bold text-sm text-brand-700 pb-2 border-b border-gray-200 flex justify-between gap-2">
                            <span><?= esc(lang('CmsLanguages.field_code')) ?>: <?= esc($langCodeMap[$tLangId] ?? ('#' . $tLangId)) ?></span>
                            <span class="inline-flex items-center gap-2"><span class="rounded-full px-2 py-0.5 text-[10px] font-semibold <?= \App\Modules\Cms\Support\TranslationStatus::badgeClasses($tState) ?>"><?= esc(lang('Translations.status_' . $tState)) ?></span><a href="<?= esc(\App\Modules\Cms\Support\TranslationStatus::editUrl(route_to('admin.cms.categories.edit', $itemId), $tLangId)) ?>" class="text-xs text-brand-600 hover:text-brand-700"><?= esc(lang('App.edit')) ?></a></span>
                            <span class="text-gray-500 font-mono">/<?= esc($t['slug'] ?? '') ?></span>
                        </div>
                        <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-xs">
                            <div>
                                <dt class="text-gray-500 font-semibold"><?= esc(lang('Categories.translation_name_label')) ?></dt>
                                <dd class="text-gray-900 mt-0.5 font-medium"><?= esc($t['name'] ?? '—') ?></dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-semibold"><?= esc(lang('Categories.translation_slug_label')) ?></dt>
                                <dd class="text-gray-900 mt-0.5"><?= esc($t['slug'] ?? '—') ?></dd>
                            </div>
                        </dl>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mt-4 text-sm text-gray-500">—</p>
        <?php endif; ?>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Categories.categories_details',
        'items' => [
            ['label' => 'Categories.field_collection_id', 'value' => ($collections[(string) ($category['collection_id'] ?? '')] ?? ($category['collection_id'] ?? '—'))],
            ['label' => 'Categories.field_parent_id', 'value' => ($categories[(string) ($category['parent_id'] ?? '')] ?? ($category['parent_id'] ?? '—'))],
            ['label' => 'Categories.field_is_active', 'value' => view('components/table/boolean_cell', ['value' => $category['is_active'] ?? false]), 'isHtml' => true],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($category['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.categories.write')): ?>
        <a href="<?= route_to('admin.cms.categories.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
        <a href="<?= route_to('admin.cms.categories.reorder') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
            <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
            <?= esc(lang('Categories.field_sort_order') ?? lang('App.reorder')) ?>
        </a>
    <?php endif; ?>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.categories.write')): ?>
        <form method="post" action="<?= route_to('admin.cms.categories.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($category['name'] ?? $category['slug'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        </form>
    <?php endif; ?>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_actions_panel', [
        'content' => $actionsContent,
        'dangerContent' => $dangerContent,
    ]) ?>
    <?php $asideContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => $asideContent,
    ]) ?>
<?php endif; ?>
