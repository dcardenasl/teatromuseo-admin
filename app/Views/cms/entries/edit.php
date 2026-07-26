<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.entries') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <div class="flex items-center gap-2">
        <a href="<?= route_to('admin.cms.entries.blocks', (string) ($item['id'] ?? '')) ?>" class="<?= esc(action_button_class('neutral')) ?>">
            <?= ui_icon('layout-template', 'h-3.5 w-3.5') ?>
            <?= esc(lang('Entries.blocks_title')) ?>
        </a>
        <form method="post" action="<?= route_to('admin.cms.entries.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['title'] ?? $item['slug'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        </form>
    </div>
</div>

<?php ob_start(); ?>
<form method="post" action="<?= route_to('admin.cms.entries.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="<?= esc($returnTo ?? '', 'attr') ?>">
        <div class="lg:col-span-2 space-y-6">

        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 space-y-4">
            <div>
                <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('App.form_core')) ?></h4>
                <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Entries.field_collection_id_help')) ?></p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= view('components/form/relation', [
                    'name' => 'collection_id',
                    'label' => 'Entries.field_collection_id',
                    'required' => true,
                    'options' => $collections ?? [],
                    'placeholder' => 'Entries.field_collection_id_placeholder',
                    'help' => 'Entries.field_collection_id_help',
                    'value' => $item['collection_id'] ?? '',
                    'errors' => $errors ?? []
                ]) ?>

                <?= view('components/form/select', [
                    'name' => 'status',
                    'label' => 'Entries.field_status',
                    'required' => true,
                    'placeholder' => 'Entries.field_status_placeholder',
                    'help' => 'Entries.field_status_help',
                    'options' => [
                        'draft' => lang('Entries.status_draft'),
                        'published' => lang('Entries.status_published'),
                        'archived' => lang('Entries.status_archived')
                    ],
                    'value' => $item['status'] ?? $item['workflow_status'] ?? 'draft',
                    'errors' => $errors ?? []
                ]) ?>
            </div>

            <?= view('components/form/boolean', [
                'name' => 'is_featured',
                'label' => 'Entries.field_is_featured',
                'value' => $item['is_featured'] ?? false,
                'on_label' => 'Entries.field_is_featured_on',
                'off_label' => 'Entries.field_is_featured_off',
                'help' => 'Entries.field_is_featured_help',
                'errors' => $errors ?? []
            ]) ?>
        </div>

        <?php
            $taxonomySelection = static function (mixed $value): array {
                if (! is_array($value)) {
                    return [];
                }

                $values = array_filter($value, static fn ($item): bool => is_scalar($item) && (string) $item !== '');

                return array_values(array_map('strval', $values));
            };

