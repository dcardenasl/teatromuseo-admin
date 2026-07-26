<?php
/**
 * @var array<string, mixed>|null $overview null when the viewer lacks
 *   cms.analytics.read — the wrapping section is hidden client-side.
 */
$totalViews = (int) ($overview['total_views'] ?? 0);
?>
<?php if ($overview === null || $totalViews === 0): ?>
    <p class="text-sm text-gray-500 text-center py-4"><?= esc(lang('Analytics.no_data')) ?></p>
<?php else: ?>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <p class="text-xs text-gray-500"><?= esc(lang('Analytics.total_views')) ?></p>
            <p class="text-xl font-bold text-gray-900"><?= esc(number_format($totalViews)) ?></p>
        </div>
        <div>
            <p class="text-xs text-gray-500"><?= esc(lang('Analytics.unique_visitors')) ?></p>
            <p class="text-xl font-bold text-gray-900"><?= esc(number_format((int) ($overview['unique_visitors'] ?? 0))) ?></p>
        </div>
    </div>

    <?php $topPageLabel = trim((string) ($overview['top_page_title'] ?? $overview['top_page'] ?? '')); ?>
    <?php if ($topPageLabel !== ''): ?>
        <div class="mt-4 pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-500"><?= esc(lang('Analytics.top_page')) ?></p>
            <p class="text-sm font-medium text-gray-700 truncate"><?= esc($topPageLabel) ?></p>
        </div>
    <?php endif; ?>

    <?php $topReferrer = trim((string) ($overview['top_referrer'] ?? '')); ?>
    <?php if ($topReferrer !== ''): ?>
        <div class="mt-3">
            <p class="text-xs text-gray-500"><?= esc(lang('Analytics.top_referrer')) ?></p>
            <p class="text-sm font-medium text-gray-700 truncate"><?= esc($topReferrer) ?></p>
        </div>
    <?php endif; ?>
<?php endif; ?>
