<?php

use App\Modules\Cms\Support\TranslationStatus;

$page      = $page      ?? [];
$block     = $block     ?? [];
$blockType = $blockType ?? [];
$languages = $languages ?? [];
$focusLangId = $focusLangId ?? 0;
$blockTranslationStatus = $blockTranslationStatus ?? [];

$schema      = is_array($blockType['schema_definition'] ?? [])
    ? ($blockType['schema_definition'] ?? [])
    : json_decode((string) ($blockType['schema_definition'] ?? '{}'), true);
$fields      = $blockType['fields']       ?? $schema['fields']       ?? [];
$configFields = $blockType['config_fields'] ?? $schema['config_fields'] ?? [];
$blockConfig  = is_array($block['block_config'] ?? []) ? ($block['block_config'] ?? []) : [];
$ownerType    = $ownerType    ?? 'page';
$ownerLabel   = $ownerLabel   ?? 'Página';
$ownerBlocksRoute = $ownerBlocksRoute ?? 'admin.cms.pages.blocks';
$ownerEditRoute   = $ownerEditRoute ?? 'admin.cms.pages.blocks.edit';
$ownerUpdateRoute = $ownerUpdateRoute ?? 'admin.cms.pages.blocks.update';
$ownerDeleteRoute = $ownerDeleteRoute ?? 'admin.cms.pages.blocks.delete';
$ownerChildrenRoute = $ownerChildrenRoute ?? 'admin.cms.pages.blocks.children';

$submittedBlockConfig = old('block_config', $blockConfig);
if (! is_array($submittedBlockConfig)) {
    $submittedBlockConfig = $blockConfig;
}

$submittedTranslations = old('translations', $block['translations'] ?? []);
if (! is_array($submittedTranslations)) {
    $submittedTranslations = [];
}

$translationsByLanguage = [];
foreach ($submittedTranslations as $translation) {
    if (! is_array($translation)) {
        continue;
    }

    $languageId = (int) ($translation['language_id'] ?? 0);
    if ($languageId > 0) {
        $translationsByLanguage[$languageId] = $translation;
    }
}

$sortOrderValue = old('sort_order', $block['sort_order'] ?? 0);
$isActiveValue  = (bool) old('is_active', ! empty($block['is_active']));
$blockIdValue   = old('block_id', (string) ($block['block_id'] ?? ''));

$defaultLangId = (int) ($defaultLangId ?? 0);
$defaultLangCode = strtoupper((string) ($defaultLangCode ?? 'EN'));
$isImageAccept = static function (string $accept): bool {
    $normalized = strtolower(trim($accept));

    return $normalized === 'image'
        || $normalized === 'image/*'
        || str_starts_with($normalized, 'image/');
};

