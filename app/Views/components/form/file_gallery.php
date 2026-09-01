<?php
/**
 * @var string $name
 * @var string $label
 * @var mixed|null $value
 * @var string|null $accept
 * @var string|null $filterType
 * @var string|null $help
 */

helper('form');

$value = old($name, $value ?? '');
if (is_array($value)) {
    $value = implode(',', $value);
}
$value = (string) $value;
$help = $help ?? '';
$accept = $accept ?? '';
$filterType = $filterType ?? '';
$errorClass = field_error_class($name, 'border-red-500 bg-red-50/40');
?>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">
        <?= lang($label) ?>
    </label>

    <div x-data="fileGalleryField({
            name: '<?= esc($name, 'js') ?>',
            value: '<?= esc($value, 'js') ?>',
            accept: '<?= esc($accept, 'js') ?>',
            filterType: '<?= esc($filterType, 'js') ?>'
        })"
         class="<?= esc($errorClass, 'attr') ?>">

        <input type="hidden" :name="fieldName" :value="csvValue">

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4" x-show="ids.length > 0" x-cloak>
            <template x-for="id in ids" :key="id">
                <div class="relative flex h-24 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                    <template x-if="fileInfo(id).is_image && (fileInfo(id).previewUrl || fileInfo(id).url)">
                        <img :src="fileInfo(id).previewUrl || fileInfo(id).url" :alt="fileInfo(id).original_name" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!fileInfo(id).is_image || fileInfo(id).url === ''">
                        <?= ui_icon('file', 'h-8 w-8 text-gray-400') ?>
                    </template>
                    <button type="button" @click="removeFile(id)"
                            class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-full bg-white/90 text-gray-500 shadow hover:bg-red-50 hover:text-red-600">
                        <?= ui_icon('x', 'h-3.5 w-3.5') ?>
                    </button>
                </div>
            </template>
        </div>

        <button type="button" @click="openPicker()"
                class="mt-3 flex w-full cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center transition-colors hover:border-brand-400 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500"
                <?= field_aria_attrs($name) ?>>
            <div class="flex flex-col items-center gap-1">
                <?= ui_icon('upload', 'h-6 w-6 text-gray-400') ?>
                <p class="text-sm text-gray-500"><?= esc(lang('Files.picker_select_file')) ?></p>
            </div>
        </button>

        <div x-show="loading" x-cloak class="mt-1 flex items-center gap-1 text-xs text-gray-400">
            <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <?= esc(lang('App.loading')) ?>
        </div>
    </div>

    <?php if ($help): ?>
        <p class="mt-1 text-xs text-gray-500"><?= lang($help) ?></p>
    <?php endif; ?>
    <?= render_field_error($name) ?>
</div>
