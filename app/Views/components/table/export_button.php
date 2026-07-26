<?php
/**
 * CSV export button.
 * @var string $exportUrl
 * @var string|null $label
 * @var string|null $title
 */

$exportUrl = $exportUrl ?? '';
$label = $label ?? '';
$title = $title ?? '';

$buttonLabel = $label !== '' ? lang($label) : safe_lang('App.export', 'Export');
$buttonTitle = $title !== '' ? lang($title) : safe_lang('App.export', 'Export');
?>
<a href="<?= esc($exportUrl, 'attr') ?>" class="<?= esc(action_button_class()) ?>" title="<?= esc($buttonTitle, 'attr') ?>">
    <?= ui_icon('download', 'h-3.5 w-3.5') ?>
    <span><?= esc($buttonLabel) ?></span>
</a>
