<?php
/**
 * @var array<int, array<string, mixed>> $rows
 */

$rows = is_array($rows ?? null) ? $rows : [];
?>
<div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
    <div class="flex items-center justify-between">
        <h5 class="text-sm font-semibold text-gray-900"><?= esc(safe_lang('App.preview', 'Preview')) ?></h5>
        <span class="text-xs text-gray-500"><?= esc(count($rows)) ?> <?= esc(safe_lang('App.rows', 'rows')) ?></span>
    </div>
    <div class="mt-3 overflow-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <?php $columns = $rows !== [] ? array_keys((array) $rows[0]) : []; ?>
                    <?php foreach ($columns as $column): ?>
                        <th class="px-3 py-2 text-left font-medium text-gray-700"><?= esc((string) $column) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <td class="px-3 py-2 text-gray-700"><?= esc((string) ($row[$column] ?? '')) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
