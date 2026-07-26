<?php
/**
 * Site Identity — Metadata-driven view.
 *
 * Renders every setting in the 'identity' group using its input_type.
 * To add a new identity field, create a cms_setting with group='identity'
 * and the correct input_type — no code change required here.
 *
 * Layout:
 *   - Left (2/3): core identity settings + unified translations panel
 *   - Right (1/3): image/file pickers (brand assets)
 *
 * @var array<int, array<string, mixed>> $contentSettings
 * @var array<int, array<string, mixed>> $assetSettings
 * @var array<string, mixed> $translationPanel
 */

helper('cms_settings');

$contentSettings = $contentSettings ?? [];
$assetSettings = $assetSettings ?? [];
$translationPanel = is_array($translationPanel ?? null) ? $translationPanel : [];
?>

<div class="space-y-5">

    <div>
        <h1 class="text-xl font-semibold text-gray-900"><?= lang('SiteIdentity.page_title') ?></h1>
        <p class="mt-1 text-sm text-gray-500"><?= lang('SiteIdentity.section_intro') ?></p>
    </div>

    <?php if (empty($contentSettings) && empty($assetSettings)): ?>

        <div class="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">
            <?= lang('SiteIdentity.no_settings') ?>
        </div>

    <?php else: ?>

    <div class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
        </svg>
        <span><?= lang('SiteIdentity.cache_note') ?></span>
    </div>

    <form method="post"
          action="<?= route_to('admin.cms.site_identity.update') ?>"
          class="space-y-6">
        <?= csrf_field() ?>

        <?php
        $hasContent = !empty($contentSettings);
        $hasAssets  = !empty($assetSettings);
        $colClass   = ($hasContent && $hasAssets) ? 'grid grid-cols-1 gap-6 lg:grid-cols-3' : '';
        ?>
        <div class="<?= esc($colClass) ?>">

                <?php if ($hasContent): ?>
            <div class="<?= $hasAssets ? 'lg:col-span-2' : '' ?> space-y-6">

                <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= lang('SiteIdentity.core_section') ?></h3>
                            <p class="mt-1 text-xs text-gray-500"><?= lang('SiteIdentity.base_section_intro') ?></p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-gray-600">
                            <?= esc(lang('SiteIdentity.base_badge')) ?>
                        </span>
                    </div>
                    <div class="divide-y divide-gray-100">

                    <?php foreach ($contentSettings as $idx => $setting):
                        if (cms_setting_is_translatable($setting)) {
                            continue;
                        }
                        $key         = (string) ($setting['setting_key'] ?? '');
                        $inputType   = (string) ($setting['input_type'] ?? 'text');
                        $currentVal  = (string) ($setting['setting_value'] ?? '');
                        if ($key === 'footer_menu_layout' && !in_array($currentVal, ['horizontal', 'vertical'], true)) {
                            $currentVal = 'vertical';
                        } elseif ($key === 'footer_legal_menu_layout' && !in_array($currentVal, ['horizontal', 'vertical'], true)) {
                            $currentVal = 'horizontal';
                        }
                        $isTrans     = cms_setting_is_translatable($setting);
                        $isReadonly  = !empty($setting['is_readonly']);
                        $label       = cms_setting_resolve_label($setting);
                        $placeholder = cms_setting_resolve_placeholder($setting);
                        $helpText    = cms_setting_resolve_help($setting);
                        ?>
                    <div class="px-5 py-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <label class="block text-sm font-medium text-gray-700">
                                <?= esc($label) ?>
                                <?php if (!empty($setting['is_required'])): ?>
                                    <span class="text-red-400 ml-0.5" aria-hidden="true">*</span>
                                <?php endif; ?>
                            </label>
                            <?php if ($isTrans && !empty($translationPanel['translationLanguages'])): ?>
                                    <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-700">
                                        <?= esc(lang('SiteIdentity.translatable_badge')) ?>
                                    </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($inputType === 'boolean'): ?>
                            <?= view('components/form/boolean', [
                                    'name'      => "{$key}_value",
                                    'label'     => '',
                                    'value'     => filter_var($currentVal, FILTER_VALIDATE_BOOLEAN),
                                    'on_label'  => 'App.yes',
                                    'off_label' => 'App.no',
                                    'readonly'  => $isReadonly,
                                    'errors'    => $errors ?? [],
                                ]) ?>

                        <?php elseif ($inputType === 'textarea' || $inputType === 'richtext'): ?>
                            <textarea name="<?= esc($key) ?>_value"
                                      rows="4"
                                      placeholder="<?= esc($placeholder) ?>"
                                      class="form-input text-sm"
                                      <?= $isReadonly ? 'readonly' : '' ?>><?= esc($currentVal) ?></textarea>

                        <?php elseif ($inputType === 'code'): ?>
                            <textarea name="<?= esc($key) ?>_value"
                                      rows="5"
                                      placeholder="<?= esc($placeholder) ?>"
                                      class="form-input font-mono text-sm bg-gray-950 text-green-400 border-gray-700"
                                      <?= $isReadonly ? 'readonly' : '' ?>><?= esc($currentVal) ?></textarea>

                        <?php elseif ($inputType === 'select'):
                            $rawOptions = $setting['options_json'] ?? null;
                            $options    = [];
                            if (is_array($rawOptions)) {
                                $rawOptions = isset($rawOptions['options']) && is_array($rawOptions['options'])
                                    ? $rawOptions['options']
                                    : $rawOptions;
                                foreach ($rawOptions as $opt) {
                                    if (is_string($opt)) {
                                        $options[$opt] = $opt;
                                        continue;
                                    }
                                    if (!is_array($opt)) {
                                        continue;
                                    }
                                    $options[(string) ($opt['value'] ?? '')] = (string) ($opt['label'] ?? '');
                                }
                            }
                            ?>
                            <select name="<?= esc($key) ?>_value" class="form-input text-sm" <?= $isReadonly ? 'disabled' : '' ?>>
                                <?php foreach ($options as $optVal => $optLabel): ?>
                                    <option value="<?= esc($optVal) ?>" <?= ($currentVal === $optVal) ? 'selected' : '' ?>>
                                        <?= esc($optLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php else:
                            $htmlType = match ($inputType) {
                                'url'    => 'url',
                                'email'  => 'email',
                                'phone'  => 'tel',
                                'color'  => 'color',
                                'number' => 'number',
                                'slug'   => 'text',
                                default  => 'text',
                            };
                            ?>
                            <input type="<?= esc($htmlType) ?>"
                                   name="<?= esc($key) ?>_value"
                                   value="<?= esc($currentVal) ?>"
                                   placeholder="<?= esc($placeholder) ?>"
                                   class="form-input text-sm"
                                   <?= $isReadonly ? 'readonly' : '' ?>>
                        <?php endif; ?>

                        <?php if ($helpText !== ''): ?>
                            <p class="mt-1 text-xs text-gray-400"><?= esc($helpText) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    </div>
                </section>

                <?php if (!empty($translationPanel['translationLanguages'])): ?>
                    <?= view('components/form/translatable_settings_panel', [
                        'title' => lang('SiteIdentity.translations_section'),
                        'description' => lang('SiteIdentity.translations_intro'),
                        'badge' => (string) ($translationPanel['translatableFieldCount'] ?? 0) . ' ' . lang('SiteIdentity.translations_ready_suffix'),
                        'languageHelp' => lang('SiteIdentity.translation_language_help'),
                        'translateAllLabel' => lang('App.translate_all'),
                        'translateFromDefaultLabel' => lang('App.translate_from_default'),
                        'translatingLabel' => lang('App.translating'),
                        'translateUrl' => route_to('admin.cms.translate'),
                        'activeLanguageId' => (int) ($translationPanel['activeLanguageId'] ?? 0),
                        'defaultLanguageCode' => (string) ($translationPanel['defaultLanguageCode'] ?? ''),
                        'translationLanguages' => $translationPanel['translationLanguages'] ?? [],
                        'rowsByLanguage' => $translationPanel['rowsByLanguage'] ?? [],
                        'translateTargets' => $translationPanel['translateTargets'] ?? [],
                        'translateTargetsByLanguageId' => $translationPanel['translateTargetsByLanguageId'] ?? [],
                    ]) ?>
                <?php endif; ?>

            </div>
            <?php endif; ?>

            <?php if ($hasAssets): ?>
            <aside class="space-y-4">
                <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= lang('SiteIdentity.assets_section') ?></h3>
                    </div>
                    <div class="divide-y divide-gray-100 px-5">

                    <?php foreach ($assetSettings as $setting):
                        $key        = (string) ($setting['setting_key'] ?? '');
                        $inputType  = (string) ($setting['input_type'] ?? 'image');
                        $isReadonly = !empty($setting['is_readonly']);
                        $label      = cms_setting_resolve_label($setting);
                        $helpText   = cms_setting_resolve_help($setting);
                        $fileId     = (string) ($setting['setting_value'] ?? '');
                        $fpAccept   = ($inputType === 'image') ? 'image/*' : '';
                        $fpFilter   = ($inputType === 'image') ? 'image' : '';
                        ?>
                    <div class="py-4">
                        <p class="mb-2 text-sm font-medium text-gray-700"><?= esc($label) ?></p>

                        <div x-data="filePickerField({
                                name: '<?= esc("{$key}_file_id", 'js') ?>',
                                value: '<?= esc($fileId, 'js') ?>',
                                accept: '<?= esc($fpAccept, 'js') ?>',
                                filterType: '<?= esc($fpFilter, 'js') ?>'
                            })"
                             x-init="init()"
                             <?= $isReadonly ? 'data-readonly="true"' : '' ?>>

                            <input type="hidden" :name="fieldName" :value="fileId">
                            <input type="hidden" name="<?= esc($key) ?>_url" :value="fileInfo.url">
                            <input type="hidden" name="<?= esc($key) ?>_mime_type" :value="fileInfo.mime_type">

                            <!-- File selected -->
                            <div x-show="fileId !== ''" x-cloak
                                 class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center overflow-hidden rounded-md border border-gray-100 bg-white">
                                    <template x-if="fileInfo.is_image && (fileInfo.previewUrl || fileInfo.url)">
                                        <img :src="fileInfo.previewUrl || fileInfo.url" :alt="fileInfo.original_name" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!fileInfo.is_image || fileInfo.url === ''">
                                        <?= ui_icon('file', 'h-7 w-7 text-gray-400') ?>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900" x-text="fileInfo.original_name || '—'"></p>
                                    <p class="text-xs text-gray-400" x-text="fileInfo.human_size"></p>
                                </div>
                                <?php if (!$isReadonly): ?>
                                <div class="flex flex-shrink-0 flex-col gap-1">
                                    <button type="button" @click="openPicker()" class="<?= action_button_class() ?> text-xs">
                                        <?= esc(lang('Files.picker_change')) ?>
                                    </button>
                                    <button type="button" @click="clearFile()" class="<?= action_button_class('danger') ?> text-xs">
                                        <?= esc(lang('App.remove')) ?>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- No file selected -->
                            <button type="button"
                                    x-show="fileId === ''"
                                    @click="<?= $isReadonly ? '' : 'openPicker()' ?>"
                                    <?= $isReadonly ? 'disabled' : '' ?>
                                    class="flex w-full cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-5 text-center transition-colors <?= $isReadonly ? 'opacity-60 cursor-not-allowed' : 'hover:border-brand-400 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500' ?>">
                                <div class="flex flex-col items-center gap-2">
                                    <?= ui_icon('upload', 'h-7 w-7 text-gray-400') ?>
                                    <p class="text-sm text-gray-400"><?= esc(lang('SiteIdentity.select_file')) ?></p>
                                </div>
                            </button>

                            <!-- Loading -->
                            <div x-show="loading" x-cloak class="mt-1 flex items-center gap-1 text-xs text-gray-400">
                                <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <?= esc(lang('App.loading')) ?>
                            </div>
                        </div>

                        <?php if ($helpText !== ''): ?>
                            <p class="mt-1.5 text-xs text-gray-400"><?= esc($helpText) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    </div>
                </section>
            </aside>
            <?php endif; ?>

        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="<?= action_button_class('primary') ?>">
                <?= lang('App.save') ?>
            </button>
        </div>

    </form>
    <?php endif; ?>

</div>
