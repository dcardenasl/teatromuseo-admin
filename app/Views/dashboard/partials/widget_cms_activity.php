<?php
/**
 * @var list<array{title: string, type_label: string, url: string, updated_at: string}> $items
 * @var array<string, string> $sourceStates
 */
?>
<?php $unavailableSources = array_keys(array_filter($sourceStates ?? [], static fn (string $state): bool => $state === 'unavailable')); ?>
<?php if ($unavailableSources !== []): ?>
    <div class="mb-3 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
        <?= ui_icon('triangle-alert', 'h-4 w-4 shrink-0') ?>
        <span><?= esc(lang('Dashboard.activity_sources_unavailable')) ?></span>
    </div>
<?php endif; ?>
<?php if (empty($items)): ?>
    <p class="text-sm text-gray-500 text-center py-4 italic">
        <?= esc($unavailableSources !== [] ? lang('Dashboard.source_unavailable') : lang('Dashboard.noRecentActivity')) ?>
    </p>
<?php else: ?>
    <ul class="divide-y divide-gray-100">
        <?php foreach ($items as $item): ?>
            <li class="py-3">
                <a href="<?= esc($item['url']) ?>" class="flex items-center justify-between gap-3 group">
                    <span class="min-w-0">
                        <span class="block text-sm font-medium text-gray-800 truncate group-hover:text-brand-700"><?= esc($item['title']) ?></span>
                        <span class="text-xs text-gray-400"><?= esc($item['type_label']) ?></span>
                    </span>
                    <?php if ($item['updated_at'] !== ''): ?>
                        <time class="shrink-0 text-xs text-gray-500" datetime="<?= esc($item['updated_at']) ?>"><?= esc(format_date($item['updated_at'])) ?></time>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
