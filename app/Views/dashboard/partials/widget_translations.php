<?php
/**
 * @var array<int, array<string, mixed>>|null $stats null when the viewer
 *   lacks cms.languages.read — the wrapping section is hidden client-side.
 */
?>
<?php if ($stats === null || $stats === []): ?>
    <p class="text-sm text-gray-500 text-center py-4"><?= esc(lang('Dashboard.no_languages_visible')) ?></p>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
        <?php foreach ($stats as $stat): ?>
            <?php $percentage = (float) ($stat['percentage'] ?? 0); ?>
            <div>
                <div class="flex items-center justify-between gap-2 text-xs mb-1">
                    <span class="font-semibold text-gray-700">
                        <?= esc(strtoupper((string) ($stat['code'] ?? ''))) ?> · <?= esc((string) ($stat['name'] ?? '')) ?>
                    </span>
                    <span class="font-bold text-gray-900"><?= esc((string) $percentage) ?>%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full <?= $percentage >= 100 ? 'bg-green-500' : ($percentage > 0 ? 'bg-blue-600' : 'bg-gray-300') ?>" style="width: <?= esc((string) min(100, max(0, $percentage))) ?>%"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
