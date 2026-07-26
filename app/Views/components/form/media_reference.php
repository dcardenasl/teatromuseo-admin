<?php
/**
 * Reusable media reference field.
 *
 * Variables:
 *   $name         - base input name, e.g. block_config[image]
 *   $value        - array with source_kind, file_id, url
 *   $label        - visible label
 *   $help         - optional help text
 *   $required     - whether the field is required
 *   $accept       - picker accept hint (default: image)
 *   $fieldKey     - optional block_data key used to copy the value to other languages
 *   $copyEnabled  - whether the copy-to-all-languages action should be shown
 *   $previewClass - CSS classes for the preview element
 */

helper('form');

$name = (string) ($name ?? '');
$label = (string) ($label ?? '');
$help = (string) ($help ?? '');
$required = (bool) ($required ?? false);
$accept = (string) ($accept ?? 'image');
$fieldKey = trim((string) ($fieldKey ?? ''));
$copyEnabled = (bool) ($copyEnabled ?? false);
$previewClass = (string) ($previewClass ?? 'h-36 w-full rounded-xl border border-gray-200 object-cover');
$payload = normalize_media_reference_value(is_array($value ?? null) ? $value : []);
?>

<div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"
     x-data="mediaReferenceField(<?= esc(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'attr') ?>, <?= esc(json_encode($accept), 'attr') ?>, <?= esc(json_encode($fieldKey), 'attr') ?>)">
    <div class="flex items-start gap-3">
        <div class="min-w-0">
            <label class="block text-xs font-semibold text-gray-700">
                <?= esc($label) ?>
                <?php if ($required): ?><span class="ml-0.5 text-red-500">*</span><?php endif; ?>
            </label>
            <p class="mt-1 text-[11px] text-gray-500">Elige un archivo desde la biblioteca o pega una URL externa pública.</p>
        </div>
    </div>

    <div class="grid gap-2 sm:grid-cols-2" role="group" aria-label="<?= esc($label, 'attr') ?>">
        <button type="button"
                @click="setSourceKind('hub_file')"
                :aria-pressed="isFileSource()"
                class="group flex w-full items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 text-left transition"
                :class="sourceKindButtonClass('hub_file')">
            <?= ui_icon('folder-open', 'h-4 w-4 shrink-0 text-gray-500 transition group-hover:text-brand-600') ?>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold">Biblioteca</span>
                    <span class="h-2 w-2 rounded-full" :class="sourceKindDotClass('hub_file')"></span>
                </div>
                <p class="mt-0.5 text-xs text-gray-500">Selecciona un archivo existente o sube uno nuevo.</p>
            </div>
            <span x-show="isFileSource()" class="ml-auto inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white shadow-sm">
                <?= ui_icon('check', 'h-3.5 w-3.5') ?>
            </span>
        </button>
        <button type="button"
                @click="setSourceKind('external_url')"
                :aria-pressed="isExternalSource()"
                class="group flex w-full items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 text-left transition"
                :class="sourceKindButtonClass('external_url')">
            <?= ui_icon('external-link', 'h-4 w-4 shrink-0 text-gray-500 transition group-hover:text-brand-600') ?>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold">URL externa</span>
                    <span class="h-2 w-2 rounded-full" :class="sourceKindDotClass('external_url')"></span>
                </div>
                <p class="mt-0.5 text-xs text-gray-500">Pega un enlace público directo a la imagen.</p>
            </div>
            <span x-show="isExternalSource()" class="ml-auto inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white shadow-sm">
                <?= ui_icon('check', 'h-3.5 w-3.5') ?>
            </span>
        </button>
    </div>

    <input type="hidden" name="<?= esc($name . '[source_kind]', 'attr') ?>" x-model="sourceKind">
    <input type="hidden" name="<?= esc($name . '[file_id]', 'attr') ?>" x-model="fileId">
    <input type="url"
           name="<?= esc($name . '[url]', 'attr') ?>"
           x-model="url"
           x-show="isExternalSource()"
           x-cloak
           :disabled="!isExternalSource()"
           @input="syncExternalUrl()"
           placeholder="https://..."
           inputmode="url"
           spellcheck="false"
           class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500">

    <div x-show="previewUrl" x-cloak class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between gap-2 border-b border-gray-200 px-3 py-2">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Vista previa</span>
            <span class="text-[11px] text-gray-400" x-text="sourceKindLabel()"></span>
        </div>
        <template x-if="accept === 'video'">
            <video :src="previewUrl" class="<?= esc($previewClass, 'attr') ?> rounded-none border-0" controls muted></video>
        </template>
        <template x-if="accept === 'image' || accept === 'any'">
            <img :src="previewUrl" class="<?= esc($previewClass, 'attr') ?> rounded-none border-0">
        </template>
        <template x-if="accept === 'document' || accept === 'audio'">
            <a :href="previewUrl" target="_blank" rel="noopener" class="flex items-center gap-2 px-3 py-3 text-xs text-gray-600 hover:bg-gray-100">
                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                <span class="truncate" x-text="previewUrl"></span>
            </a>
        </template>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button type="button"
                @click="openPicker()"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
            <?= ui_icon('folder-open', 'h-4 w-4') ?>
            <span class="flex flex-col items-start leading-tight">
                <span><?= esc(lang('Files.picker_change')) ?></span>
                <span class="text-[11px] font-normal text-gray-500" x-text="pickerButtonLabel()"></span>
            </span>
        </button>
        <button type="button"
                @click="clearReference()"
                x-show="fileId || url"
                class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm transition-colors hover:bg-red-100">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h12m-10.5 0V6a1.5 1.5 0 0 1 1.5-1.5h6A1.5 1.5 0 0 1 16.5 6v1.5m-9 0 .75 10.5A1.5 1.5 0 0 0 9.75 19.5h4.5a1.5 1.5 0 0 0 1.5-1.5L16.5 7.5m-7.5 3v4.5m3-4.5v4.5"/>
            </svg>
            <span><?= esc(lang('App.remove')) ?></span>
        </button>
        <?php if ($copyEnabled && $fieldKey !== ''): ?>
            <button type="button"
                    @click="copyToAllLanguages()"
                    x-show="fileId || url"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-brand-50 px-3 py-1.5 text-xs font-medium text-brand-700 shadow-sm transition-colors hover:bg-brand-100">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19H9m4 0h4m-11-8h.01M9 3h6m4 0a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m6 0a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-6 0h4"/>
                </svg>
                <span>Copiar a otros idiomas</span>
            </button>
        <?php endif; ?>
    </div>

    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/70 px-3 py-2">
        <p class="text-[11px] text-gray-500" x-text="sourceKindHint()"></p>
        <?php if ($help !== ''): ?>
            <p class="mt-1 text-xs text-gray-500"><?= esc($help) ?></p>
        <?php endif; ?>
    </div>

</div>
