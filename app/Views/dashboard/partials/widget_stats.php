<?php if (($sourceState ?? 'unavailable') === 'unavailable'): ?>
    <div class="col-span-full rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <?= esc(lang('Dashboard.source_unavailable')) ?>
    </div>
<?php else: ?>
    <?php foreach ($stats as $stat): ?>
        <?= view('dashboard/partials/stat_card', [
            'label'  => $stat['label'],
            'value'  => $stat['value'],
            'icon'   => $stat['icon'],
            'suffix' => $stat['suffix'] ?? null,
        ]) ?>
    <?php endforeach; ?>
<?php endif; ?>
