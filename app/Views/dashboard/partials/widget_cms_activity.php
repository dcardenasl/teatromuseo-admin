<?php
/**
 * @var list<array{title: string, type_label: string, url: string, updated_at: string}> $items
 */
?>
<?php if (empty($items)): ?>
    <p class="text-sm text-gray-500 text-center py-4 italic"><?= esc(lang('Dashboard.noRecentActivity')) ?></p>
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
