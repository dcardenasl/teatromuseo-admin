<div class="mb-4">
    <a href="<?= route_to('admin.cms.pages') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<?php ob_start(); ?>
<form method="post" action="<?= route_to('admin.cms.pages.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <?= csrf_field() ?>
        <div class="lg:col-span-2 space-y-6">

        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 space-y-4">
            <div>
                <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('App.form_core')) ?></h4>
                <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Pages.field_page_type_help')) ?></p>
            </div>

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


        <!-- Translations with language tabs -->
        <?php if (!empty($languages)): ?>
            <?php
                $defaultLangId    = (int) ($defaultLangId ?? 0);
            $defaultLangIndex = (int) ($defaultLangIndex ?? 0);
            $defaultLangCode  = (string) ($defaultLangCode ?? '');
            $translateUrl = route_to('admin.cms.translate');
            $checkSlugBase = route_to('admin.cms.pages.check_slug');
            ?>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4">
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Pages.translations_title')) ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Pages.translations_help')) ?></p>
                </div>

                <div x-data="langTabs(<?= $defaultLangId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
                    <!-- Tab bar + translate-all button -->
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

                    <!-- Tab panels — x-show keeps inputs in DOM for form submission -->
                    <?php foreach ($languages as $index => $lang): ?>
                        <?php
                            $isDefault = !empty($lang['is_default']);
                        $langCode  = strtoupper($lang['code'] ?? '');
                        $checkUrl  = $checkSlugBase . '?language_id=' . (int) $lang['id'];
                        // Field pairs for auto-translate: from default tab → this tab
                        $fields = [
                            ['from' => sprintf('[name="translations[%d][title]"]', $defaultLangIndex),       'to' => sprintf('[name="translations[%d][title]"]', $index)],
                            ['from' => sprintf('[name="translations[%d][excerpt]"]', $defaultLangIndex),     'to' => sprintf('[name="translations[%d][excerpt]"]', $index)],
                            ['from' => sprintf('[name="translations[%d][meta_title]"]', $defaultLangIndex),  'to' => sprintf('[name="translations[%d][meta_title]"]', $index)],
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
                                'label' => 'Pages.translation_title_label',
                                'required' => $isDefault,
                                'placeholder' => 'Pages.translation_title_placeholder',
                                'help' => 'Pages.translation_title_help',
                                'value' => old("translations.{$index}.title", ''),
                                'maxlength' => 255,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/slug', [
                                'name' => "translations[{$index}][slug]",
                                'label' => 'Pages.translation_slug_label',
                                'required' => $isDefault,
                                'sourceId' => sprintf('[name="translations[%d][title]"]', $index),
                                'checkUrl' => $checkUrl,
                                'value' => old("translations.{$index}.slug", ''),
                                'help' => 'Pages.translation_slug_help',
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][excerpt]",
                                'label' => 'Pages.translation_excerpt_label',
                                'required' => false,
                                'placeholder' => 'Pages.translation_excerpt_placeholder',
                                'help' => 'Pages.translation_excerpt_help',
                                'value' => old("translations.{$index}.excerpt", ''),
                                'maxlength' => 500,
                                'errors' => $errors ?? []
                            ]) ?>

                            <!-- SEO per language (collapsed) -->
                            <details class="group border border-gray-100 rounded-lg bg-gray-50/30">
                                <summary class="flex cursor-pointer items-center justify-between px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 rounded-lg select-none">
                                    <span><?= esc(lang('Pages.section_seo_per_lang')) ?></span>
                                    <svg class="h-3.5 w-3.5 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                                </summary>
                                <div class="px-3 pb-3 pt-2 space-y-4 border-t border-gray-100">
                                    <?= view('components/form/text', [
                                        'name' => "translations[{$index}][meta_title]",
                                        'label' => 'Pages.translation_meta_title_label',
                                        'required' => false,
                                        'placeholder' => 'Pages.translation_meta_title_placeholder',
                                        'help' => 'Pages.translation_meta_title_help',
                                        'value' => old("translations.{$index}.meta_title", ''),
                                        'maxlength' => 255,
                                        'errors' => $errors ?? []
                                    ]) ?>
                                    <?= view('components/form/textarea', [
                                        'name' => "translations[{$index}][meta_description]",
                                        'label' => 'Pages.translation_meta_description_label',
                                        'required' => false,
                                        'placeholder' => 'Pages.translation_meta_description_placeholder',
                                        'help' => 'Pages.translation_meta_description_help',
                                        'value' => old("translations.{$index}.meta_description", ''),
                                        'maxlength' => 500,
                                        'rows' => 3,
                                        'errors' => $errors ?? []
                                    ]) ?>
                                    <?= view('components/form/media_reference', [
                                        'name' => "translations[{$index}][og_image]",
                                        'label' => lang('Pages.translation_og_image_label'),
                                        'help' => lang('Pages.translation_og_image_help'),
                                        'value' => old("translations.{$index}.og_image", []),
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
            <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.create')) ?></button>
            <a href="<?= route_to('admin.cms.pages') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
            <?php $actionsContent = ob_get_clean(); ?>
            <?= view('components/display/admin_actions_panel', ['content' => $actionsContent]) ?>

            <!-- Publishing & Scheduling -->
            <details class="group rounded-xl border border-gray-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                    <span><?= esc(lang('Pages.section_publishing')) ?></span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </summary>
                <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
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

            <details class="group rounded-xl border border-gray-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                    <span><?= esc(lang('Pages.section_seo_sitemap')) ?></span>
                    <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
                </summary>
                <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
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
    'title' => 'Pages.pages_create',
    'description' => 'Pages.pages_details',
    'content' => $sectionContent,
]) ?>
