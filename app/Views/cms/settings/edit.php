<?php
$item = $item ?? [];
$selectedSettingType = old('setting_type', $item['setting_type'] ?? 'string');
$baseLanguageId = isset($baseLanguageId) && is_numeric($baseLanguageId) ? (int) $baseLanguageId : null;
$isTranslatable = (bool) old('is_translatable', $item['is_translatable'] ?? false);
?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= route_to('admin.cms.settings') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
    <form method="post" action="<?= route_to('admin.cms.settings.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['setting_key'] ?? $item['setting_group'] ?? null), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
</div>

<div class="space-y-6"
    x-data="{
        settingType: '<?= esc($selectedSettingType, 'js') ?>',
        isTranslatable: <?= $isTranslatable ? 'true' : 'false' ?>
    }">
    
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900"><?= esc(lang('Settings.settings_edit')) ?></h2>
    </div>

    <form method="post" action="<?= route_to('admin.cms.settings.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <?= csrf_field() ?>
        <input type="hidden" name="return_to" value="<?= esc($returnTo ?? '', 'attr') ?>">

        <div class="lg:col-span-2 space-y-6">
            <?php ob_start(); ?>
            <div class="space-y-4">
                <div x-show="settingType === 'string' || settingType === 'file_id'">
                    <?= view('components/form/text', [
                        'name' => 'setting_value_string',
                        'label' => 'Settings.field_setting_value',
                        'required' => false,
                        'value' => in_array($selectedSettingType, ['string', 'file_id'], true) ? ($item['setting_value'] ?? '') : '',
                        'placeholder' => 'Settings.field_setting_value_placeholder',
                        'errors' => $errors ?? [],
                        'attributes' => [':name' => "(settingType === 'string' || settingType === 'file_id') ? 'setting_value' : ''"]
                    ]) ?>
                </div>
                <div x-show="settingType === 'int'" x-cloak>
                    <?= view('components/form/number', [
                        'name' => 'setting_value_int',
                        'label' => 'Settings.field_setting_value',
                        'required' => false,
                        'value' => $selectedSettingType === 'int' ? ($item['setting_value'] ?? '') : '',
                        'placeholder' => 'Settings.field_setting_value_placeholder',
                        'errors' => $errors ?? [],
                        'attributes' => [':name' => "settingType === 'int' ? 'setting_value' : ''"]
                    ]) ?>
                </div>
                <div x-show="settingType === 'bool'" x-cloak>
                    <?= view('components/form/boolean', [
                        'name' => 'setting_value_bool',
                        'label' => 'Settings.field_setting_value',
                        'value' => $selectedSettingType === 'bool' ? filter_var($item['setting_value'] ?? false, FILTER_VALIDATE_BOOLEAN) : false,
                        'on_label' => 'App.yes',
                        'off_label' => 'App.no',
                        'errors' => $errors ?? [],
                        'attributes' => [':name' => "settingType === 'bool' ? 'setting_value' : ''"]
                    ]) ?>
                </div>
                <div x-show="settingType === 'json'" x-cloak>
                    <?= view('components/form/textarea', [
                        'name' => 'setting_value_json',
                        'label' => 'Settings.field_setting_value',
                        'required' => false,
                        'value' => $selectedSettingType === 'json' ? ($item['setting_value'] ?? '') : '',
                        'placeholder' => 'Settings.field_setting_value_placeholder',
                        'errors' => $errors ?? [],
                        'rows' => 8,
                        'class' => 'font-mono text-sm bg-gray-50 border-gray-300 focus:bg-white',
                        'attributes' => [':name' => "settingType === 'json' ? 'setting_value' : ''"]
                    ]) ?>
                </div>
            </div>
            <?= render_field_error('setting_value') ?>
            <?php $baseValueContent = ob_get_clean(); ?>

            <?= view('components/display/form_section', [
                'title' => 'Settings.settings_value_section',
                'description' => $isTranslatable ? 'Settings.field_base_value_help' : 'Settings.field_setting_type_help',
                'badge' => $isTranslatable ? 'Settings.field_base_value' : 'Settings.field_is_translatable_off',
                'content' => $baseValueContent,
                'bodyClass' => 'space-y-4'
            ]) ?>

            <?php if (!empty($languages)): ?>
                <div x-show="isTranslatable" x-cloak>
                    <?php ob_start(); ?>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach ($languages as $lang): ?>
                            <?php
                                $langId = (int) $lang['id'];
                            if ($baseLanguageId !== null && $langId === $baseLanguageId) {
                                continue;
                            }
                            $langName = esc($lang['native_name'] ?? $lang['name']);
                            $langCode = esc(strtoupper($lang['code']));
                            $transValue = '';
                            if (!empty($item['translations'])) {
                                foreach ($item['translations'] as $t) {
                                    if ((int) ($t['language_id'] ?? 0) === $langId) {
                                        $transValue = $t['setting_value'] ?? '';
                                        break;
                                    }
                                }
                            }
                            ?>
                            <?php $isFocusedLang = (int) ($focusLangId ?? 0) === $langId; ?>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 space-y-3" <?php if ($isFocusedLang): ?>x-data x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'center' })"<?php endif; ?>>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center font-bold px-2.5 py-1 rounded bg-blue-50 text-blue-700 text-xs border border-blue-200 shrink-0">
                                        <?= $langCode ?>
                                    </span>
                                    <span class="text-xs font-bold text-gray-700"><?= $langName ?></span>
                                </div>
                                <div x-show="settingType === 'string' || settingType === 'file_id'">
                                    <input type="text" name="translations[<?= $langId ?>]" value="<?= esc($transValue) ?>" class="<?= input_class("translations[$langId]") ?> !mt-0 text-sm" :disabled="!isTranslatable || !(settingType === 'string' || settingType === 'file_id')" placeholder="<?= esc(lang('Settings.field_setting_value_placeholder')) ?> (<?= strtolower($langName) ?>)">
                                </div>
                                <div x-show="settingType === 'int'" x-cloak>
                                    <input type="number" name="translations[<?= $langId ?>]" value="<?= esc($transValue) ?>" class="<?= input_class("translations[$langId]") ?> !mt-0 text-sm" :disabled="!isTranslatable || settingType !== 'int'" placeholder="0">
                                </div>
                                <div x-show="settingType === 'bool'" x-cloak>
                                    <select name="translations[<?= $langId ?>]" class="<?= input_class("translations[$langId]") ?> !mt-0 text-sm" :disabled="!isTranslatable || settingType !== 'bool'">
                                        <option value="1" <?= $transValue === '1' || $transValue === 'true' || $transValue === true ? 'selected' : '' ?>><?= lang('App.yes') ?></option>
                                        <option value="0" <?= $transValue === '0' || $transValue === 'false' || $transValue === false || $transValue === '' || $transValue === null ? 'selected' : '' ?>><?= lang('App.no') ?></option>
                                    </select>
                                </div>
                                <div x-show="settingType === 'json'" x-cloak>
                                    <textarea name="translations[<?= $langId ?>]" rows="5" class="<?= input_class("translations[$langId]") ?> !mt-0 text-sm font-mono bg-white" :disabled="!isTranslatable || settingType !== 'json'" placeholder="{}"><?= esc($transValue) ?></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php $translationsContent = ob_get_clean(); ?>

                    <?php
                    $copyTargets = [];
                foreach ($languages as $lang) {
                    $langId = (int) $lang['id'];
                    if ($baseLanguageId !== null && $langId === $baseLanguageId) {
                        continue;
                    }
                    $copyTargets[] = '[name="translations[' . $langId . ']"]';
                }
                $copySource = '[name="setting_value_string"], [name="setting_value_int"], [name="setting_value_bool"][type="checkbox"], [name="setting_value_json"]';
                $copyMappings = [['source' => $copySource, 'targets' => $copyTargets]];
                ?>
                    <div class="mb-4 flex justify-end">
                        <button type="button" x-show="isTranslatable" @click="window.copyDefaultToAll(<?= esc(json_encode($copyMappings, JSON_THROW_ON_ERROR), 'attr') ?>, '<?= esc(lang('Translations.confirm_copy_default'), 'js') ?>')" class="inline-flex items-center gap-1.5 text-xs text-gray-700 border border-gray-300 rounded px-3 py-1.5 bg-white hover:bg-gray-50">
                            <?= ui_icon('copy', 'h-3.5 w-3.5') ?> <?= esc(lang('Translations.action_copy_default')) ?>
                        </button>
                    </div>

                    <?= view('components/display/form_section', [
                    'title' => 'Settings.settings_translations',
                    'description' => 'Settings.field_is_translatable_help',
                    'badge' => 'Settings.field_is_translatable',
                    'content' => $translationsContent,
                    'bodyClass' => 'space-y-4'
                ]) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <?php ob_start(); ?>
            <?= view('components/form/text', [
                'name' => 'setting_key',
                'label' => 'Settings.field_setting_key',
                'required' => true,
                'value' => $item['setting_key'] ?? '',
                'placeholder' => 'Settings.field_setting_key_placeholder',
                'help' => 'Settings.field_setting_key_help',
                'errors' => $errors ?? []
            ]) ?>
            <?= view('components/form/select', [
                'name' => 'setting_type',
                'label' => 'Settings.field_setting_type',
                'required' => true,
                'placeholder' => 'Settings.field_setting_type_placeholder',
                'help' => 'Settings.field_setting_type_help',
                'options' => [
                'string' => lang('BlockTypes.type_string'),
                'int' => lang('BlockTypes.type_int'),
                'bool' => lang('BlockTypes.type_bool'),
                'json' => lang('BlockTypes.type_json'),
                'file_id' => lang('BlockTypes.type_file_id')
                ],
                'value' => $item['setting_type'] ?? 'string',
                'errors' => $errors ?? [],
                'attributes' => ['x-model' => 'settingType']
            ]) ?>
            <div @change="isTranslatable = $event.target.checked">
                <?= view('components/form/boolean', [
                'name' => 'is_translatable',
                'label' => 'Settings.field_is_translatable',
                'value' => $item['is_translatable'] ?? false,
                'on_label' => 'Settings.field_is_translatable_on',
                'off_label' => 'Settings.field_is_translatable_off',
                'help' => 'Settings.field_is_translatable_help',
                'errors' => $errors ?? []
                ]) ?>
            </div>
            <?= view('components/form/text', [
                'name' => 'setting_group',
                'label' => 'Settings.field_setting_group',
                'required' => false,
                'value' => $item['setting_group'] ?? '',
                'placeholder' => 'Settings.field_setting_group_placeholder',
                'help' => 'Settings.field_setting_group_help',
                'errors' => $errors ?? []
            ]) ?>
            <?= view('components/form/textarea', [
                'name' => 'description',
                'label' => 'Settings.field_description',
                'required' => false,
                'value' => $item['description'] ?? '',
                'placeholder' => 'Settings.field_description_placeholder',
                'help' => 'Settings.field_description_help',
                'errors' => $errors ?? []
            ]) ?>
            <?php $propertiesContent = ob_get_clean(); ?>

            <?= view('components/display/form_section', [
                'title' => 'Settings.settings_metadata_section',
                'description' => 'Settings.settings_details',
                'content' => $propertiesContent,
                'bodyClass' => 'space-y-4'
            ]) ?>

            <?php ob_start(); ?>
            <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5">
                <?= esc(lang('App.update')) ?>
            </button>
            <a href="<?= route_to('admin.cms.settings') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5">
                <?= esc(lang('App.cancel')) ?>
            </a>
            <?php $actionsContent = ob_get_clean(); ?>

            <?= view('components/display/form_section', [
                'title' => 'App.actions',
                'content' => $actionsContent,
                'bodyClass' => 'space-y-3'
            ]) ?>
        </div>
    </form>
</div>
