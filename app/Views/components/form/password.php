<?php
/**
 * Reusable password input with an accessible visibility toggle.
 *
 * Required:
 * - $name
 *
 * Optional:
 * - $label (language key)
 * - $required
 * - $autocomplete
 * - $placeholder (language key)
 * - $help (language key)
 * - $class
 * - $inputClass
 * - $attributes (additional HTML attributes, e.g. x-model)
 * - $id
 * - $showLabel / $hideLabel (translated action labels)
 */

helper('form');

$name         ??= '';
$label        ??= null;
$required     ??= false;
$autocomplete ??= 'current-password';
$placeholder  ??= '';
$help         ??= '';
$class        ??= '';
$inputClass   ??= '';
$attributes   = is_array($attributes ?? null) ? $attributes : [];
$id           ??= $name;
$showLabel    ??= lang('App.show_password');
$hideLabel    ??= lang('App.hide_password');

$inputId = trim((string) $id) !== '' ? (string) $id : 'password';
$inputClasses = trim(input_class((string) $name) . ' pr-10 ' . (string) $inputClass);
$showLabelExpression = esc(json_encode((string) $showLabel, JSON_THROW_ON_ERROR), 'attr');
$hideLabelExpression = esc(json_encode((string) $hideLabel, JSON_THROW_ON_ERROR), 'attr');
?>
<div x-data="passwordToggle()" class="<?= esc(trim((string) $class), 'attr') ?>">
    <?php if ($label !== null): ?>
        <label class="block text-sm font-medium text-gray-700" for="<?= esc($inputId, 'attr') ?>">
            <?= lang((string) $label) ?>
        </label>
    <?php endif; ?>

    <div class="relative mt-1">
        <input
            id="<?= esc($inputId, 'attr') ?>"
            name="<?= esc((string) $name, 'attr') ?>"
            type="password"
            :type="visible ? 'text' : 'password'"
            autocomplete="<?= esc((string) $autocomplete, 'attr') ?>"
            placeholder="<?= esc($placeholder !== '' ? lang((string) $placeholder) : '', 'attr') ?>"
            class="<?= esc($inputClasses, 'attr') ?>"
            <?= $required ? 'required' : '' ?>
            <?= field_aria_attrs((string) $name, (bool) $required) ?>
            <?= render_extra_attrs($attributes) ?>
        >
        <button
            type="button"
            aria-label="<?= esc((string) $showLabel, 'attr') ?>"
            aria-pressed="false"
            aria-controls="<?= esc($inputId, 'attr') ?>"
            :aria-label="visible ? <?= $hideLabelExpression ?> : <?= $showLabelExpression ?>"
            :aria-pressed="visible ? 'true' : 'false'"
            @click="toggle()"
            class="absolute inset-y-0 right-0 inline-flex w-10 items-center justify-center text-gray-500 transition hover:text-gray-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500"
        >
            <span x-show="!visible" aria-hidden="true">
                <?= ui_icon('eye', 'h-5 w-5') ?>
            </span>
            <span x-show="visible" x-cloak aria-hidden="true">
                <?= ui_icon('eye-off', 'h-5 w-5') ?>
            </span>
        </button>
    </div>

    <?php if ($help !== ''): ?>
        <p class="mt-1 text-xs text-gray-500"><?= lang((string) $help) ?></p>
    <?php endif; ?>
    <?= render_field_error((string) $name) ?>
</div>
