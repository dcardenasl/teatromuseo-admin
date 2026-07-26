<?php
/**
 * Reusable translated image field.
 *
 * Variables:
 *   $label                     — translation key for the field label
 *   $help                      — translation key for the help text
 *   $fileIdName                — hidden input name for file ID
 *   $fileUrlName               — hidden input name for file URL
 *   $fileIdInputId             — hidden input id for file ID
 *   $fileUrlInputId            — hidden input id for file URL
 *   $fileIdValue               — current file ID
 *   $fileUrlValue              — current file URL
 *   $copyTargetFileIdSelectors — array of selectors for target file ID inputs
 *   $copyTargetFileUrlSelectors — array of selectors for target file URL inputs
 *   $accept                    — picker accept hint (default: image)
 *   $previewClass              — preview image classes (default: h-32 w-full rounded-lg border border-gray-200 object-cover)
 *   $copyLabel                 — translation key for copy button label
 */

helper('form');

$fileIdName = (string) ($fileIdName ?? '');
$fileUrlName = (string) ($fileUrlName ?? '');
$fileIdInputId = (string) ($fileIdInputId ?? '');
$fileUrlInputId = (string) ($fileUrlInputId ?? '');
$fileIdValue = (string) ($fileIdValue ?? '');
$fileUrlValue = (string) ($fileUrlValue ?? '');
$accept = (string) ($accept ?? 'image');
$help = (string) ($help ?? '');
$copyLabel = (string) ($copyLabel ?? 'Entries.translation_copy_to_other_languages');
$previewClass = (string) ($previewClass ?? 'h-32 w-full rounded-lg border border-gray-200 object-cover');
$copyTargetFileIdSelectors = is_array($copyTargetFileIdSelectors ?? null) ? $copyTargetFileIdSelectors : [];
$copyTargetFileUrlSelectors = is_array($copyTargetFileUrlSelectors ?? null) ? $copyTargetFileUrlSelectors : [];

?>

<div class="space-y-2 rounded-lg border border-gray-200 bg-white p-4"
     x-data="translatableFileField(<?= esc(json_encode($fileIdValue), 'attr') ?>, <?= esc(json_encode($fileUrlValue), 'attr') ?>, <?= esc(json_encode($accept), 'attr') ?>)">
    <input type="hidden"
           id="<?= esc($fileIdInputId, 'attr') ?>"
           name="<?= esc($fileIdName, 'attr') ?>"
           x-model="fileId">
    <input type="hidden"
           id="<?= esc($fileUrlInputId, 'attr') ?>"
           name="<?= esc($fileUrlName, 'attr') ?>"
           x-model="fileUrl">

    <div x-show="previewUrl">
        <img :src="previewUrl" class="<?= esc($previewClass, 'attr') ?>">
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="button"
                @click="openPicker()"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
            <?= ui_icon('folder-open', 'h-4 w-4') ?>
            <span x-text="fileId ? pickerLabels[accept]?.change : pickerLabels[accept]?.select"></span>
        </button>
        <button type="button"
                @click="clearFile()"
                x-show="fileId"
                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100 transition-colors">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
            </svg>
            <span><?= esc(lang('App.remove')) ?></span>
        </button>
        <?php if ($copyTargetFileIdSelectors !== []): ?>
            <button type="button"
                    @click="window.copyLangTabsFileFieldToTargets('<?= esc($fileIdInputId, 'js') ?>', '<?= esc($fileUrlInputId, 'js') ?>', <?= esc(json_encode($copyTargetFileIdSelectors), 'attr') ?>, <?= esc(json_encode($copyTargetFileUrlSelectors), 'attr') ?>)"
                    x-show="fileId"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 shadow-sm hover:bg-blue-100 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19H9m4 0h4m-11-8h.01M9 3h6m4 0a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 0a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-6 0h4"/>
                </svg>
                <span><?= esc(lang($copyLabel)) ?></span>
            </button>
        <?php endif; ?>
    </div>
    <?php if ($help !== ''): ?>
        <p class="mt-1 text-xs text-gray-500"><?= esc(lang($help)) ?></p>
    <?php endif; ?>
</div>