$blockKey    = $blockType['block_key'] ?? '';
$previewUrl  = route_to('admin.cms.blocks.preview');
$configJs    = json_encode($blockConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$entryOptionsUrlJs = json_encode((string) ($entryOptionsUrl ?? ''), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<meta name="block-preview-url" content="<?= esc($previewUrl) ?>">

<div class="mb-4">
    <a href="<?= route_to($ownerBlocksRoute, (string) $page['id']) ?>"
       class="text-sm text-brand-600 hover:text-brand-700">
        &larr; <?= esc(lang('Pages.block_back_to_blocks')) ?> — <?= esc($ownerLabel) ?>
    </a>
</div>

<?php ob_start(); ?>
<div class="space-y-5">

    <!-- Block type identity card (readonly) -->
    <div class="bg-brand-50 border border-brand-200 rounded-xl p-4 flex items-start gap-4">
        <div class="w-10 h-10 rounded-lg bg-brand-100 flex items-center justify-center text-brand-600 shrink-0">
            <i data-lucide="<?= esc($blockType['icon'] ?? 'layout') ?>" class="w-5 h-5"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-brand-900"><?= esc($blockType['name'] ?? 'Bloque') ?></p>
            <code class="text-xs font-mono text-brand-600 bg-brand-100 px-1.5 py-0.5 rounded"><?= esc($blockKey) ?></code>
            <?php if (! empty($blockType['description'])): ?>
                <p class="text-xs text-brand-700 mt-1"><?= esc($blockType['description']) ?></p>
            <?php endif; ?>
        </div>
        <button type="button"
                onclick="window.openBlockEditPreview && window.openBlockEditPreview(<?= esc(json_encode($blockKey), 'attr') ?>)"
                class="shrink-0 flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700 border border-brand-200 hover:border-brand-400 bg-white hover:bg-brand-50 px-3 py-1.5 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.963-7.178Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
            </svg>
            <?= esc(lang('Pages.block_preview_button')) ?>
        </button>
    </div>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <form method="post"
              id="block-edit-form"
              action="<?= route_to($ownerUpdateRoute, (string) $page['id'], (string) $block['id']) ?>"
              class="space-y-6">
            <?= csrf_field() ?>
            <input type="hidden" name="return_to" value="<?= esc($returnTo ?? '', 'attr') ?>">
            <input type="hidden" name="block_id" value="<?= esc((string) $blockIdValue) ?>">

            <!-- Hidden sort order and active checkbox -->
            <input type="hidden" name="sort_order" value="<?= esc((string) $sortOrderValue) ?>">
            <div class="flex items-center py-2">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1"
                           <?= $isActiveValue ? 'checked' : '' ?>
                           class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm font-medium text-gray-700"><?= esc(lang('Pages.block_active_label')) ?></span>
                </label>
            </div>

            <!-- Schema-driven config fields -->
            <?php if (! empty($configFields)): ?>
            <div class="border-t border-gray-100 pt-5"
                 x-data="blockInstanceConfig(<?= esc($entryOptionsUrlJs, 'attr') ?>, <?= esc($configJs, 'attr') ?>)">
                <h4 class="text-sm font-semibold text-gray-800 mb-1"><?= esc(lang('Pages.block_config_section')) ?></h4>
                <p class="text-xs text-gray-500 mb-4"><?= esc(lang('Pages.block_config_desc')) ?></p>
                <div class="space-y-4">
                    <?php foreach ($configFields as $cfKey => $cf):
                        $cfType    = $cf['type']     ?? 'string';
                        $cfLabel   = $cf['label']    ?? $cfKey;
                        $cfDefault = $cf['default']  ?? '';
                        $cfVal     = $submittedBlockConfig[$cfKey] ?? $cfDefault;
                        $cfOptions = isset($cf['options']) ? (array) $cf['options'] : [];
                        $cfReq     = ! empty($cf['required']);
                        $cfFieldName = "block_config[{$cfKey}]";
                        ?>
                    <div class="space-y-1">
                        <?php if ($cfType !== 'media_reference'): ?>
                            <label class="block text-xs font-semibold text-gray-700">
                                <?= esc($cfLabel) ?>
                                <?php if ($cfReq): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
                            </label>
                        <?php endif; ?>
                        <?php if ($cfType === 'boolean'): ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="<?= esc($cfFieldName, 'attr') ?>" value="1"
                                       <?= ! empty($cfVal) ? 'checked' : '' ?>
                                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm text-gray-600"><?= esc($cfLabel) ?></span>
                            </label>
                        <?php elseif ($cfType === 'select' && $cfKey === 'collection_id'): ?>
                            <select name="<?= esc($cfFieldName, 'attr') ?>"
                                    x-model="collectionId"
                                    @change="onCollectionChange($event.target.value)"
                                    class="<?= esc(input_class($cfFieldName)) ?>"
                                    <?= $cfReq ? 'required' : '' ?>>
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($cfOptions as $opt):
                                    $val = is_array($opt) ? $opt['value'] : $opt;
                                    $lbl = is_array($opt) ? $opt['label'] : $opt;
                                    ?>
                                    <option value="<?= esc((string) $val) ?>">
                                        <?= esc((string) $lbl) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($cfType === 'select' && $cfKey === 'entry_id'): ?>
                            <div class="space-y-1">
                                <select name="<?= esc($cfFieldName, 'attr') ?>"
                                        x-model="entryId"
                                        :disabled="!collectionId || entryOptionsLoading"
                                        class="<?= esc(input_class($cfFieldName)) ?> disabled:bg-gray-100"
                                        <?= $cfReq ? 'required' : '' ?>>
                                    <option value="" x-text="entryOptionsLoading ? 'Cargando entradas...' : '— Seleccionar —'"></option>
                                    <template x-for="opt in entryOptions" :key="opt.value">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </select>
                                <p x-show="!collectionId" class="text-[11px] text-gray-400">Selecciona primero una colección.</p>
                                <p x-show="entryOptionsError" class="text-[11px] text-red-500" x-text="entryOptionsError"></p>
                            </div>
                        <?php elseif ($cfType === 'select' && ! empty($cfOptions)): ?>
                            <?php
                            $flatOptionValues = array_map(function ($opt) {
                                return is_array($opt) ? (string) ($opt['value'] ?? '') : (string) $opt;
                            }, $cfOptions);
                            if ($cfVal !== '' && $cfVal !== null && is_scalar($cfVal) && ! in_array((string) $cfVal, $flatOptionValues, true)) {
                                $cfOptions[] = [
                                    'value' => $cfVal,
                                    'label' => $cfVal,
                                ];
                            }
                            ?>
                            <select name="<?= esc($cfFieldName, 'attr') ?>"
                                    class="<?= esc(input_class($cfFieldName)) ?>"
                                    <?= $cfReq ? 'required' : '' ?>>
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($cfOptions as $opt):
                                    $val = is_array($opt) ? $opt['value'] : $opt;
                                    $lbl = is_array($opt) ? $opt['label'] : $opt;
                                    ?>
                                    <option value="<?= esc((string) $val) ?>" <?= (string) $cfVal === (string) $val ? 'selected' : '' ?>>
                                        <?= esc((string) $lbl) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif ($cfType === 'color'): ?>
                            <?= view('components/form/color', [
                                'name'       => $cfFieldName,
                                'value'      => $cfVal,
                                'required'   => $cfReq,
                                'label'      => '',
                                'show_error' => false,
                            ]) ?>
                        <?php elseif ($cfType === 'media_reference'): ?>
                            <?= view('components/form/media_reference', [
                                'name'       => $cfFieldName,
                                'value'      => is_array($cfVal) ? $cfVal : [],
                                'label'      => $cfLabel,
                                'required'   => $cfReq,
                                'accept'     => (string) ($cf['accept'] ?? 'image'),
                                'help'       => (string) ($cf['help'] ?? ''),
                                'previewClass' => 'h-36 w-full rounded-xl border border-gray-200 object-cover',
                            ]) ?>
                        <?php else: ?>
                            <input type="<?= $cfType === 'url' ? 'url' : ($cfType === 'integer' ? 'number' : 'text') ?>"
                                   name="<?= esc($cfFieldName, 'attr') ?>"
                                   value="<?= esc((string) $cfVal) ?>"
                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                   <?= $cfReq ? 'required' : '' ?>>
                        <?php endif; ?>
                        <?= render_field_error($cfFieldName) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Content fields by language -->
            <?php if (! empty($fields)): ?>
            <?php $initialTabId = $focusLangId > 0 ? $focusLangId : $defaultLangId; ?>
            <div class="border-t border-gray-100 pt-5"
                 x-ref="langTabs"
                 x-data="langTabs(<?= $initialTabId ?>, '<?= esc(route_to('admin.cms.translate'), 'attr') ?>', '<?= esc($defaultLangCode, 'attr') ?>')">
                <h4 class="text-sm font-semibold text-gray-800 mb-1"><?= esc(lang('Pages.block_content_section')) ?></h4>
                <p class="text-xs text-gray-500 mb-4"><?= esc(lang('Pages.block_content_desc')) ?></p>

                <!-- Tab bar -->
                <div class="flex items-center justify-between border-b border-gray-200 mb-4">
                    <div class="flex" role="tablist">
                        <?php foreach ($languages as $lang):
                            $tabLangCode = strtolower((string) ($lang['code'] ?? ''));
                            $tabStatus = (string) ($blockTranslationStatus[$tabLangCode]['status'] ?? '');
                            ?>
                            <button type="button"
                                    role="tab"
                                    @click="setTab(<?= (int) $lang['id'] ?>)"
                                    :aria-selected="isActive(<?= (int) $lang['id'] ?>)"
                                    :class="isActive(<?= (int) $lang['id'] ?>)
                                        ? 'border-brand-600 text-brand-700 bg-brand-50/40'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                                <?php if ($tabStatus !== ''): ?>
                                    <span class="inline-block w-1.5 h-1.5 rounded-full mr-1 align-middle <?= TranslationStatus::badgeClasses($tabStatus, 'dot') ?>"
                                          title="<?= esc(lang('Translations.status_' . $tabStatus), 'attr') ?>"></span>
                                <?php endif; ?>
                                <?= esc(strtoupper($tabLangCode)) ?>
                                <?php if (! empty($lang['is_default'])): ?>
                                    <span class="ml-1 text-brand-400">★</span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Translate All button -->
                    <?php if (! empty($translateTargets)): ?>
                        <button type="button"
                                @click="autoTranslateAll(<?= esc(json_encode($translateTargets, JSON_THROW_ON_ERROR), 'attr') ?>)"
                                :disabled="translatingAll"
                                class="shrink-0 inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 shadow-sm hover:bg-green-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621c0-.012 0-.024 0-.036V3.75a2.25 2.25 0 0 1 2.25-2.25h15a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 20.25 21H3.75A2.25 2.25 0 0 1 1.5 18.75Zm12.621-4.72l-6.89 7.72m0 0l-6.89-7.72m6.89 7.72l6.89-7.72m-6.89 7.72l-6.89 7.72"/>
                            </svg>
                            <span x-text="translatingAll ? 'Traduciendo...' : 'Traducir automáticamente'"></span>
                        </button>
                    <?php endif; ?>
                </div>

                <p x-show="translateError !== ''"
                   x-text="translateError"
                   x-cloak
                   class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2"></p>

                <!-- Tab panels -->
                <?php foreach ($languages as $idx => $lang):
                    $langId = (int) $lang['id'];
                    $currentRow = [];
                    foreach ($block['translations'] ?? [] as $t) {
                        if (is_array($t) && (int) ($t['language_id'] ?? 0) === $langId) {
                            $currentRow = $t;
                            break;
                        }
                    }
                    $transRow = $translationsByLanguage[$langId] ?? $currentRow;
                    $isDefault = ! empty($lang['is_default']);
                    ?>
                <div x-show="isActive(<?= (int) $lang['id'] ?>)"
                     data-language-id="<?= (int) $lang['id'] ?>"
                     data-translation-index="<?= (int) $idx ?>"
                     class="space-y-4">
                    <input type="hidden" name="translations[<?= $idx ?>][language_id]" value="<?= esc((string) $lang['id']) ?>">
                    <input type="hidden" name="translations[<?= $idx ?>][is_published]" value="1">

                    <?php foreach ($fields as $fieldKey => $field):
                        $ft       = $field['type']  ?? 'string';
                        $flabel   = $field['label']  ?? $fieldKey;
                        $freq     = ! empty($field['required']) && $isDefault;
                        $fval     = $transRow['block_data'][$fieldKey] ?? '';
                        $foptions = isset($field['options']) ? (array) $field['options'] : [];
                        $fieldName = "translations[{$idx}][block_data][{$fieldKey}]";
                        ?>
                    <div class="space-y-1">
                        <?php if ($ft !== 'file' && $ft !== 'repeater' && $ft !== 'media_reference'): ?>
                        <label class="block text-xs font-semibold text-gray-700">
                            <?= esc($flabel) ?>
                            <?php if ($freq): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
                        </label>
                        <?php endif; ?>
                        <?php if ($ft === 'richtext'): ?>
                            <?php $initialValue = (string) old($fieldName, $fval);
                            $required = $freq;
                            $dynamicName = false; ?>
                            <?= view('partials/richtext_editor', [
                                'fieldName'    => $fieldName,
                                'initialValue' => $initialValue,
                                'required'     => $required,
                                'dynamicName'  => false,
                            ]) ?>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif (in_array($ft, ['text', 'textarea'])): ?>
                            <textarea name="<?= esc($fieldName, 'attr') ?>"
                                      rows="4"
                                      class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                      <?= $freq ? 'required' : '' ?>><?= esc((string) old($fieldName, $fval)) ?></textarea>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'url'): ?>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
                                    </svg>
                                </span>
                                <input type="text"
                                       name="<?= esc($fieldName, 'attr') ?>"
                                       value="<?= esc((string) old($fieldName, $fval)) ?>"
                                       placeholder="https:// o /ruta"
                                       inputmode="url"
                                       spellcheck="false"
                                       class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 pl-9 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                       <?= $freq ? 'required' : '' ?>>
                            </div>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'integer'): ?>
                            <input type="number"
                                   name="<?= esc($fieldName, 'attr') ?>"
                                   value="<?= esc((string) old($fieldName, $fval)) ?>"
                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                   <?= $freq ? 'required' : '' ?>>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'boolean'): ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox"
                                       name="<?= esc($fieldName, 'attr') ?>"
                                       value="1"
                                       <?= ! empty(old($fieldName, $fval)) ? 'checked' : '' ?>
                                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm text-gray-600"><?= esc($flabel) ?></span>
                            </label>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'select' && ! empty($foptions)): ?>
                            <select name="<?= esc($fieldName, 'attr') ?>"
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                    <?= $freq ? 'required' : '' ?>>
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($foptions as $opt):
                                    $val = is_array($opt) ? $opt['value'] : $opt;
                                    $lbl = is_array($opt) ? $opt['label'] : $opt;
                                    ?>
                                    <option value="<?= esc((string) $val) ?>" <?= (string) old($fieldName, $fval) === (string) $val ? 'selected' : '' ?>>
                                        <?= esc((string) $lbl) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'color'): ?>
                            <?= view('components/form/color', [
                                'name'       => $fieldName,
                                'value'      => $fval,
                                'required'   => $freq,
                                'label'      => '',
                                'show_error' => false,
                            ]) ?>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'media_reference'): ?>
                            <?php
                            $fieldValue = old($fieldName, $fval);
                            if (! is_array($fieldValue)) {
                                $fieldValue = is_array($fval) ? $fval : [];
                            }
                            ?>
                            <?= view('components/form/media_reference', [
                                'name'        => $fieldName,
                                'value'       => $fieldValue,
                                'label'       => $flabel,
                                'required'    => $freq,
                                'accept'      => (string) ($field['accept'] ?? 'image'),
                                'help'        => (string) ($field['help'] ?? ''),
                                'fieldKey'    => $fieldKey,
                                'copyEnabled' => count($languages) > 1,
                                'previewClass' => 'h-36 w-full rounded-xl border border-gray-200 object-cover',
                            ]) ?>
                            <?= render_field_error($fieldName) ?>
                        <?php elseif ($ft === 'file' && $isImageAccept((string) ($field['accept'] ?? 'image'))): ?>
                            <?php
                            $fieldValue = normalize_media_reference_value(old($fieldName, is_array($fval) ? $fval : []));
                            ?>
                            <?= view('components/form/media_reference', [
                                'name'        => $fieldName,
                                'value'       => $fieldValue,
                                'label'       => $flabel,
                                'required'    => $freq,
                                'accept'      => (string) ($field['accept'] ?? 'image'),
                                'help'        => (string) ($field['help'] ?? ''),
                                'fieldKey'    => $fieldKey,
                                'copyEnabled' => count($languages) > 1,
                                'previewClass' => 'h-36 w-full rounded-xl border border-gray-200 object-cover',
                            ]) ?>
                            <?= render_field_error($fieldName) ?>
                            <?= render_field_error($fieldName . '_source_kind') ?>
                            <?= render_field_error($fieldName . '_file_id') ?>
                            <?= render_field_error($fieldName . '_url') ?>
                        <?php elseif ($ft === 'file'): ?>
                            <?php
                            $fieldFileIdName = "translations[{$idx}][block_data][{$fieldKey}_file_id]";
                            $fieldUrlName = "translations[{$idx}][block_data][{$fieldKey}_url]";
                            $existingFileId  = old($fieldFileIdName, $transRow['block_data'][$fieldKey . '_file_id'] ?? '');
                            $existingFileUrl = old($fieldUrlName, $transRow['block_data'][$fieldKey . '_url'] ?? '');
                            $existingFileIdJs  = esc(json_encode((string) $existingFileId));
                            $existingFileUrlJs = esc(json_encode((string) $existingFileUrl));
                            $faccept = (string) ($field['accept'] ?? 'image');
                            $facceptJs = esc(json_encode($faccept));
                            ?>
                            <label class="block text-xs font-semibold text-gray-700">
                                <?= esc($flabel) ?>
                                <?php if ($freq): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
                            </label>
                            <div x-data="translatableFileField(<?= $existingFileIdJs ?>, <?= $existingFileUrlJs ?>, <?= $facceptJs ?>)" class="space-y-2">
                                <input type="hidden"
                                       id="file_id_lang_<?= $idx ?>_<?= esc($fieldKey) ?>"
                                       name="<?= $fieldFileIdName ?>"
                                       x-model="fileId">
                                <input type="hidden"
                                       id="file_url_lang_<?= $idx ?>_<?= esc($fieldKey) ?>"
                                       name="<?= $fieldUrlName ?>"
                                       x-model="fileUrl">
                                <div x-show="previewUrl">
                                    <template x-if="accept === 'video'">
                                        <video :src="previewUrl" class="h-24 w-auto rounded border border-gray-200" controls muted></video>
                                    </template>
                                    <template x-if="accept !== 'video'">
                                        <img :src="previewUrl" class="h-24 w-auto rounded border border-gray-200 object-cover">
                                    </template>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button"
                                            @click="openPicker()"
                                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                        </svg>
                                        <span x-text="fileId ? pickerLabels[accept]?.change : pickerLabels[accept]?.select"></span>
                                    </button>
                                    <button type="button"
                                            @click="clearFile()"
                                            x-show="fileId"
                                            class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
                                        </svg>
                                        <span>Quitar</span>
                                    </button>
                                    <button type="button"
                                            @click="window.copyLangTabsFileFieldToAll('#file_id_lang_<?= $idx ?>_<?= esc($fieldKey) ?>', '#file_url_lang_<?= $idx ?>_<?= esc($fieldKey) ?>', '<?= esc($fieldKey) ?>')"
                                            x-show="fileId"
                                            class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-100 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 19H9m4 0h4m-11-8h.01M9 3h6m4 0a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 0a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-6 0h4"/>
                                        </svg>
                                        <span>Copiar a otros idiomas</span>
                                    </button>
                                </div>
                            </div>
                            <?= render_field_error($fieldName) ?>
                            <?= render_field_error($fieldName . '_file_id') ?>
                            <?= render_field_error($fieldName . '_url') ?>
                        <?php elseif ($ft === 'repeater'): ?>
                            <?php
                            $existingItems   = old($fieldName, $fval);
                            $existingItems   = is_array($existingItems) ? $existingItems : [];
                            $itemFields      = $field['item_fields'] ?? [];
                            $existingItemsJs = esc(json_encode(array_values($existingItems), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                            $itemFieldsJs    = esc(json_encode($itemFields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                            $fieldKeyJs      = esc(json_encode($fieldKey));
                            $langIdxJs       = esc(json_encode($idx));
                            ?>
                            <label class="block text-xs font-semibold text-gray-700 mb-2">
                                <?= esc($flabel) ?>
                            </label>
                            <div x-data="blockRepeaterField(<?= $existingItemsJs ?>, <?= $itemFieldsJs ?>, <?= $fieldKeyJs ?>, <?= $langIdxJs ?>)"
                                 class="space-y-3">
                                <template x-for="(item, itemIdx) in items" :key="itemIdx">
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-semibold text-gray-600" x-text="`Item ${itemIdx + 1}`"></span>
                                            <button type="button" @click="removeItem(itemIdx)"
                                                    class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                                        </div>
                                        <template x-for="(subField, subKey) in itemFields" :key="subKey">
                                            <div class="space-y-1">
                                                <template x-if="subField.type !== 'media_reference'">
                                                    <label class="block text-xs font-medium text-gray-600" x-text="subField.label || subKey"></label>
                                                </template>
                                                <template x-if="subField.type === 'media_reference'">
                                                    <div x-data="{ outerFieldKey: fieldKey }">
                                                    <div x-data="mediaReferenceField(item[subKey] || {}, subField.accept || 'image', `${outerFieldKey}][${itemIdx}][${subKey}`)" class="space-y-4 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                                        <label class="block text-xs font-medium text-gray-600" x-text="subField.label || subKey"></label>
                                                        <input type="hidden"
                                                               :name="`translations[${langIdx}][block_data][${outerFieldKey}][${itemIdx}][${subKey}][source_kind]`"
                                                               x-model="sourceKind">
                                                        <input type="hidden"
                                                               :name="`translations[${langIdx}][block_data][${outerFieldKey}][${itemIdx}][${subKey}][file_id]`"
                                                               x-model="fileId">
                                                        <input :type="isExternalSource() ? 'url' : 'hidden'"
                                                               :name="`translations[${langIdx}][block_data][${outerFieldKey}][${itemIdx}][${subKey}][url]`"
                                                               x-model="url"
                                                               @input="syncExternalUrl()"
                                                               placeholder="https://..."
                                                               inputmode="url"
                                                               spellcheck="false"
                                                               class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                        <div x-show="previewUrl" x-cloak>
                                                            <template x-if="accept === 'video'">
                                                                <video :src="previewUrl" class="h-20 w-auto rounded border border-gray-200 object-cover" controls muted></video>
                                                            </template>
                                                            <template x-if="accept === 'image' || accept === 'any'">
                                                                <img :src="previewUrl" class="h-20 w-auto rounded border border-gray-200 object-cover">
                                                            </template>
                                                            <template x-if="accept === 'document' || accept === 'audio'">
                                                                <a :href="previewUrl" target="_blank" rel="noopener" class="flex items-center gap-2 rounded border border-gray-200 bg-gray-50 px-2 py-1.5 text-xs text-gray-600 hover:bg-gray-100">
                                                                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                                    </svg>
                                                                    <span class="truncate" x-text="previewUrl"></span>
                                                                </a>
                                                            </template>
                                                        </div>
                                                        <div class="flex flex-wrap gap-2">
                                                            <button type="button"
                                                                    @click="openPicker()"
                                                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                                </svg>
                                                                <span x-text="pickerButtonLabel()"></span>
                                                            </button>
                                                            <button type="button"
                                                                    @click="clearReference()"
                                                                    x-show="fileId || url"
                                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 shadow-sm transition-colors hover:bg-red-100">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
                                                                </svg>
                                                                <span>Quitar</span>
                                                            </button>
                                                            <button type="button"
                                                                    @click="copyToAllLanguages()"
                                                                    x-show="fileId || url"
                                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-brand-50 px-3 py-2 text-sm font-medium text-brand-700 shadow-sm transition-colors hover:bg-brand-100">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19H9m4 0h4m-11-8h.01M9 3h6m4 0a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 0a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-6 0h4"/>
                                                                </svg>
                                                                <span>Copiar a otros idiomas</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    </div>
                                                </template>
                                                <template x-if="subField.type === 'file'">
                                                    <div class="space-y-1.5">
                                                        <template x-if="String(subField.accept || 'image').toLowerCase() === 'image' || String(subField.accept || 'image').toLowerCase() === 'image/*' || String(subField.accept || 'image').toLowerCase().startsWith('image/')">
                                                            <div x-data="mediaReferenceField(item[subKey] || {}, subField.accept || 'image')" class="space-y-4 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}][source_kind]`"
                                                                       x-model="sourceKind">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}][file_id]`"
                                                                       x-model="fileId">
                                                                <input :type="isExternalSource() ? 'url' : 'hidden'"
                                                                       :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}][url]`"
                                                                       x-model="url"
                                                                       @input="syncExternalUrl()"
                                                                       placeholder="https://..."
                                                                       inputmode="url"
                                                                       spellcheck="false"
                                                                       class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                                <div x-show="previewUrl" x-cloak>
                                                                    <template x-if="accept === 'video'">
                                                                        <video :src="previewUrl" class="h-20 w-auto rounded border border-gray-200 object-cover" controls muted></video>
                                                                    </template>
                                                                    <template x-if="accept !== 'video'">
                                                                        <img :src="previewUrl" class="h-20 w-auto rounded border border-gray-200 object-cover">
                                                                    </template>
                                                                </div>
                                                                <div class="flex flex-wrap gap-2">
                                                                <button type="button"
                                                                        @click="openPicker()"
                                                                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                                        </svg>
                                                                        <span x-text="pickerButtonLabel()"></span>
                                                                    </button>
                                                                <button type="button"
                                                                        @click="clearReference()"
                                                                        x-show="fileId || url"
                                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 shadow-sm transition-colors hover:bg-red-100">
                                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
                                                                        </svg>
                                                                        <span>Quitar</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template x-if="!(String(subField.accept || 'image').toLowerCase() === 'image' || String(subField.accept || 'image').toLowerCase() === 'image/*' || String(subField.accept || 'image').toLowerCase().startsWith('image/'))">
                                                            <div class="space-y-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}_file_id]`"
                                                                       :value="item[subKey + '_file_id'] || ''">
                                                                <input type="hidden"
                                                                       :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}_url]`"
                                                                       :value="item[subKey + '_url'] || ''">
                                                                <div x-show="item[subKey + '_url'] || item[subKey + '_preview_url']">
                                                                    <template x-if="(subField.accept || 'image') === 'video'">
                                                                        <video :src="item[subKey + '_preview_url'] || item[subKey + '_url']"
                                                                               class="h-20 w-auto rounded border border-gray-200" controls muted></video>
                                                                    </template>
                                                                    <template x-if="(subField.accept || 'image') !== 'video'">
                                                                        <img :src="item[subKey + '_preview_url'] || item[subKey + '_url']"
                                                                             class="h-20 w-auto rounded border border-gray-200 object-cover">
                                                                    </template>
                                                                </div>
                                                                <button type="button"
                                                                        @click="openPickerForItem(itemIdx, subKey, subField.accept || 'image')"
                                                                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                                                    </svg>
                                                                    <span x-text="item[subKey + '_file_id'] ? 'Cambiar' : 'Seleccionar'"></span>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="subField.type === 'url'">
                                                    <input type="text"
                                                           :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                           x-model="item[subKey]"
                                                           placeholder="https:// o /ruta"
                                                           inputmode="url"
                                                           spellcheck="false"
                                                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                </template>
                                                <template x-if="subField.type === 'text' || subField.type === 'textarea'">
                                                    <textarea :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                              x-model="item[subKey]"
                                                              rows="3"
                                                              class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"></textarea>
                                                </template>
                                                <template x-if="!['file','media_reference','url','text','textarea'].includes(subField.type)">
                                                    <input type="text"
                                                           :name="`translations[${langIdx}][block_data][${fieldKey}][${itemIdx}][${subKey}]`"
                                                           x-model="item[subKey]"
                                                           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <button type="button"
                                        @click="addItem"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-dashed border-brand-300 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-brand-100">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                    </svg>
                                    Agregar item
                                </button>
                            </div>
                        <?php else: ?>
                            <input type="text"
                                   name="<?= esc($fieldName, 'attr') ?>"
                                   value="<?= esc((string) old($fieldName, $fval)) ?>"
                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                                   <?= $freq ? 'required' : '' ?>>
                            <?= render_field_error($fieldName) ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('Pages.block_update_button')) ?></button>
                <a href="<?= route_to($ownerBlocksRoute, (string) $page['id']) ?>" class="<?= esc(action_button_class()) ?>"><?= esc(lang('App.cancel')) ?></a>
            </div>
        </form>
    </section>
</div>
<script>
window.openBlockEditPreview = window.openBlockEditPreview || function openBlockEditPreview(blockKey) {
    const form = document.getElementById('block-edit-form');
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const langTabs = form.querySelector('[x-ref="langTabs"]')?._x_dataStack?.[0] || null;
    const activeLanguageId = Number(langTabs?.active || 0);
    const activePanel = activeLanguageId > 0
        ? form.querySelector(`[data-language-id="${activeLanguageId}"]`)
        : form.querySelector('[data-language-id]');

    const translatedData = typeof window.formValuesToObject === 'function'
        ? window.formValuesToObject(form)
        : {};
    const blockConfig = translatedData.block_config || {};
    const translationIndex = Number(activePanel?.dataset?.translationIndex || 0);
    const blockData = translatedData.translations?.[String(translationIndex)]?.block_data || {};

    window.dispatchEvent(new CustomEvent('block-preview-open', {
        detail: {
            blockKey,
            blockConfig,
            blockData,
            previewMode: 'live',
        },
    }));
};
</script>
<?php $blockEditContent = ob_get_clean(); ?>

<?= view('components/display/form_section', [
    'title' => 'Pages.block_editor_title',
    'description' => 'Pages.block_editor_desc',
    'content' => $blockEditContent,
    'bodyClass' => 'space-y-5',
]) ?>
