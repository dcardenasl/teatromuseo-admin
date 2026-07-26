<?php
$item = $item ?? [];
$selectedSettingType = old('setting_type', $item['setting_type'] ?? 'string');
$selectedInputType   = old('input_type', $item['input_type'] ?? 'text');
$baseLanguageId      = isset($baseLanguageId) && is_numeric($baseLanguageId) ? (int) $baseLanguageId : null;
$isTranslatable      = (bool) old('is_translatable', $item['is_translatable'] ?? false);
$optionsJson         = old('options_json', $item['options_json'] ?? null);
if (is_array($optionsJson)) {
    $optionsJson = json_encode($optionsJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>
<div class="mb-4">
    <a href="<?= route_to('admin.cms.settings') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<div class="space-y-6"
    x-data="{
        settingType: '<?= esc($selectedSettingType, 'js') ?>',
        inputType: '<?= esc($selectedInputType, 'js') ?>',
        isTranslatable: <?= (old('is_translatable', $item['is_translatable'] ?? false)) ? 'true' : 'false' ?>,
        showOptions: <?= ($selectedInputType === 'select') ? 'true' : 'false' ?>
    }">
    
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900"><?= esc(lang('Settings.settings_create')) ?></h2>
    </div>

    <form method="post" action="<?= route_to('admin.cms.settings.store') ?>" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <?= csrf_field() ?>

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
                            ?>
                            <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 space-y-3">
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
                                        <option value="1"><?= lang('App.yes') ?></option>
                                        <option value="0" selected><?= lang('App.no') ?></option>
                                    </select>
                                </div>

                                <div x-show="settingType === 'json'" x-cloak>
                                    <textarea name="translations[<?= $langId ?>]" rows="5" class="<?= input_class("translations[$langId]") ?> !mt-0 text-sm font-mono bg-white" :disabled="!isTranslatable || settingType !== 'json'" placeholder="{}"><?= esc($transValue) ?></textarea>
                                </div>

                                <details class="pt-1">
                                    <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600 select-none">
                                        <?= lang('Settings.ui_labels_section') ?>
                                    </summary>
                                    <div class="mt-2 space-y-2">
                                        <input type="text" name="ui_translations[<?= $langId ?>][label]" value="" class="form-input text-sm" placeholder="<?= esc(lang('Settings.field_label_placeholder')) ?>" :disabled="!isTranslatable">
                                        <input type="text" name="ui_translations[<?= $langId ?>][placeholder]" value="" class="form-input text-sm" placeholder="<?= esc(lang('Settings.field_placeholder_placeholder')) ?>" :disabled="!isTranslatable">
                                        <input type="text" name="ui_translations[<?= $langId ?>][help_text]" value="" class="form-input text-sm" placeholder="<?= esc(lang('Settings.field_help_text_placeholder')) ?>" :disabled="!isTranslatable">
                                    </div>
                                </details>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php $translationsContent = ob_get_clean(); ?>

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
                    'string'  => lang('BlockTypes.type_string'),
                    'int'     => lang('BlockTypes.type_int'),
                    'bool'    => lang('BlockTypes.type_bool'),
                    'json'    => lang('BlockTypes.type_json'),
                    'file_id' => lang('BlockTypes.type_file_id'),
                ],
                'value' => $item['setting_type'] ?? 'string',
                'errors' => $errors ?? [],
                'attributes' => ['x-model' => 'settingType'],
            ]) ?>

            <?= view('components/form/select', [
                'name' => 'input_type',
                'label' => 'Settings.field_input_type',
                'required' => true,
                'help' => 'Settings.field_input_type_help',
                'options' => [
                    'text'     => lang('Settings.input_type_text'),
                    'textarea' => lang('Settings.input_type_textarea'),
                    'richtext' => lang('Settings.input_type_richtext'),
                    'url'      => lang('Settings.input_type_url'),
                    'email'    => lang('Settings.input_type_email'),
                    'phone'    => lang('Settings.input_type_phone'),
                    'color'    => lang('Settings.input_type_color'),
                    'number'   => lang('Settings.input_type_number'),
                    'boolean'  => lang('Settings.input_type_boolean'),
                    'image'    => lang('Settings.input_type_image'),
                    'file'     => lang('Settings.input_type_file'),
                    'select'   => lang('Settings.input_type_select'),
                    'code'     => lang('Settings.input_type_code'),
                    'slug'     => lang('Settings.input_type_slug'),
                ],
                'value' => $selectedInputType,
                'errors' => $errors ?? [],
                'attributes' => ['x-model' => 'inputType', '@change' => "showOptions = inputType === 'select'"],
            ]) ?>

            <div x-show="showOptions" x-cloak>
                <?= view('components/form/textarea', [
                    'name'        => 'options_json',
                    'label'       => 'Settings.field_options_json',
                    'required'    => false,
                    'value'       => $optionsJson ?? '[{"value":"option_1","label":"Option 1"}]',
                    'placeholder' => 'Settings.field_options_json_placeholder',
                    'help'        => 'Settings.field_options_json_help',
                    'rows'        => 6,
                    'errors'      => $errors ?? [],
                    'class'       => 'font-mono text-sm bg-gray-50',
                ]) ?>
            </div>

            <div @change="isTranslatable = $event.target.checked">
                <?= view('components/form/boolean', [
                    'name'      => 'is_translatable',
                    'label'     => 'Settings.field_is_translatable',
                    'value'     => $item['is_translatable'] ?? false,
                    'on_label'  => 'Settings.field_is_translatable_on',
                    'off_label' => 'Settings.field_is_translatable_off',
                    'help'      => 'Settings.field_is_translatable_help',
                    'errors'    => $errors ?? [],
                ]) ?>
            </div>

            <?= view('components/form/boolean', [
                'name'      => 'is_required',
                'label'     => 'Settings.field_is_required',
                'value'     => $item['is_required'] ?? false,
                'on_label'  => 'App.yes',
                'off_label' => 'App.no',
                'errors'    => $errors ?? [],
            ]) ?>

            <?= view('components/form/boolean', [
                'name'      => 'is_readonly',
                'label'     => 'Settings.field_is_readonly',
                'value'     => $item['is_readonly'] ?? false,
                'on_label'  => 'App.yes',
                'off_label' => 'App.no',
                'errors'    => $errors ?? [],
            ]) ?>

            <?= view('components/form/text', [
                'name'        => 'setting_group',
                'label'       => 'Settings.field_setting_group',
                'required'    => false,
                'value'       => $item['setting_group'] ?? '',
                'placeholder' => 'Settings.field_setting_group_placeholder',
                'help'        => 'Settings.field_setting_group_help',
                'errors'      => $errors ?? [],
            ]) ?>

            <?= view('components/form/textarea', [
                'name'        => 'description',
                'label'       => 'Settings.field_description',
                'required'    => false,
                'value'       => $item['description'] ?? '',
                'placeholder' => 'Settings.field_description_placeholder',
                'help'        => 'Settings.field_description_help',
                'errors'      => $errors ?? [],
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
                <?= esc(lang('App.create')) ?>
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