$selectedCategoryValues = $taxonomySelection(old('category_ids', $selectedCategoryIds ?? []));
$selectedTagValues = $taxonomySelection(old('tag_ids', $selectedTagIds ?? []));
$taxonomyShouldOpen = $selectedCategoryValues !== [] || $selectedTagValues !== [];
$taxonomySummaryParts = [];
if ($selectedCategoryValues !== []) {
    $taxonomySummaryParts[] = count($selectedCategoryValues) . ' ' . lang('Entries.categories_title');
}
if ($selectedTagValues !== []) {
    $taxonomySummaryParts[] = count($selectedTagValues) . ' ' . lang('Entries.tags_title');
}
?>
        <details class="group rounded-xl border border-gray-200 bg-gray-50/60 p-4 space-y-6" <?= $taxonomyShouldOpen ? 'open' : '' ?>>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Entries.taxonomy_title')) ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Entries.taxonomy_help')) ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <?php if ($taxonomySummaryParts !== []): ?>
                        <div class="hidden flex-wrap justify-end gap-2 sm:flex">
                            <?php foreach ($taxonomySummaryParts as $part): ?>
                                <span class="inline-flex items-center rounded-full border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-600"><?= esc($part) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200 group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                    </svg>
                </div>
            </summary>
            <div class="mt-6 space-y-6">
                <?= view('components/form/taxonomy_checklist', [
            'name' => 'category_ids',
            'label' => 'Entries.categories_title',
            'help' => 'Entries.categories_help',
            'options' => $categoryOptions ?? [],
            'selected' => $selectedCategoryValues,
        ]) ?>
                <?= view('components/form/taxonomy_checklist', [
            'name' => 'tag_ids',
            'label' => 'Entries.tags_title',
            'help' => 'Entries.tags_help',
            'options' => $tagOptions ?? [],
            'selected' => $selectedTagValues,
        ]) ?>
            </div>
        </details>




        <!-- Block Template Banner -->
        <?php if (!empty($blockTemplate['blocks'])): ?>
            <div class="flex items-start gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3">
                <svg class="h-4 w-4 text-brand-600 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                <div class="flex-1">
                    <p class="text-sm font-medium text-brand-900"><?= esc(lang('Entries.block_template_notice_title')) ?></p>
                    <p class="mt-0.5 text-xs text-brand-700"><?= esc(lang('Entries.block_template_notice_body', [count($blockTemplate['blocks'])])) ?></p>
                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                        <?php foreach ($blockTemplate['blocks'] as $block): ?>
                            <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-xs font-medium <?= !empty($block['locked']) ? 'bg-amber-100 text-amber-800' : 'bg-brand-100 text-brand-700' ?>">
                                <?php if (!empty($block['locked'])): ?>
                                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <?php endif; ?>
                                <?= esc($block['label'] ?? $block['block_key'] ?? '') ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="<?= route_to('admin.cms.entries.blocks', (string) ($item['id'] ?? '')) ?>" class="flex-shrink-0 text-xs font-medium text-brand-600 hover:text-brand-800 underline underline-offset-2 mt-0.5"><?= esc(lang('Entries.block_template_manage_link')) ?></a>
            </div>
        <?php endif; ?>

        <!-- Translations with language tabs -->
        <?php if (!empty($languages)): ?>
            <?php
            $defaultLangId = (int) ($defaultLangId ?? 0);
            $defaultLangCode = (string) ($defaultLangCode ?? '');
            $defaultLangIndex = (int) ($defaultLangIndex ?? 0);
            $focusLangId = (int) ($focusLangId ?? 0);
            $initialTabId = $focusLangId > 0 ? $focusLangId : $defaultLangId;
            $translateUrl = route_to('admin.cms.translate');
            ?>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4">
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Entries.translation_title')) ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Entries.translations_help')) ?></p>
                </div>

                <div x-data="langTabs(<?= $initialTabId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
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
                        <?php $copyMappings = cms_translation_copy_mappings(['slug', 'title', 'excerpt', 'meta_title', 'meta_description'], $languages, $defaultLangIndex); ?>
                        <button type="button" @click="copyDefaultToAll(<?= esc(json_encode($copyMappings, JSON_THROW_ON_ERROR), 'attr') ?>, '<?= esc(lang('Translations.confirm_copy_default'), 'js') ?>')" class="mb-px inline-flex items-center gap-1.5 text-xs text-gray-700 hover:text-gray-900 border border-gray-300 rounded px-3 py-1.5 bg-white hover:bg-gray-50 transition-colors">
                            <?= ui_icon('copy', 'h-3.5 w-3.5') ?> <?= esc(lang('Translations.action_copy_default')) ?>
                        </button>
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

                    <?php foreach ($languages as $index => $lang): ?>
                        <?php
                                $transValue = [];
                        if (!empty($item['translations']) && is_array($item['translations'])) {
                            foreach ($item['translations'] as $t) {
                                if (is_array($t) && (int)($t['language_id'] ?? 0) === (int)$lang['id']) {
                                    $transValue = $t;
                                    break;
                                }
                            }
                        }
                        $isDefault = !empty($lang['is_default']);
                        $langCode  = strtoupper($lang['code'] ?? '');
                        $fields = [
                            ['from' => sprintf('[name="translations[%d][title]"]', $defaultLangIndex),            'to' => sprintf('[name="translations[%d][title]"]', $index)],
                            ['from' => sprintf('[name="translations[%d][excerpt]"]', $defaultLangIndex),          'to' => sprintf('[name="translations[%d][excerpt]"]', $index)],
                            ['from' => sprintf('[name="translations[%d][meta_title]"]', $defaultLangIndex),       'to' => sprintf('[name="translations[%d][meta_title]"]', $index)],
                            ['from' => sprintf('[name="translations[%d][meta_description]"]', $defaultLangIndex), 'to' => sprintf('[name="translations[%d][meta_description]"]', $index)],
                        ];
                        ?>
                        <div x-show="isActive(<?= (int) $lang['id'] ?>)" class="space-y-4">
                            <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                            <?php if (!$isDefault): ?>
                            <div class="flex justify-end">
                                <button type="button"
                                    @click="autoTranslate('<?= esc($langCode, 'attr') ?>', <?= esc(json_encode($fields, JSON_THROW_ON_ERROR), 'attr') ?>)"
                                    :disabled="translating"
                                    class="inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-3 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                                    <span x-show="!translating"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_from_default')) ?></span>
                                    <span x-show="translating" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <?= esc(lang('App.translating')) ?></span>
                                </button>
                            </div>
                            <?php endif; ?>

                            <?= view('components/form/text', [
                                'name' => "translations[{$index}][title]",
                                'label' => 'Entries.translation_name_label',
                                'required' => !empty($lang['is_default']),
                                'placeholder' => 'Entries.translation_name_placeholder',
                                'help' => 'Entries.translation_name_help',
                                'value' => old("translations.{$index}.title", $transValue['title'] ?? ''),
                                'maxlength' => 255,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/slug', [
                                'name' => "translations[{$index}][slug]",
                                'label' => 'Entries.translation_slug_label',
                                'required' => !empty($lang['is_default']),
                                'sourceId' => sprintf('[name="translations[%d][title]"]', $index),
                                'checkUrl' => route_to('admin.cms.entries.check_slug') . '?language_id=' . (int)$lang['id'],
                                'currentId' => $item['id'] ?? '',
                                'value' => old("translations.{$index}.slug", $transValue['slug'] ?? ''),
                                'help' => 'Entries.translation_slug_help',
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][excerpt]",
                                'label' => 'Entries.translation_excerpt_label',
                                'required' => false,
                                'placeholder' => 'Entries.translation_excerpt_placeholder',
                                'help' => 'Entries.translation_excerpt_help',
                                'value' => old("translations.{$index}.excerpt", $transValue['excerpt'] ?? ''),
                                'maxlength' => 500,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/media_reference', [
                            'name' => "translations[{$index}][featured_image]",
                            'label' => lang('Entries.translation_featured_image_label'),
                            'help' => lang('Entries.translation_featured_image_help'),
                            'value' => old("translations.{$index}.featured_image", $transValue['featured_image'] ?? []),
                            'fieldKey' => 'featured_image',
                            'copyEnabled' => true,
                            'accept' => 'image',
                        ]) ?>

                            <details class="group border border-gray-100 rounded-lg bg-gray-50/30" <?= (!empty($transValue['meta_title']) || !empty($transValue['meta_description']) || !empty($transValue['og_image']['file_id'] ?? null) || !empty($transValue['og_image']['url'] ?? null)) ? 'open' : '' ?>>
                                <summary class="flex cursor-pointer items-center justify-between px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 rounded-lg select-none">
                                    <span><?= esc(lang('Entries.section_seo_per_lang')) ?></span>
                                    <svg class="h-3.5 w-3.5 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                                </summary>
                                <div class="px-3 pb-3 pt-2 space-y-4 border-t border-gray-100">
                                    <?= view('components/form/text', [
                                    'name' => "translations[{$index}][meta_title]",
                                    'label' => 'Entries.translation_meta_title_label',
                                    'required' => false,
                                    'placeholder' => 'Entries.translation_meta_title_placeholder',
                                    'help' => 'Entries.translation_meta_title_help',
                                    'value' => old("translations.{$index}.meta_title", $transValue['meta_title'] ?? ''),
                                    'maxlength' => 255,
                                    'errors' => $errors ?? []
                                ]) ?>
                                    <?= view('components/form/textarea', [
                                    'name' => "translations[{$index}][meta_description]",
                                    'label' => 'Entries.translation_meta_description_label',
                                    'required' => false,
                                    'placeholder' => 'Entries.translation_meta_description_placeholder',
                                    'help' => 'Entries.translation_meta_description_help',
                                    'value' => old("translations.{$index}.meta_description", $transValue['meta_description'] ?? ''),
                                    'maxlength' => 500,
                                    'rows' => 3,
                                    'errors' => $errors ?? []
                                ]) ?>
                                    <?= view('components/form/media_reference', [
                                    'name' => "translations[{$index}][og_image]",
                                    'label' => lang('Entries.translation_og_image_label'),
                                    'help' => lang('Entries.translation_og_image_help'),
                                    'value' => old("translations.{$index}.og_image", $transValue['og_image'] ?? []),
                                    'fieldKey' => 'og_image',
                                    'copyEnabled' => true,
                                    'accept' => 'image',
                                ]) ?>
                                </div>
                            </details>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        </div>
        <aside class="space-y-6">
            <?php ob_start(); ?>
            <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.update')) ?></button>
            <a href="<?= route_to('admin.cms.entries') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
            <?php $actionsContent = ob_get_clean(); ?>
            <?= view('components/display/admin_actions_panel', ['content' => $actionsContent]) ?>

            <?php if (isset($item['view_count'])): ?>
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-gray-500"><?= esc(lang('Entries.field_view_count')) ?></span>
                        <span class="font-semibold text-gray-950 bg-gray-100 px-2.5 py-0.5 rounded-md text-xs"><?= (int) $item['view_count'] ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Publishing & Scheduling -->
            <details class="group rounded-xl border border-gray-200 bg-white" <?= (!empty($item['published_at']) || !empty($item['scheduled_at'])) ? 'open' : '' ?>>
                <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                    <span><?= esc(lang('Entries.section_publishing')) ?></span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </summary>
                <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                    <?= view('components/form/number', [
                        'name' => 'sort_order',
                        'label' => 'Entries.field_sort_order',
                        'required' => false,
                        'value' => $item['sort_order'] ?? 0,
                        'placeholder' => 'Entries.field_sort_order_placeholder',
                        'help' => 'Entries.field_sort_order_help',
                        'errors' => $errors ?? []
                    ]) ?>
                    <?= view('components/form/datetime', [
                        'name' => 'published_at',
                        'label' => 'Entries.field_published_at',
                        'required' => false,
                        'value' => $item['published_at'] ?? '',
                        'placeholder' => 'Entries.field_published_at_placeholder',
                        'help' => 'Entries.field_published_at_help',
                        'errors' => $errors ?? []
                    ]) ?>
                    <?= view('components/form/datetime', [
                        'name' => 'scheduled_at',
                        'label' => 'Entries.field_scheduled_at',
                        'required' => false,
                        'value' => $item['scheduled_at'] ?? '',
                        'placeholder' => 'Entries.field_scheduled_at_placeholder',
                        'help' => 'Entries.field_scheduled_at_help',
                        'errors' => $errors ?? []
                    ]) ?>
                </div>
            </details>

            <!-- SEO & Sitemap -->
            <details class="group rounded-xl border border-gray-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                    <span><?= esc(lang('Entries.section_seo_sitemap')) ?></span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </summary>
                <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                    <?= view('components/form/boolean', [
                        'name' => 'is_in_sitemap',
                        'label' => 'Entries.field_is_in_sitemap',
                        'value' => $item['is_in_sitemap'] ?? true,
                        'on_label' => 'Entries.field_is_in_sitemap_on',
                        'off_label' => 'Entries.field_is_in_sitemap_off',
                        'help' => 'Entries.field_is_in_sitemap_help',
                        'errors' => $errors ?? []
                    ]) ?>
                    <?= view('components/form/text', [
                        'name' => 'sitemap_priority',
                        'label' => 'Entries.field_sitemap_priority',
                        'required' => false,
                        'value' => $item['sitemap_priority'] ?? '',
                        'placeholder' => 'Entries.field_sitemap_priority_placeholder',
                        'help' => 'Entries.field_sitemap_priority_help',
                        'errors' => $errors ?? []
                    ]) ?>
                    <?= view('components/form/select', [
                        'name' => 'sitemap_changefreq',
                        'label' => 'Entries.field_sitemap_changefreq',
                        'required' => false,
                        'placeholder' => 'Entries.field_sitemap_changefreq_placeholder',
                        'help' => 'Entries.field_sitemap_changefreq_help',
                        'options' => [
                        'always' => lang('Entries.frequency_always'),
                        'hourly' => lang('Entries.frequency_hourly'),
                        'daily' => lang('Entries.frequency_daily'),
                        'weekly' => lang('Entries.frequency_weekly'),
                        'monthly' => lang('Entries.frequency_monthly'),
                        'yearly' => lang('Entries.frequency_yearly'),
                        'never' => lang('Entries.frequency_never'),
                        ],
                        'value' => $item['sitemap_changefreq'] ?? 'weekly',
                        'errors' => $errors ?? []
                    ]) ?>
                </div>
            </details>
        </aside>
    </form>
<?php $sectionContent = ob_get_clean(); ?>
<?= view('components/display/form_section', [
    'title' => 'Entries.entries_edit',
    'description' => 'Entries.entries_details',
    'content' => $sectionContent,
]) ?>
