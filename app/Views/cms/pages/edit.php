<?php
$item        = $item        ?? [];
$focusLangId = $focusLangId ?? 0;
$itemIdStr   = (string) ($item['id'] ?? '');
$translations = is_array($item['translations'] ?? null) ? $item['translations'] : [];

$resolveTranslation = static function (array $lang) use ($translations): array {
    $langId = (int) ($lang['id'] ?? 0);

    foreach ($translations as $translation) {
        if (is_array($translation) && (int) ($translation['language_id'] ?? 0) === $langId) {
            return $translation;
        }
    }

    return [];
};

// Calculate translation completion stats
$translationStats = [];
if (!empty($languages)) {
    foreach ($languages as $lang) {
        $langId = (int) ($lang['id'] ?? 0);
        $transValue = $resolveTranslation($lang);
        $translationStats[$langId] = [
            'code' => $lang['code'] ?? '',
            'status' => empty($transValue) ? 'missing' : ((!empty($transValue['title']) && !empty($transValue['slug'])) ? 'complete' : 'incomplete'),
            'has_seo' => !empty($transValue['meta_title']) || !empty($transValue['meta_description'])
        ];
    }
}
$completedCount = array_sum(array_map(fn ($s) => $s['status'] === 'complete' ? 1 : 0, $translationStats));
$totalLanguages = count($translationStats);
?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= $itemIdStr !== '' ? route_to('admin.cms.pages.show', $itemIdStr) : route_to('admin.cms.pages') ?>"
       class="text-sm text-brand-600 hover:text-brand-700">
        &larr; <?= esc(lang('Pages.pages_details')) ?>
    </a>
    <div class="flex items-center gap-2">
        <?php if ($itemIdStr !== ''): ?>
        <a href="<?= route_to('admin.cms.pages.blocks', $itemIdStr) ?>" class="<?= esc(action_button_class('neutral')) ?>">
            <?= ui_icon('layout-template', 'h-3.5 w-3.5') ?>
            <?= esc(lang('Pages.manage_blocks')) ?>
        </a>
        <?php endif; ?>
        <form method="post" action="<?= route_to('admin.cms.pages.delete', $itemIdStr) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['title'] ?? $item['slug'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        </form>
    </div>
</div>

    <?php
    $hasTranslationIssues = false;
