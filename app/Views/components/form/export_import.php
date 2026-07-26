<?php
/**
 * @var string $importUrl
 * @var string|null $importLabel
 * @var string|null $previewView
 * @var array<int, array<string, mixed>>|null $previewRows
 */

helper('form');

$importUrl = $importUrl ?? '';
$importLabel = $importLabel ?? '';
$previewView = $previewView ?? 'components/form/import_preview';
$previewRows = is_array($previewRows ?? null) ? $previewRows : [];

$importText = $importLabel !== '' ? lang($importLabel) : safe_lang('App.import', 'Import');
?>
<div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h4 class="text-sm font-semibold text-gray-900"><?= esc($importText) ?></h4>
            <p class="mt-1 text-xs text-gray-500"><?= esc(safe_lang('App.csv_help', 'Use a CSV file to export or import records.')) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="post" action="<?= esc($importUrl, 'attr') ?>" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                <?= csrf_field() ?>
                <label class="sr-only" for="csv_file"><?= esc($importText) ?></label>
                <input id="csv_file" type="file" name="csv_file" accept=".csv,text/csv" class="<?= esc(input_class('csv_file')) ?>">
                <button type="submit" class="<?= esc(action_button_class('primary')) ?>">
                    <?= ui_icon('upload', 'h-3.5 w-3.5') ?>
                    <span><?= esc($importText) ?></span>
                </button>
            </form>
        </div>
    </div>

    <?php if ($previewRows !== []): ?>
        <div class="mt-4">
            <?= view($previewView, ['rows' => $previewRows]) ?>
        </div>
    <?php endif; ?>
</div>
