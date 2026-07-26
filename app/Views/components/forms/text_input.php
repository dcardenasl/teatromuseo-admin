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
 */
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

$baseClass = trim('mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 ' . $inputClass);
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
        value="<?= esc($value) ?>"
        placeholder="<?= esc($placeholder) ?>"
        <?= $required ? 'required' : '' ?>
        class="<?= esc($baseClass) ?>"
        <?= $attrs ?>
    >
    <?php if ($error): ?>
        <p class="mt-1 text-xs text-red-600"><?= esc($error) ?></p>
    <?php endif; ?>
</div>
