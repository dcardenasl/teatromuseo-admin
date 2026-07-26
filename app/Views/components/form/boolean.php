<?php
/**
 * @var string $name
 * @var string $label
 * @var mixed|null $value
 * @var bool|null $required
 * @var string|null $help
 * @var string|null $on_label
 * @var string|null $off_label
 * @var array<string, scalar|null>|null $attributes
 */

declare(strict_types=1);

helper('form');

$required = $required ?? false;
$value = old($name, $value ?? false);
$help = $help ?? '';
$on_label = $on_label ?? 'App.yes';
$off_label = $off_label ?? 'App.no';
$attributes = is_array($attributes ?? null) ? $attributes : [];
$checked = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

if ($checked === null) {
    $checked = (bool) $value;
}

$onLabelText = (string) lang($on_label);
$offLabelText = (string) lang($off_label);
?>
<div>
    <span class="block text-sm font-medium text-gray-700">
        <?= lang($label) ?>
        <?php if ($required): ?>
            <span class="text-red-500" aria-hidden="true">*</span>
        <?php endif; ?>
    </span>

    <input type="hidden" name="<?= esc($name, 'attr') ?>" value="0">

    <label
        class="mt-2 inline-flex cursor-pointer items-center gap-3"
        x-data="{ checked: <?= $checked ? 'true' : 'false' ?> }"
    >
        <input
            id="<?= esc($name, 'attr') ?>"
            name="<?= esc($name, 'attr') ?>"
            type="checkbox"
            value="1"
            class="peer sr-only"
            <?= $checked ? 'checked' : '' ?>
            x-model="checked"
            :aria-checked="checked ? 'true' : 'false'"
            <?= field_aria_attrs($name, $required) ?>
            <?= render_extra_attrs($attributes) ?>
        >
        <span
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full bg-gray-200 transition-colors duration-200 ease-in-out peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500 peer-focus-visible:ring-offset-2"
            :style="checked ? 'width: 2.75rem; height: 1.5rem; background-color: var(--color-brand-600)' : 'width: 2.75rem; height: 1.5rem'"
            aria-hidden="true"
        >
            <span
                class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform duration-200 ease-in-out"
                :style="checked ? 'transform: translateX(1.25rem)' : 'transform: translateX(0.125rem)'"
            ></span>
        </span>
        <span
            class="text-sm font-medium text-gray-700"
            x-text="checked ? '<?= esc($onLabelText, 'js') ?>' : '<?= esc($offLabelText, 'js') ?>'"
        >
            <?= esc($checked ? $onLabelText : $offLabelText) ?>
        </span>
    </label>

    <?php if ($help): ?>
        <p class="mt-1 text-xs text-gray-500"><?= lang($help) ?></p>
    <?php endif; ?>
    <?= render_field_error($name) ?>
</div>
