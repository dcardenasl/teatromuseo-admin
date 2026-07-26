<?php

use App\Modules\Cms\Support\BlockOwnerRouting;

$entry         = $entry ?? [];
$collection    = $collection ?? [];
$languages     = $languages ?? [];
$publicSiteUrl = $publicSiteUrl ?? '';

// Build language code map: id → uppercase code (e.g. 3 → 'ES')
$langCodeMap = [];
foreach ($languages as $l) {
    if (is_array($l) && isset($l['id'], $l['code'])) {
        $langCodeMap[(int) $l['id']] = strtoupper((string) $l['code']);
    }
}
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($entry)): ?>
    <?php
    $itemId = (string) ($entry['id'] ?? '');
    $entryTitle = (string) (($entry['translations'][0]['title'] ?? null) ?: ($entry['title'] ?? $entry['slug'] ?? $itemId));
    ?>
    <?= view('components/table/translation_status_panel', ['languages' => $languages, 'translations' => $entry['translations'] ?? [], 'requiredFields' => ['slug', 'title'], 'sourceFields' => $entry, 'sourceUpdatedAt' => $entry['updated_at'] ?? null, 'editUrlTemplate' => route_to('admin.cms.entries.edit', $itemId)]) ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.cms.entries'),
        'backLabel' => 'Entries.entries_title',
        'eyebrow' => 'Entries.entries_details',
        'title' => $entryTitle,
        'badge' => ! empty($entry['status']) ? cms_status_badge($entry['status']) : null,
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Entries.translation_title')) ?></h3>

        <?php if (! empty($entry['translations']) && is_array($entry['translations'])): ?>
            <div class="mt-4 space-y-4">
                <?php foreach ($entry['translations'] as $t):
                    $tLangId  = (int) ($t['language_id'] ?? 0);
                    $tLangCode = strtolower($langCodeMap[$tLangId] ?? (string) $tLangId);
                    $tSlug    = trim((string) ($t['slug'] ?? ''));

                    $collectionSlugs = $collection['localized_slugs'] ?? [];
                    if (isset($collection['index_page']['localized_slugs'])) {
                        $collectionSlugs = $collection['index_page']['localized_slugs'];
                    }
                    $colSlug = trim((string) ($collectionSlugs[$tLangCode] ?? $collection['slug'] ?? $collection['collection_key'] ?? ''), '/');
                    if ($colSlug !== '') {
                        $colSlug = '/' . $colSlug;
                    }

                    $tPreview = $publicSiteUrl !== '' && $tSlug !== ''
                        ? $publicSiteUrl . '/' . $tLangCode . $colSlug . '/' . ltrim($tSlug, '/') . '?preview=1'
                        : '';
                    ?>
                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/50">
                        <div class="font-bold text-sm text-brand-700 pb-2 border-b border-gray-200 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <span><?= esc(lang('Entries.translation_language_label')) ?>: <?= esc($langCodeMap[$tLangId] ?? $tLangId) ?></span>
                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                <span class="text-gray-500 font-mono text-xs">/<?= esc($tSlug) ?></span>
                                <?php if ($tPreview !== ''): ?>
                                <a href="<?= esc($tPreview) ?>" target="_blank" rel="noopener noreferrer"
                                   class="text-xs text-brand-600 hover:text-brand-700 inline-flex items-center gap-1 whitespace-nowrap">
                                    <?= ui_icon('external-link', 'h-3 w-3') ?>
                                    <?= esc(lang('Pages.block_preview_button')) ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-xs">
                            <div>
                                <dt class="text-gray-500 font-semibold"><?= esc(lang('Entries.translation_name_label')) ?></dt>
                                <dd class="text-gray-900 mt-0.5 font-medium"><?= esc($t['title'] ?? '—') ?></dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-semibold"><?= esc(lang('Entries.translation_excerpt_label')) ?></dt>
                                <dd class="text-gray-900 mt-0.5"><?= esc($t['excerpt'] ?? '—') ?></dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-semibold"><?= esc(lang('Entries.translation_meta_title_label')) ?></dt>
                                <dd class="text-gray-900 mt-0.5"><?= esc($t['meta_title'] ?? '—') ?></dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 font-semibold"><?= esc(lang('Entries.translation_meta_description_label')) ?></dt>
                                <dd class="text-gray-900 mt-0.5"><?= esc($t['meta_description'] ?? '—') ?></dd>
                            </div>
                        </dl>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mt-3 text-sm text-gray-500">—</p>
        <?php endif; ?>
    </section>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <?= view('cms/partials/block_instances_panel', [
            'blocks'          => $blocks ?? [],
            'blockTypes'      => $blockTypes ?? [],
            'itemId'          => $itemId,
            'ownerType'       => 'entry',
            'writePermission' => 'cms.entries.write',
            'languages'       => $languages ?? [],
            'blockTranslationStatus'  => $blockTranslationStatus ?? [],
        ]) ?>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Entries.entries_details',
        'items' => [
            ['label' => 'Entries.field_collection_id', 'value' => ($collections[(string) ($entry['collection_id'] ?? '')] ?? ($entry['collection_id'] ?? '—'))],
            ['label' => 'Entries.field_status', 'value' => ! empty($entry['status']) ? cms_status_badge($entry['status']) : '—', 'isHtml' => true],
            ['label' => 'Entries.field_published_at', 'value' => $entry['published_at'] ?? '—'],
            ['label' => 'Entries.field_scheduled_at', 'value' => $entry['scheduled_at'] ?? '—'],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($entry['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php if (! empty($entry['categories']) && is_array($entry['categories'])): ?>
        <?php ob_start(); ?>
        <div class="flex flex-wrap gap-1">
            <?php foreach ($entry['categories'] as $c): ?>
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                    <?= esc($c['name'] ?? $c['slug'] ?? $c['id']) ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php $categoriesContent = ob_get_clean(); ?>
        <?= view('components/display/admin_meta_panel', [
            'title' => 'Entries.categories_title',
            'content' => $categoriesContent,
        ]) ?>
    <?php endif; ?>

    <?php if (! empty($entry['tags']) && is_array($entry['tags'])): ?>
        <?php ob_start(); ?>
        <div class="flex flex-wrap gap-1">
            <?php foreach ($entry['tags'] as $tg): ?>
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                    #<?= esc($tg['name'] ?? $tg['slug'] ?? $tg['id']) ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php $tagsContent = ob_get_clean(); ?>
        <?= view('components/display/admin_meta_panel', [
            'title' => 'Entries.tags_title',
            'content' => $tagsContent,
        ]) ?>
    <?php endif; ?>

    <?php ob_start(); ?>
    <?php
    $previewUrl = BlockOwnerRouting::previewUrl('entry', $entry, $languages);
?>
    <?php if ($previewUrl !== ''): ?>
        <a href="<?= esc($previewUrl) ?>" target="_blank" rel="noopener noreferrer" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
            <?= ui_icon('external-link', 'h-3.5 w-3.5') ?>
            <span><?= esc(lang('Pages.block_preview_button')) ?></span>
        </a>
    <?php endif; ?>
    <?php if (has_permission('cms.entries.write')): ?>
        <a href="<?= route_to('admin.cms.entries.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
        <a href="<?= route_to('admin.cms.entries.blocks', $itemId) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
            <?= ui_icon('layout-template', 'h-3.5 w-3.5') ?>
            <?= esc(lang('Entries.blocks_title')) ?>
        </a>
        <form method="post" action="<?= route_to('admin.cms.entries.publish', $itemId) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class()) ?> w-full justify-center">
                <?= esc(lang('Entries.entries_publish')) ?>
            </button>
        </form>
        <form method="post" action="<?= route_to('admin.cms.entries.archive', $itemId) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class()) ?> w-full justify-center">
                <?= esc(lang('Entries.entries_archive')) ?>
            </button>
        </form>
        <a href="<?= route_to('admin.cms.entries.reorder') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
            <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.reorder')) ?>
        </a>
    <?php endif; ?>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.entries.write')): ?>
        <form method="post" action="<?= route_to('admin.cms.entries.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($entry['title'] ?? $entry['slug'] ?? null), 'js') ?>', () => $el.submit())">
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
