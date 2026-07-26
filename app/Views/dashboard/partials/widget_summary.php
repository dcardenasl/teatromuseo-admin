<?php
/**
 * @var list<array{label: string, count: int, url: string, icon: string, badge: ?array{count: int, label: string, url: string}}> $items
 */
?>
<?php if (empty($items)): ?>
    <p class="text-sm text-gray-500 text-center py-4"><?= esc(lang('Dashboard.no_summary_visible')) ?></p>
<?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6 gap-3">
        <?php foreach ($items as $item): ?>
            <a href="<?= esc($item['badge']['url'] ?? $item['url']) ?>" class="relative flex flex-col items-center justify-center gap-1 rounded-lg border border-gray-200 p-4 text-center hover:bg-gray-50 hover:border-brand-200 transition-colors">
                <?php if ($item['badge'] !== null): ?>
                    <span
                        class="absolute -top-2 -right-2 inline-flex items-center justify-center min-w-[1.375rem] h-5 px-1 rounded-full bg-red-500 text-white text-[11px] font-bold shadow-sm"
                        title="<?= esc($item['badge']['label']) ?>"
                    ><?= esc((string) $item['badge']['count']) ?></span>
                <?php endif; ?>
                <?= ui_icon($item['icon'], 'h-5 w-5 text-brand-500') ?>
                <span class="text-xl font-bold text-gray-900"><?= esc((string) $item['count']) ?></span>
                <span class="text-xs font-medium text-gray-500"><?= esc($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
