<?php
$item = $item ?? [];
$itemId = (string) ($item['id'] ?? '');
?>

<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.cms.tags'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Tags.tags_details',
    'title' => 'Tags.tags_edit',
    'subtitle' => (string) ($item['slug'] ?? $item['name'] ?? ''),
]) ?>

<form method="post" action="<?= route_to('admin.cms.tags.update', $itemId) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= esc($returnTo ?? '', 'attr') ?>">

    <div class="lg:col-span-2 space-y-6">
        <?php ob_start(); ?>
        <?php if (!empty($languages)): ?>
            <?php
            $defaultLangId = (int) ($defaultLangId ?? 0);
            $defaultLangCode = (string) ($defaultLangCode ?? '');
            $defaultLangIndex = (int) ($defaultLangIndex ?? 0);
            $focusLangId = (int) ($focusLangId ?? 0);
            $initialTabId = $focusLangId > 0 ? $focusLangId : $defaultLangId;
            $translateUrl = route_to('admin.cms.translate');
            $translations = is_array($item['translations'] ?? null) ? $item['translations'] : [];
            ?>
            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/60">
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-900"><?= esc(lang('Tags.translations_title')) ?></h4>
                    <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Tags.tags_details')) ?></p>
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
                            <?php $copyMappings = cms_translation_copy_mappings(['name', 'slug'], $languages, $defaultLangIndex); ?>
                            <button type="button" @click="copyDefaultToAll(<?= esc(json_encode($copyMappings, JSON_THROW_ON_ERROR), 'attr') ?>, '<?= esc(lang('Translations.confirm_copy_default'), 'js') ?>')" class="shrink-0 inline-flex items-center gap-1.5 text-xs text-gray-700 border border-gray-300 rounded px-3 py-1.5 bg-white hover:bg-gray-50"><?= ui_icon('copy', 'h-3.5 w-3.5') ?> <?= esc(lang('Translations.action_copy_default')) ?></button>
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
                        foreach ($translations as $translation) {
                            if (is_array($translation) && (int) ($translation['language_id'] ?? 0) === (int) $lang['id']) {
                                $transValue = $translation;
                                break;
                            }
                        }
                        ?>
                        <div x-show="isActive(<?= (int) $lang['id'] ?>)" class="space-y-4">
                            <input type="hidden" name="translations[<?= $index ?>][language_id]" value="<?= esc($lang['id']) ?>">

                            <?= view('components/form/text', [
                                'name' => "translations[{$index}][name]",
                                'label' => 'Tags.translation_name_label',
                                'required' => !empty($lang['is_default']),
                                'placeholder' => 'Tags.translation_name_placeholder',
                                'help' => 'Tags.translation_name_help',
                                'value' => old("translations.{$index}.name", $transValue['name'] ?? ''),
                                'errors' => $errors ?? []
                            ]) ?>

                            <?= view('components/form/text', [
                                'name' => "translations[{$index}][slug]",
                                'label' => 'Tags.translation_slug_label',
                                'required' => !empty($lang['is_default']),
                                'placeholder' => 'Tags.translation_slug_placeholder',
                                'help' => 'Tags.translation_slug_help',
                                'value' => old("translations.{$index}.slug", $transValue['slug'] ?? ''),
                                'errors' => $errors ?? []
                            ]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php $translationsContent = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'Tags.translations_title',
            'description' => 'Tags.tags_details',
            'content' => $translationsContent,
        ]) ?>
    </div>

    <aside class="space-y-6">
        <?php ob_start(); ?>
        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Tags.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'Tags.field_is_active_on',
            'off_label' => 'Tags.field_is_active_off',
            'help' => 'Tags.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>
        <?php $metaFields = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'Tags.tags_details',
            'content' => $metaFields,
            'bodyClass' => 'space-y-4',
        ]) ?>

        <?php ob_start(); ?>
        <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.update')) ?></button>
        <a href="<?= route_to('admin.cms.tags') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
        <?php $actionsContent = ob_get_clean(); ?>

        <?php ob_start(); ?>
        <button type="submit" form="delete-tag-form" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
        <?php $dangerContent = ob_get_clean(); ?>

        <?= view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]) ?>
    </aside>
</form>

<form id="delete-tag-form" method="post" action="<?= route_to('admin.cms.tags.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['name'] ?? $item['slug'] ?? null), 'js') ?>', () => $el.submit())">
    <?= csrf_field() ?>
</form>
