<?php
/**
 * @var string $name
 * @var string $label
 * @var array<string, string> $options
 * @var list<int> $selected
 * @var string|null $help
 */

$options = $options ?? [];
$selected = old($name, $selected ?? []);
if (! is_array($selected)) {
    $selected = [];
}
$selected = array_map('strval', $selected);
$help = $help ?? '';
?>
<fieldset>
    <legend class="block text-sm font-medium text-gray-700"><?= esc(lang($label)) ?></legend>
    <?php if ($help !== ''): ?>
        <p class="mt-1 text-xs text-gray-500"><?= esc(lang($help)) ?></p>
    <?php endif; ?>
    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
        <?php foreach ($options as $value => $optionLabel): ?>
            <label class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:border-brand-300">
                <input type="checkbox" name="<?= esc($name, 'attr') ?>[]" value="<?= esc($value, 'attr') ?>" <?= in_array((string) $value, $selected, true) ? 'checked' : '' ?> class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <span><?= esc($optionLabel) ?></span>
            </label>
        <?php endforeach; ?>
    </div>
    <?php if ($options === []): ?>
        <p class="mt-3 text-sm text-gray-500"><?= esc(lang('Entries.taxonomy_empty')) ?></p>
    <?php endif; ?>
</fieldset>
