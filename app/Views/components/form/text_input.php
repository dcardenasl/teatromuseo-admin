<?php
/**
 * Reusable admin text input component.
 *
 * Required:
 * - $name
 *
 * Optional:
 * - $value
 * - $label
 * - $placeholder
 * - $type
 * - $required
 * - $class
 * - $inputClass
 * - $attrs
 * - $error
 * - $maxlength
 * - $alpineErrorField
 */
helper('form');

$name        ??= '';
$value       ??= '';
$label       ??= null;
$placeholder ??= '';
$type        ??= 'text';
$required    ??= false;
$class       ??= '';
$inputClass  ??= '';
$attrs       ??= '';
$error       ??= null;
$maxlength   ??= null;
$maxlength   = is_numeric($maxlength) && (int) $maxlength > 0 ? (int) $maxlength : null;
$alpineErrorField ??= null;
$error       = $error ?? get_field_error((string) $name);
$alpineErrorExpression = $alpineErrorField !== null && $alpineErrorField !== ''
    ? 'fieldHasError(' . json_encode((string) $alpineErrorField, JSON_THROW_ON_ERROR) . ')'
    : null;

$baseClass = trim('mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 ' . $inputClass . ' ' . field_error_class((string) $name));
$type = $type ?: 'text';
?>
<div class="<?= esc(trim($class)) ?>">
    <?php if ($label !== null): ?>
        <label class="block text-sm font-medium text-gray-700" for="<?= esc($name) ?>"><?= esc($label) ?></label>
    <?php endif; ?>
    <input
        id="<?= esc($name) ?>"
        name="<?= esc($name) ?>"
        type="<?= esc($type) ?>"
        value="<?= esc(old((string) $name, $value)) ?>"
        placeholder="<?= esc($placeholder) ?>"
        <?= $required ? 'required' : '' ?>
        class="<?= esc($baseClass) ?>"
        <?= $maxlength !== null ? 'maxlength="' . $maxlength . '"' : '' ?>
        <?= field_aria_attrs((string) $name, (bool) $required) ?>
        <?= $alpineErrorExpression !== null ? ':class="' . esc($alpineErrorExpression . " ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''", 'attr') . '"' : '' ?>
        <?= $alpineErrorExpression !== null ? ':aria-invalid="' . esc($alpineErrorExpression . " ? 'true' : null", 'attr') . '"' : '' ?>
        <?= $attrs ?>
    >
    <?php if ($error && ! has_field_error((string) $name)): ?>
        <p class="mt-1 text-xs text-red-600"><?= esc($error) ?></p>
    <?php endif; ?>
    <?php if ($alpineErrorExpression !== null): ?>
        <p x-show="fieldErrorFor(<?= esc(json_encode((string) $alpineErrorField, JSON_THROW_ON_ERROR), 'attr') ?>)" x-text="fieldErrorFor(<?= esc(json_encode((string) $alpineErrorField, JSON_THROW_ON_ERROR), 'attr') ?>)" x-cloak role="alert" class="mt-1 text-xs text-red-600"></p>
    <?php endif; ?>
    <?= render_field_error((string) $name) ?>
</div>
