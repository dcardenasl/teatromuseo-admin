<?php
$collection = $collection ?? [];
$languages = $languages ?? [];
$langCodeMap = [];
foreach ($languages as $language) {
    if (is_array($language) && isset($language['id'], $language['code'])) {
        $langCodeMap[(int) $language['id']] = strtoupper((string) $language['code']);
    }
}
$translationByLang = [];
foreach (($collection['translations'] ?? []) as $translation) {
    $translationByLang[(int) ($translation['language_id'] ?? 0)] = $translation;
}
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($collection)): ?>
    <?php $itemId = (string) ($collection['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.cms.collections'),
        'backLabel' => 'Collections.collections_title',
        'eyebrow' => 'Collections.collections_details',
        'title' => (string) ($collection['collection_key'] ?? '—'),
        'badge' => view('components/table/boolean_cell', ['value' => $collection['is_active'] ?? false]),
    ]) ?>

    <?= view('components/table/translation_status_panel', ['languages' => $languages, 'translations' => $collection['translations'] ?? [], 'requiredFields' => ['name'], 'sourceFields' => $collection, 'sourceUpdatedAt' => $collection['updated_at'] ?? null, 'editUrlTemplate' => route_to('admin.cms.collections.edit', $itemId)]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('Collections.collections_details') ?></h3>
        <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <?= view('components/display/field_row', [
                'label' => 'Collections.field_collection_key',
                'value' => $collection['collection_key'] ?? '—'
            ]) ?>
        </dl>

        <?php if (!empty($collection['translations']) && is_array($collection['translations'])): ?>
            <div class="mt-6 border-t border-gray-100 pt-6">
                <h4 class="text-md font-semibold text-gray-800 mb-3"><?= esc(lang('Collections.translation_title')) ?></h4>
                <div class="space-y-4">
                    <?php foreach ($languages as $language):
                        $tLangId = (int) ($language['id'] ?? 0);
                        $t = $translationByLang[$tLangId] ?? [];
                        $tState = \App\Modules\Cms\Support\TranslationStatus::evaluate(array_merge($language, ['_source' => $collection]), $collection['translations'] ?? [], ['name'], $collection['updated_at'] ?? null)['status'];
                        ?>
                        <div class="border border-gray-200 rounded-lg p-3 bg-gray-50/50">
                            <div class="text-xs font-semibold text-brand-700 mb-1 flex items-center justify-between gap-2">
                                <span><?= esc(lang('Collections.translation_language_label')) ?>: <?= esc($langCodeMap[$tLangId] ?? ('#' . $tLangId)) ?></span>
                                <span class="inline-flex items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold <?= \App\Modules\Cms\Support\TranslationStatus::badgeClasses($tState) ?>"><?= esc(lang('Translations.status_' . $tState)) ?></span>
                                    <a href="<?= esc(\App\Modules\Cms\Support\TranslationStatus::editUrl(route_to('admin.cms.collections.edit', $itemId), $tLangId)) ?>" class="text-xs text-brand-600 hover:text-brand-700"><?= esc(lang('App.edit')) ?></a>
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 block"><?= esc(lang('Collections.translation_name_label')) ?></span>
                                    <span class="text-gray-900 font-medium"><?= esc($t['name'] ?? '—') ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block"><?= esc(lang('Collections.translation_description_label')) ?></span>
                                    <span class="text-gray-900"><?= esc($t['description'] ?? '—') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($languages !== []): ?>
            <p class="mt-4 text-sm text-gray-500"><?= esc(lang('Translations.no_missing_elements')) ?></p>
        <?php endif; ?>
        
        <?php if (!empty($collection['block_template'])): ?>
            <?php
            $templateData = is_string($collection['block_template']) ? json_decode($collection['block_template'], true) : $collection['block_template'];
            $blocks = $templateData['blocks'] ?? [];
            ?>
            <?php if (!empty($blocks) && is_array($blocks)): ?>
                <div class="mt-6 border-t border-gray-200 pt-6">
                    <h4 class="text-base font-semibold text-gray-900">
                        <?= esc(lang('Collections.block_template_show_title')) ?>
                    </h4>
                    <p class="text-sm text-gray-500 mt-0.5">
                        <?= esc(lang('Collections.block_template_show_desc')) ?>
                    </p>
                    <ul class="mt-4 space-y-2">
                        <?php foreach ($blocks as $block): ?>
                            <li class="flex items-center gap-3 bg-white border border-gray-200 rounded-lg px-3 py-2.5 shadow-sm">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                    <?= ui_icon('layout-template', 'h-4 w-4') ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-xs font-semibold text-gray-700"><?= esc($block['label'] ?? $block['block_key']) ?></span>
                                    <p class="text-xs text-gray-400 mt-0.5 font-mono"><?= esc($block['block_key']) ?></p>
                                </div>
                                <div class="flex gap-2 flex-shrink-0">
                                    <?php if (!empty($block['required'])): ?>
                                        <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                            <?= esc(lang('Collections.block_template_builder_required')) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($block['locked'])): ?>
                                        <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                            <?= esc(lang('Collections.block_template_builder_locked')) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Collections.collections_details',
        'items' => [
            ['label' => 'Collections.field_is_active', 'value' => view('components/table/boolean_cell', ['value' => $collection['is_active'] ?? false]), 'isHtml' => true],
            ['label' => 'Collections.field_requires_approval', 'value' => view('components/table/boolean_cell', ['value' => $collection['requires_approval'] ?? false]), 'isHtml' => true],
            ['label' => 'Collections.field_enables_categories', 'value' => view('components/table/boolean_cell', ['value' => $collection['enables_categories'] ?? false]), 'isHtml' => true],
            ['label' => 'Collections.field_enables_tags', 'value' => view('components/table/boolean_cell', ['value' => $collection['enables_tags'] ?? false]), 'isHtml' => true],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($collection['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.collections.write')): ?>
        <a href="<?= route_to('admin.cms.collections.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
        <a href="<?= route_to('admin.cms.collections.structure', $itemId) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
            <?= ui_icon('layout-template', 'h-3.5 w-3.5') ?>
            <?= esc(lang('Collections.collections_manage_structure')) ?>
        </a>
    <?php endif; ?>
    <a href="<?= site_url('admin/cms/entries?collection_id=' . $itemId) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
        <?= ui_icon('cms-entry', 'h-4 w-4') ?>
        <span><?= esc(lang('Collections.collections_entries')) ?></span>
    </a>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.collections.write')): ?>
        <form method="post" action="<?= route_to('admin.cms.collections.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($collection['name'] ?? $collection['collection_key'] ?? null), 'js') ?>', () => $el.submit())">
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