$issueDetails = [];
if (!empty($languages)) {
    foreach ($languages as $lang) {
        $transValue = $resolveTranslation($lang);
        if (empty($transValue)) {
            $hasTranslationIssues = true;
            $issueDetails[] = strtoupper($lang['code']) . ' (Missing)';
        } elseif (empty($transValue['title']) || empty($transValue['slug'])) {
            $hasTranslationIssues = true;
            $issueDetails[] = strtoupper($lang['code']) . ' (Incomplete)';
        }
    }
}
?>

    <?php if ($hasTranslationIssues): ?>
        <div class="mt-4 rounded-lg bg-yellow-50 border border-yellow-200 p-4 text-sm text-yellow-800 flex items-center gap-2">
            <svg class="h-5 w-5 text-yellow-600 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a1 1 0 112 0v5a1 1 0 11-2 0V5zm1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            <div>
                <span class="font-bold"><?= esc(lang('Pages.alert_translation_attention')) ?>:</span> Translation issues detected for: <span class="font-semibold"><?= implode(', ', $issueDetails) ?></span>.
            </div>
        </div>
    <?php endif; ?>

    <?php ob_start(); ?>
    <form method="post" action="<?= route_to('admin.cms.pages.update', $itemIdStr) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3" x-data="{ expandedSections: { basic: true, translations: true, publishing: false, seo: false, advanced: false } }">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="<?= esc($returnTo ?? '', 'attr') ?>">
        <div class="lg:col-span-2 space-y-0">

        <!-- SECTION: BASIC (Always expanded) -->
        <div class="border-b border-gray-200 last:border-b-0">
            <button type="button"
                    @click="expandedSections.basic = !expandedSections.basic"
                    class="w-full flex items-center justify-between px-4 py-3.5 hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <span x-show="!expandedSections.basic" class="text-gray-400">▶</span>
                    <span x-show="expandedSections.basic" class="text-gray-600">▼</span>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900"><?= esc(lang('App.form_core')) ?></h3>
                        <p class="text-xs text-gray-500 mt-0.5"><?= esc(lang('Pages.field_page_type_help')) ?></p>
                    </div>
                </div>
            </button>

            <div x-show="expandedSections.basic" class="px-4 pb-4 space-y-4 border-t border-gray-100">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <?= view('components/form/select', [
                    'name' => 'page_type',
                    'label' => 'Pages.field_page_type',
                    'required' => false,
                    'placeholder' => 'Pages.field_page_type_placeholder',
                    'help' => 'Pages.field_page_type_help',
                    'options' => array_column($pageTypes ?? [], 'label', 'key'),
                    'value' => $item['page_type'] ?? 'generic',
                    'errors' => $errors ?? []
                ]) ?>

                    <?= view('components/form/select', [
                    'name' => 'status',
                    'label' => 'Pages.field_status',
                    'required' => true,
                    'placeholder' => 'Pages.field_status_placeholder',
                    'help' => 'Pages.field_status_help',
                    'options' => [
                        'draft' => lang('Pages.status_draft'),
                        'published' => lang('Pages.status_published'),
                        'archived' => lang('Pages.status_archived')
                    ],
                    'value' => $item['status'] ?? 'draft',
                    'errors' => $errors ?? []
                ]) ?>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <?= view('components/form/relation', [
                    'name' => 'collection_id',
                    'label' => 'Pages.field_collection_id',
                    'required' => false,
                    'options' => $collections ?? [],
                    'placeholder' => 'Pages.field_collection_id_placeholder',
                    'help' => 'Pages.field_collection_id_help',
                    'value' => $item['collection_id'] ?? '',
                    'errors' => $errors ?? []
                ]) ?>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <?= view('components/form/relation', [
                    'name' => 'parent_id',
                    'label' => 'Pages.field_parent_id',
                    'required' => false,
                    'options' => $pages ?? [],
                    'placeholder' => 'Pages.field_parent_id_placeholder',
                    'help' => 'Pages.field_parent_id_help',
                    'value' => $item['parent_id'] ?? '',
                    'errors' => $errors ?? []
                ]) ?>

                    <?= view('components/form/number', [
                    'name' => 'sort_order',
                    'label' => 'Pages.field_sort_order',
                    'required' => false,
                    'value' => $item['sort_order'] ?? 0,
                    'placeholder' => 'Pages.field_sort_order_placeholder',
                    'help' => 'Pages.field_sort_order_help',
                    'errors' => $errors ?? []
                ]) ?>
                </div>
            </div>
        </div>



        <!-- SECTION: TRANSLATIONS (with collapsible language tabs) -->
        <?php if (!empty($languages)): ?>
            <?php
            $defaultLangId = (int) ($defaultLangId ?? 0);
            $defaultLangCode = (string) ($defaultLangCode ?? '');
            $defaultLangIndex = (int) ($defaultLangIndex ?? 0);
            $translateUrl = route_to('admin.cms.translate');
            ?>
            <div class="border-b border-gray-200 last:border-b-0">
                <button type="button"
                        @click="expandedSections.translations = !expandedSections.translations"
                        class="w-full flex items-center justify-between px-4 py-3.5 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span x-show="!expandedSections.translations" class="text-gray-400">▶</span>
                        <span x-show="expandedSections.translations" class="text-gray-600">▼</span>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900"><?= esc(lang('Pages.translations_title')) ?></h3>
                            <p class="text-xs text-gray-500 mt-0.5"><?= esc(lang('Pages.translations_help')) ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 ml-4">
                        <span class="text-xs font-medium text-gray-600 whitespace-nowrap">
                            <?php if ($totalLanguages > 0): ?>
                                <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700">
                                    <?= esc("$completedCount/$totalLanguages") ?>
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>
                </button>

                <div x-show="expandedSections.translations" class="px-4 pb-4 space-y-4 border-t border-gray-100">
                    <?php $initialTabId = $focusLangId > 0 ? $focusLangId : $defaultLangId; ?>
                    <div x-data="langTabs(<?= $initialTabId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">

                        <!-- Language tabs with indicators -->
                        <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-gray-200">
                            <div class="flex gap-1 flex-wrap" role="tablist">
                                <?php foreach ($languages as $lang): ?>
                                    <?php $langId = (int) $lang['id']; ?>
                                    <button type="button"
                                        role="tab"
                                        @click="setTab(<?= $langId ?>)"
                                        :aria-selected="isActive(<?= $langId ?>)"
                                        :class="isActive(<?= $langId ?>) ? 'border-b-2 border-brand-600 text-brand-700 bg-brand-50' : 'border-b-2 border-transparent text-gray-600 hover:text-gray-900 hover:bg-gray-100'"
                                        class="px-3 py-2 text-sm font-medium transition-all rounded-t-lg inline-flex items-center gap-2">
                                        <span><?= esc(strtoupper($lang['code'])) ?></span>
                                        <?php if ($translationStats[$langId]['status'] === 'complete'): ?>
                                            <span class="inline-block w-2 h-2 rounded-full bg-green-500" title="<?= lang('Pages.translation_complete') ?>"></span>
                                        <?php elseif ($translationStats[$langId]['status'] === 'incomplete'): ?>
                                            <span class="inline-block w-2 h-2 rounded-full bg-yellow-500" title="<?= lang('Pages.translation_incomplete') ?>"></span>
                                        <?php else: ?>
                                            <span class="inline-block w-2 h-2 rounded-full bg-red-500" title="<?= lang('Pages.translation_missing') ?>"></span>
                                        <?php endif; ?>
                                        <?php if (!empty($lang['is_default'])): ?>
                                            <span class="text-brand-500">★</span>
                                        <?php endif; ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <?php if (!empty($translateTargets)): ?>
                                <?php $copyMappings = cms_translation_copy_mappings(['slug', 'title', 'excerpt', 'meta_title', 'meta_description'], $languages, $defaultLangIndex); ?>
                                <button type="button" @click="copyDefaultToAll(<?= esc(json_encode($copyMappings, JSON_THROW_ON_ERROR), 'attr') ?>, '<?= esc(lang('Translations.confirm_copy_default'), 'js') ?>')" class="shrink-0 inline-flex items-center gap-1.5 text-xs text-gray-700 hover:text-gray-900 border border-gray-300 rounded px-3 py-1.5 bg-white hover:bg-gray-50 transition-colors">
                                    <?= ui_icon('copy', 'h-3.5 w-3.5') ?> <?= esc(lang('Translations.action_copy_default')) ?>
                                </button>
                                <button type="button"
                                    @click="autoTranslateAll(<?= esc(json_encode($translateTargets, JSON_THROW_ON_ERROR), 'attr') ?>)"
                                    :disabled="translating || translatingAll"
                                    class="shrink-0 inline-flex items-center gap-1.5 text-xs text-brand-600 hover:text-brand-700 border border-brand-200 rounded px-3 py-1.5 bg-brand-50 hover:bg-brand-100 transition-colors disabled:opacity-50">
                                    <span x-show="!translatingAll"><?= ui_icon('languages', 'h-3.5 w-3.5') ?> <?= esc(lang('App.translate_all')) ?></span>
                                    <span x-show="translatingAll" x-cloak><?= ui_icon('loader', 'h-3.5 w-3.5 animate-spin') ?> <span x-text="translateAllProgress"></span></span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <!-- Translation error message -->
                        <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2"></p>

                        <!-- Tab content panels -->
                        <?php foreach ($languages as $index => $lang): ?>
                            <?php
                                $langId = (int) $lang['id'];
                            $transValue = $resolveTranslation($lang);
                            $isDefault = !empty($lang['is_default']);
                            $langCode  = strtoupper($lang['code'] ?? '');
                            $fields = [
                                ['from' => sprintf('[name="translations[%d][title]"]', $defaultLangIndex),            'to' => sprintf('[name="translations[%d][title]"]', $index)],
                                ['from' => sprintf('[name="translations[%d][excerpt]"]', $defaultLangIndex),          'to' => sprintf('[name="translations[%d][excerpt]"]', $index)],
                                ['from' => sprintf('[name="translations[%d][meta_title]"]', $defaultLangIndex),       'to' => sprintf('[name="translations[%d][meta_title]"]', $index)],
                                ['from' => sprintf('[name="translations[%d][meta_description]"]', $defaultLangIndex), 'to' => sprintf('[name="translations[%d][meta_description]"]', $index)],
                            ];
                            ?>
                            <div x-show="isActive(<?= $langId ?>)" class="space-y-4">
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
                                    'label' => 'Pages.translation_title_label',
                                    'required' => !empty($lang['is_default']),
                                    'placeholder' => 'Pages.translation_title_placeholder',
                                    'help' => 'Pages.translation_title_help',
                                    'value' => old("translations.{$index}.title", $transValue['title'] ?? ''),
                                    'maxlength' => 255,
                                    'errors' => $errors ?? []
                                ]) ?>

                                <?= view('components/form/slug', [
                                    'name' => "translations[{$index}][slug]",
                                    'label' => 'Pages.translation_slug_label',
                                    'required' => !empty($lang['is_default']),
                                    'sourceId' => sprintf('[name="translations[%d][title]"]', $index),
                                    'checkUrl' => route_to('admin.cms.pages.check_slug') . '?language_id=' . (int)$lang['id'],
                                    'currentId' => $item['id'] ?? '',
                                    'value' => old("translations.{$index}.slug", $transValue['slug'] ?? ''),
                                    'help' => 'Pages.translation_slug_help',
                                    'errors' => $errors ?? []
                                ]) ?>

                                <?= view('components/form/textarea', [
                                    'name' => "translations[{$index}][excerpt]",
                                    'label' => 'Pages.translation_excerpt_label',
                                    'required' => false,
                                    'placeholder' => 'Pages.translation_excerpt_placeholder',
                                    'help' => 'Pages.translation_excerpt_help',
                                    'value' => old("translations.{$index}.excerpt", $transValue['excerpt'] ?? ''),
                                    'maxlength' => 500,
                                    'errors' => $errors ?? []
                                ]) ?>

                                <!-- SEO per language (collapsible, open if has values) -->
                                <details class="group border border-gray-200 rounded-lg bg-gray-50" <?= (!empty($transValue['meta_title']) || !empty($transValue['meta_description']) || !empty($transValue['og_image']['file_id'] ?? null) || !empty($transValue['og_image']['url'] ?? null)) ? 'open' : '' ?>>
                                    <summary class="flex cursor-pointer items-center justify-between px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 rounded-lg select-none transition-colors">
                                        <span><?= esc(lang('Pages.section_seo_per_lang')) ?></span>
                                        <svg class="h-3.5 w-3.5 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                                    </summary>
                                    <div class="px-3 pb-3 pt-2 space-y-4 border-t border-gray-200">
                                        <?= view('components/form/text', [
                                            'name' => "translations[{$index}][meta_title]",
                                            'label' => 'Pages.translation_meta_title_label',
                                            'required' => false,
                                            'placeholder' => 'Pages.translation_meta_title_placeholder',
                                            'help' => 'Pages.translation_meta_title_help',
                                            'value' => old("translations.{$index}.meta_title", $transValue['meta_title'] ?? ''),
                                            'maxlength' => 255,
                                            'errors' => $errors ?? []
                                        ]) ?>
                                        <?= view('components/form/textarea', [
                                            'name' => "translations[{$index}][meta_description]",
                                            'label' => 'Pages.translation_meta_description_label',
                                            'required' => false,
                                            'placeholder' => 'Pages.translation_meta_description_placeholder',
                                            'help' => 'Pages.translation_meta_description_help',
                                            'value' => old("translations.{$index}.meta_description", $transValue['meta_description'] ?? ''),
                                            'maxlength' => 500,
                                            'rows' => 3,
                                            'errors' => $errors ?? []
                                        ]) ?>
                                        <?= view('components/form/media_reference', [
                                            'name' => "translations[{$index}][og_image]",
                                            'label' => lang('Pages.translation_og_image_label'),
                                            'help' => lang('Pages.translation_og_image_help'),
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
            </div>
        <?php endif; ?>

        </div>
        <aside class="space-y-4">
            <!-- Action buttons panel -->
            <?php ob_start(); ?>
            <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.update')) ?></button>
            <a href="<?= $itemIdStr !== '' ? route_to('admin.cms.pages.show', $itemIdStr) : route_to('admin.cms.pages') ?>"
               class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
            <?php $actionsContent = ob_get_clean(); ?>
            <?= view('components/display/admin_actions_panel', ['content' => $actionsContent]) ?>

            <!-- Publishing & Scheduling (collapsible) -->
            <div class="border-b border-gray-200 last:border-b-0 bg-white rounded-lg overflow-hidden" x-show="expandedSections.publishing" @click="expandedSections.publishing = !expandedSections.publishing">
                <button type="button"
                        @click.stop="expandedSections.publishing = !expandedSections.publishing"
                        class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-2">
                        <span x-show="!expandedSections.publishing" class="text-gray-400">▶</span>
                        <span x-show="expandedSections.publishing" class="text-gray-600">▼</span>
                        <span class="text-sm font-medium text-gray-700"><?= esc(lang('Pages.section_publishing')) ?></span>
                    </div>
                </button>
            </div>

            <details class="group border border-gray-200 rounded-lg bg-white" x-show="!expandedSections.publishing" <?= (!empty($item['published_at']) || !empty($item['scheduled_at'])) ? 'open' : '' ?>>
                <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <span><?= esc(lang('Pages.section_publishing')) ?></span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </summary>
                <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-200">
                    <?= view('components/form/datetime', [
                    'name' => 'published_at',
                    'label' => 'Pages.field_published_at',
                    'required' => false,
                    'value' => $item['published_at'] ?? '',
                    'placeholder' => 'Pages.field_published_at_placeholder',
                    'help' => 'Pages.field_published_at_help',
                    'errors' => $errors ?? []
                ]) ?>
                    <?= view('components/form/datetime', [
                    'name' => 'scheduled_at',
                    'label' => 'Pages.field_scheduled_at',
                    'required' => false,
                    'value' => $item['scheduled_at'] ?? '',
                    'placeholder' => 'Pages.field_scheduled_at_placeholder',
                    'help' => 'Pages.field_scheduled_at_help',
                    'errors' => $errors ?? []
                ]) ?>
                </div>
            </details>

            <!-- SEO & Sitemap (collapsible) -->
            <details class="group border border-gray-200 rounded-lg bg-white" x-show="!expandedSections.seo">
                <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <span><?= esc(lang('Pages.section_seo_sitemap')) ?></span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </summary>
                <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-200">
                    <?= view('components/form/boolean', [
                    'name' => 'is_in_sitemap',
                    'label' => 'Pages.field_is_in_sitemap',
                    'value' => $item['is_in_sitemap'] ?? true,
                    'on_label' => 'Pages.field_is_in_sitemap_on',
                    'off_label' => 'Pages.field_is_in_sitemap_off',
                    'help' => 'Pages.field_is_in_sitemap_help',
                    'errors' => $errors ?? []
                ]) ?>
                    <?= view('components/form/text', [
                    'name' => 'sitemap_priority',
                    'label' => 'Pages.field_sitemap_priority',
                    'required' => false,
                    'value' => $item['sitemap_priority'] ?? '',
                    'placeholder' => 'Pages.field_sitemap_priority_placeholder',
                    'help' => 'Pages.field_sitemap_priority_help',
                    'errors' => $errors ?? []
                ]) ?>
                    <?= view('components/form/select', [
                    'name' => 'sitemap_changefreq',
                    'label' => 'Pages.field_sitemap_changefreq',
                    'required' => false,
                    'placeholder' => 'Pages.field_sitemap_changefreq_placeholder',
                    'help' => 'Pages.field_sitemap_changefreq_help',
                    'options' => [
                        'always' => lang('Pages.sitemap_changefreq_always'),
                        'hourly' => lang('Pages.sitemap_changefreq_hourly'),
                        'daily' => lang('Pages.sitemap_changefreq_daily'),
                        'weekly' => lang('Pages.sitemap_changefreq_weekly'),
                        'monthly' => lang('Pages.sitemap_changefreq_monthly'),
                        'yearly' => lang('Pages.sitemap_changefreq_yearly'),
                        'never' => lang('Pages.sitemap_changefreq_never'),
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
        'title' => 'Pages.pages_edit',
        'description' => 'Pages.pages_details',
        'content' => $sectionContent,
    ]) ?>
