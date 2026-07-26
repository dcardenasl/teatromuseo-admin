<?php
/**
 * @var string $name
 * @var string $label
 * @var mixed|null $value
 * @var bool|null $required
 * @var bool|null $readonly
 * @var string|null $placeholder
 * @var string|null $help
 * @var string|null $autocomplete
 * @var int|null $maxlength
 * @var array<string, scalar|null>|null $attributes
 */

helper('form');

$required     = $required ?? false;
$readonly     = $readonly ?? false;
$value        = old($name, $value ?? '');
$placeholder  = $placeholder ?? '';
$help         = $help ?? '';
$autocomplete = $autocomplete ?? 'off';
$maxlength    = isset($maxlength) ? (int) $maxlength : null;
$attributes   = is_array($attributes ?? null) ? $attributes : [];
?>
<div<?= $maxlength !== null ? ' x-data="{ _len: ' . strlen((string) $value) . ' }"' : '' ?>>
    <div class="flex items-center justify-between">
        <label class="block text-sm font-medium text-gray-700" for="<?= esc($name, 'attr') ?>">
            <?= lang($label) ?>
            <?php if ($required): ?>
                <span class="text-red-500" aria-hidden="true">*</span>
            <?php endif; ?>
        </label>
        <?php if ($maxlength !== null): ?>
            <span class="text-xs text-gray-400" x-text="''+_len+'/<?= $maxlength ?>'"><?= strlen((string) $value) ?>/<?= $maxlength ?></span>
        <?php endif; ?>
    </div>
    <input
        id="<?= esc($name, 'attr') ?>"
        name="<?= esc($name, 'attr') ?>"
        type="text"
        value="<?= esc($value) ?>"
        class="<?= input_class($name) ?>"
        placeholder="<?= esc(lang($placeholder), 'attr') ?>"
        autocomplete="<?= esc($autocomplete, 'attr') ?>"
        <?= $maxlength !== null ? 'maxlength="' . $maxlength . '" @input="_len = $event.target.value.length"' : '' ?>
        <?= $required ? 'required' : '' ?>
        <?= $readonly ? 'readonly aria-readonly="true"' : '' ?>
        <?= field_aria_attrs($name, $required) ?>
        <?= render_extra_attrs($attributes) ?>
    >
    <?php if ($help): ?>
        <p class="mt-1 text-xs text-gray-500"><?= lang($help) ?></p>
    <?php endif; ?>
    <?= render_field_error($name) ?>
</div>
