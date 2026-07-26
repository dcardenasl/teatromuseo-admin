<?php $item = $item ?? []; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.collections') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.collections.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['name'] ?? $item['collection_key'] ?? null), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<?php ob_start(); ?>
<form method="post" action="<?= route_to('admin.cms.collections.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= esc($returnTo ?? '', 'attr') ?>">
    <input type="hidden" name="current_id" value="<?= esc((string) ($item['id'] ?? '')) ?>">
    <div class="lg:col-span-2 space-y-6">
        <?php $checkSlugBase = route_to('admin.cms.collections.check_slug'); ?>
        <?php $currentCollectionId = (string) ($item['id'] ?? ''); ?>

        <?php if (!empty($languages)): ?>
            <?php
            $defaultLangId = (int) ($defaultLangId ?? 0);
            $defaultLangCode = (string) ($defaultLangCode ?? '');
            $defaultLangIndex = (int) ($defaultLangIndex ?? 0);
            $focusLangId = (int) ($focusLangId ?? 0);
            $initialTabId = $focusLangId > 0 ? $focusLangId : $defaultLangId;
            $translateUrl = route_to('admin.cms.translate');
            $translateTargets = is_array($translateTargets ?? null) ? $translateTargets : [];
            $translations = is_array($item['translations'] ?? null) ? $item['translations'] : [];
            ?>
            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4">
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Collections.translation_title')) ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Collections.translations_help')) ?></p>
                </div>

                <input type="hidden" name="default_language_id" value="<?= esc((string) $defaultLangId) ?>">

                <div x-data="langTabs(<?= $initialTabId ?>, '<?= esc($translateUrl, 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-200 mb-4">
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
                        <?php if ($translateTargets !== []): ?>
                            <?php $copyMappings = cms_translation_copy_mappings(['name', 'slug', 'description', 'entry_cta_label'], $languages, $defaultLangIndex); ?>
                            <button type="button" @click="copyDefaultToAll(<?= esc(json_encode($copyMappings, JSON_THROW_ON_ERROR), 'attr') ?>, '<?= esc(lang('Translations.confirm_copy_default'), 'js') ?>')" class="shrink-0 inline-flex items-center gap-1.5 text-xs text-gray-700 border border-gray-300 rounded px-3 py-1.5 bg-white hover:bg-gray-50"><?= ui_icon('copy', 'h-3.5 w-3.5') ?> <?= esc(lang('Translations.action_copy_default')) ?></button>
                            <?= view('layouts/partials/translate_button', [
                                'translateTargets' => $translateTargets,
                            ]) ?>
                        <?php endif; ?>
                    </div>

                    <p x-show="translateError !== ''" x-text="translateError" x-cloak class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600"></p>

                    <?php foreach ($languages as $index => $lang): ?>
                        <?php $transValue = [];
                        foreach ($translations as $translation) {
                            if (is_array($translation) && (int) ($translation['language_id'] ?? 0) === (int) $lang['id']) {
                                $transValue = $translation;
                                break;
                            }
                        } ?>
                        <div x-show="isActive(<?= (int) $lang['id'] ?>)" class="space-y-4">
                            <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                            <?= view('components/form/text', [
                                'name' => "translations[{$index}][name]",
                                'label' => 'Collections.translation_name_label',
                                'required' => !empty($lang['is_default']),
                                'placeholder' => 'Collections.translation_name_placeholder',
                                'help' => 'Collections.translation_name_help',
                                'value' => old("translations.{$index}.name", $transValue['name'] ?? ''),
                                'maxlength' => 150,
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/slug', [
                                'name' => "translations[{$index}][slug]",
                                'label' => 'Collections.translation_slug_label',
                                'required' => !empty($lang['is_default']),
                                'sourceId' => sprintf('[name="translations[%d][name]"]', $index),
                                'checkUrl' => $checkSlugBase . '?language_id=' . (int) $lang['id'],
                                'currentId' => $currentCollectionId,
                                'placeholder' => 'Collections.translation_slug_placeholder',
                                'help' => 'Collections.translation_slug_help',
                                'value' => old("translations.{$index}.slug", $transValue['slug'] ?? ''),
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/textarea', [
                                'name' => "translations[{$index}][description]",
                                'label' => 'Collections.translation_description_label',
                                'required' => false,
                                'placeholder' => 'Collections.translation_description_placeholder',
                                'help' => 'Collections.translation_description_help',
                                'value' => old("translations.{$index}.description", $transValue['description'] ?? ''),
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/text', [
                                'name' => "translations[{$index}][entry_cta_label]",
                                'label' => 'Collections.translation_entry_cta_label_label',
                                'required' => false,
                                'placeholder' => 'Collections.translation_entry_cta_label_placeholder',
                                'help' => 'Collections.translation_entry_cta_label_help',
                                'value' => old("translations.{$index}.entry_cta_label", $transValue['entry_cta_label'] ?? ''),
                                'maxlength' => 100,
                                'errors' => $errors ?? []
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4 space-y-4">
            <div>
                <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Collections.section_identity')) ?></h4>
                <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Collections.field_collection_key_edit_help')) ?></p>
            </div>

            <?= view('components/form/text', [
                'name' => 'collection_key',
                'label' => 'Collections.field_collection_key',
                'required' => true,
                'value' => $item['collection_key'] ?? '',
                'placeholder' => 'Collections.field_collection_key_placeholder',
                'help' => 'Collections.field_collection_key_help',
                'errors' => $errors ?? []
            ]) ?>

            <?php $collectionTypeSuggestions = is_array($collectionTypeSuggestions ?? null) ? $collectionTypeSuggestions : []; ?>
            <?= view('components/form/text', [
                'name' => 'collection_type',
                'label' => 'Collections.field_collection_type',
                'required' => false,
                'value' => $item['collection_type'] ?? '',
                'placeholder' => 'Collections.field_collection_type_placeholder',
                'help' => 'Collections.field_collection_type_help',
                'maxlength' => 50,
                'attributes' => ['list' => 'collection_type_options'],
                'errors' => $errors ?? []
            ]) ?>
            <datalist id="collection_type_options">
                <?php foreach ($collectionTypeSuggestions as $suggestion): ?>
                    <option value="<?= esc($suggestion, 'attr') ?>"></option>
                <?php endforeach; ?>
            </datalist>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <?= view('components/form/boolean', [
                    'name' => 'is_active',
                    'label' => 'Collections.field_is_active',
                    'value' => $item['is_active'] ?? true,
                    'on_label' => 'Collections.field_is_active_on',
                    'off_label' => 'Collections.field_is_active_off',
                    'help' => 'Collections.field_is_active_help',
                    'errors' => $errors ?? []
                ]) ?>

                <?= view('components/form/boolean', [
                    'name' => 'requires_approval',
                    'label' => 'Collections.field_requires_approval',
                    'value' => $item['requires_approval'] ?? false,
                    'on_label' => 'Collections.field_requires_approval_on',
                    'off_label' => 'Collections.field_requires_approval_off',
                    'help' => 'Collections.field_requires_approval_help',
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </div>

        <details class="group rounded-xl border border-gray-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span><?= esc(lang('Collections.section_taxonomy')) ?></span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                <?= view('components/form/boolean', [
                    'name' => 'enables_categories',
                    'label' => 'Collections.field_enables_categories',
                    'value' => $item['enables_categories'] ?? true,
                    'on_label' => 'Collections.field_enables_categories_on',
                    'off_label' => 'Collections.field_enables_categories_off',
                    'help' => 'Collections.field_enables_categories_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/boolean', [
                    'name' => 'enables_tags',
                    'label' => 'Collections.field_enables_tags',
                    'value' => $item['enables_tags'] ?? true,
                    'on_label' => 'Collections.field_enables_tags_on',
                    'off_label' => 'Collections.field_enables_tags_off',
                    'help' => 'Collections.field_enables_tags_help',
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </details>

        <details class="group rounded-xl border border-gray-200 bg-white">
            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg select-none">
                <span><?= esc(lang('Collections.section_seo_defaults')) ?></span>
                <svg class="h-4 w-4 text-gray-400 transition-transform group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="px-4 pb-4 pt-2 space-y-4 border-t border-gray-100">
                <?= view('components/form/number', [
                    'name' => 'sort_order',
                    'label' => 'Collections.field_sort_order',
                    'required' => false,
                    'value' => $item['sort_order'] ?? 0,
                    'placeholder' => 'Collections.field_sort_order_placeholder',
                    'help' => 'Collections.field_sort_order_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/decimal', [
                    'name' => 'default_sitemap_priority',
                    'label' => 'Collections.field_default_sitemap_priority',
                    'required' => false,
                    'value' => $item['default_sitemap_priority'] ?? '0.5',
                    'placeholder' => 'Collections.field_default_sitemap_priority_placeholder',
                    'help' => 'Collections.field_default_sitemap_priority_help',
                    'errors' => $errors ?? []
                ]) ?>
                <?= view('components/form/select', [
                    'name' => 'default_changefreq',
                    'label' => 'Collections.field_default_changefreq',
                    'required' => false,
                    'placeholder' => 'Collections.field_default_changefreq_placeholder',
                    'help' => 'Collections.field_default_changefreq_help',
                    'options' => [
                        'always' => lang('Collections.frequency_always'),
                        'hourly' => lang('Collections.frequency_hourly'),
                        'daily' => lang('Collections.frequency_daily'),
                        'weekly' => lang('Collections.frequency_weekly'),
                        'monthly' => lang('Collections.frequency_monthly'),
                        'yearly' => lang('Collections.frequency_yearly'),
                        'never' => lang('Collections.frequency_never'),
                    ],
                    'value' => $item['default_changefreq'] ?? 'weekly',
                    'errors' => $errors ?? []
                ]) ?>
            </div>
        </details>

        <?php
        $templateData = [];
if (is_array($item['block_template'] ?? null)) {
    $templateData = $item['block_template'];
} elseif (is_string($item['block_template'] ?? null)) {
    $decodedTemplate = json_decode((string) $item['block_template'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedTemplate)) {
        $templateData = $decodedTemplate;
    }
}
$templateBlocks = is_array($templateData['blocks'] ?? null) ? $templateData['blocks'] : [];
$templateCount = count($templateBlocks);
?>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Collections.collections_structure')) ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Collections.collections_structure_help')) ?></p>
                </div>
                <a href="<?= route_to('admin.cms.collections.structure', (string) ($item['id'] ?? '')) ?>" class="<?= esc(action_button_class('primary')) ?> px-3 py-1.5 text-xs font-medium">
                    <?= esc(lang('Collections.collections_manage_structure')) ?>
                </a>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-gray-200 bg-gray-50/70 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500"><?= esc(lang('Collections.block_template_builder_count')) ?></p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900"><?= esc((string) $templateCount) ?></p>
                </div>
                <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50/40 px-4 py-3 text-sm text-gray-600">
                    <?= $templateCount > 0
                ? esc(lang('Collections.collections_structure_has_template'))
                : esc(lang('Collections.collections_structure_empty')) ?>
                </div>
            </div>
        </div>
    </div>
    <aside class="space-y-6">
        <?php ob_start(); ?>
        <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.update')) ?></button>
        <a href="<?= route_to('admin.cms.collections') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
        <?php $actionsContent = ob_get_clean(); ?>
        <?= view('components/display/admin_actions_panel', ['content' => $actionsContent]) ?>
    </aside>
</form>
<?php $sectionContent = ob_get_clean(); ?>
<?= view('components/display/form_section', [
    'title' => 'Collections.collections_edit',
    'description' => 'Collections.collections_details',
    'content' => $sectionContent,
]) ?>
