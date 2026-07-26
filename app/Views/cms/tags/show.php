<?php
$tag = $tag ?? [];
$languages = $languages ?? [];
$langCodeMap = [];
foreach ($languages as $language) {
    if (is_array($language) && isset($language['id'], $language['code'])) {
        $langCodeMap[(int) $language['id']] = strtoupper((string) $language['code']);
    }
}
$translationByLang = [];
foreach (($tag['translations'] ?? []) as $translation) {
    $translationByLang[(int) ($translation['language_id'] ?? 0)] = $translation;
}
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($tag)): ?>
    <?php $itemId = (string) ($tag['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.cms.tags'),
        'backLabel' => 'Tags.tags_title',
        'eyebrow' => 'Tags.tags_details',
        'title' => (string) (($tag['translations'][0]['name'] ?? null) ?: ($tag['name'] ?? $tag['slug'] ?? '—')),
        'badge' => view('components/table/boolean_cell', ['value' => $tag['is_active'] ?? false]),
    ]) ?>

    <?= view('components/table/translation_status_panel', ['languages' => $languages, 'translations' => $tag['translations'] ?? [], 'requiredFields' => ['name', 'slug'], 'sourceFields' => $tag, 'sourceUpdatedAt' => $tag['updated_at'] ?? null, 'editUrlTemplate' => route_to('admin.cms.tags.edit', $itemId)]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('Tags.tags_details') ?></h3>

        <?php if (! empty($tag['translations']) && is_array($tag['translations'])): ?>
            <div class="mt-4 space-y-4">
                <?php foreach ($languages as $language):
                    $tLangId = (int) ($language['id'] ?? 0);
                    $t = $translationByLang[$tLangId] ?? [];
                    $tState = \App\Modules\Cms\Support\TranslationStatus::evaluate(array_merge($language, ['_source' => $tag]), $tag['translations'] ?? [], ['name', 'slug'], $tag['updated_at'] ?? null)['status'];
                    ?>
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                        <div class="font-bold text-sm text-brand-700 pb-2 border-b border-gray-200 flex justify-between gap-2">
                            <span><?= esc(lang('CmsLanguages.field_code')) ?>: <?= esc($langCodeMap[$tLangId] ?? ('#' . $tLangId)) ?></span>
                            <span class="inline-flex items-center gap-2"><span class="rounded-full px-2 py-0.5 text-[10px] font-semibold <?= \App\Modules\Cms\Support\TranslationStatus::badgeClasses($tState) ?>"><?= esc(lang('Translations.status_' . $tState)) ?></span><a href="<?= esc(\App\Modules\Cms\Support\TranslationStatus::editUrl(route_to('admin.cms.tags.edit', $itemId), $tLangId)) ?>" class="text-xs text-brand-600 hover:text-brand-700"><?= esc(lang('App.edit')) ?></a></span>
                            <span class="text-gray-500 font-mono">/<?= esc($t['slug'] ?? '') ?></span>
                        </div>
                        <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-xs">
                            <div>
                                <dt class="text-gray-500 font-semibold"><?= esc(lang('Tags.translation_name_label')) ?></dt>
                                <dd class="text-gray-900 mt-0.5 font-medium"><?= esc($t['name'] ?? '—') ?></dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-semibold"><?= esc(lang('Tags.translation_slug_label')) ?></dt>
                                <dd class="text-gray-900 mt-0.5"><?= esc($t['slug'] ?? '—') ?></dd>
                            </div>
                        </dl>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mt-4 text-sm text-gray-500"><?= esc(lang('App.no_results')) ?></p>
        <?php endif; ?>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Tags.tags_details',
        'items' => [
            ['label' => 'Tags.field_is_active', 'value' => view('components/table/boolean_cell', ['value' => $tag['is_active'] ?? false]), 'isHtml' => true],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($tag['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.tags.write')): ?>
        <a href="<?= route_to('admin.cms.tags.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
    <?php endif; ?>
    <a href="<?= route_to('admin.cms.entries') . '?tag_id=' . urlencode($itemId) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
        <?= ui_icon('link', 'h-3.5 w-3.5') ?>
        <?= esc(lang('Entries.entries_title')) ?>
    </a>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.tags.write')): ?>
        <form method="post" action="<?= route_to('admin.cms.tags.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($tag['name'] ?? $tag['slug'] ?? null), 'js') ?>', () => $el.submit())">
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
