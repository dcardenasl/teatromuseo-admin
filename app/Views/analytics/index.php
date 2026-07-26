<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
    <?= view('layouts/partials/table_toolbar', [
        'title' => lang('Analytics.title'),
    ]) ?>

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'      => route_to('admin.analytics'),
        'clearUrl'       => route_to('admin.analytics'),
        'hasFilters'     => $hasFilters ?? false,
        'filterDefaults' => $defaultFilters ?? [],
        'fieldsView'     => 'analytics/partials/filters',
        'fieldsData'     => [
            'filters'       => $filters,
            'periodOptions' => $periodOptions ?? [],
        ],
        'submitLabel'    => lang('App.apply_filters'),
    ]) ?>
</section>

<?php
$totalViews     = (int) ($overview['total_views']     ?? 0);
    $uniqueVisitors = (int) ($overview['unique_visitors'] ?? 0);
    $topPage        = (string) ($overview['top_page_title'] ?? $overview['top_page'] ?? '');
    $topReferrer    = (string) ($overview['top_referrer']  ?? '');
    ?>

<!-- KPI Cards -->
<section class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-gray-500"><?= lang('Analytics.total_views') ?></p>
        <p class="mt-1 text-2xl font-semibold text-gray-900"><?= esc(number_format($totalViews)) ?></p>
    </article>
    <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-gray-500"><?= lang('Analytics.unique_visitors') ?></p>
        <p class="mt-1 text-2xl font-semibold text-gray-900"><?= esc(number_format($uniqueVisitors)) ?></p>
    </article>
    <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-gray-500"><?= lang('Analytics.top_page') ?></p>
        <p class="mt-1 text-sm font-semibold text-gray-900 truncate" title="<?= esc($topPage) ?>">
            <?= $topPage !== '' ? esc($topPage) : '<span class="text-gray-400">' . lang('Analytics.no_data') . '</span>' ?>
        </p>
    </article>
    <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-gray-500"><?= lang('Analytics.top_referrer') ?></p>
        <p class="mt-1 text-sm font-semibold text-gray-900 truncate" title="<?= esc($topReferrer) ?>">
            <?= $topReferrer !== '' ? esc($topReferrer) : '<span class="text-gray-400">' . lang('Analytics.no_data') . '</span>' ?>
        </p>
    </article>
</section>

<!-- Traffic Trend -->
<?php if (! empty($timeseries)): ?>
<section class="mt-6 bg-white border border-gray-200 rounded-xl shadow-sm p-5">
    <h3 class="text-base font-semibold text-gray-900 mb-4"><?= lang('Analytics.trend_title') ?></h3>
    <div class="<?= esc(table_wrapper_class()) ?>">
        <div class="<?= esc(table_scroll_class()) ?>">
            <table class="<?= esc(table_class()) ?>">
                <thead class="<?= esc(table_head_class()) ?>">
                    <tr>
                        <th class="<?= esc(table_th_class()) ?>"><?= lang('TableColumns.period') ?></th>
                        <th class="<?= esc(table_th_class()) ?>"><?= lang('Analytics.col_views') ?></th>
                        <th class="<?= esc(table_th_class()) ?>"><?= lang('Analytics.col_visitors') ?></th>
                    </tr>
                </thead>
                <tbody class="<?= esc(table_body_class()) ?>">
                    <?php foreach ($timeseries as $point): ?>
                        <tr class="<?= esc(table_row_class()) ?>">
                            <td class="<?= esc(table_td_class()) ?>"><?= esc((string) ($point['label'] ?? '-')) ?></td>
                            <td class="<?= esc(table_td_class('primary')) ?>"><?= esc(number_format((int) ($point['views'] ?? 0))) ?></td>
                            <td class="<?= esc(table_td_class('muted')) ?>"><?= esc(number_format((int) ($point['unique_visitors'] ?? 0))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Top Pages -->
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-base font-semibold text-gray-900 mb-4"><?= lang('Analytics.top_pages_title') ?></h3>
        <?php if (! empty($pages)): ?>
            <div class="<?= esc(table_wrapper_class()) ?>">
                <div class="<?= esc(table_scroll_class()) ?>">
                    <table class="<?= esc(table_class()) ?>">
                        <thead class="<?= esc(table_head_class()) ?>">
                            <tr>
                                <th class="<?= esc(table_th_class()) ?>"><?= lang('Analytics.col_page') ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= lang('Analytics.col_views') ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= lang('Analytics.col_share') ?></th>
                            </tr>
                        </thead>
                        <tbody class="<?= esc(table_body_class()) ?>">
                            <?php foreach ($pages as $page): ?>
                                <tr class="<?= esc(table_row_class()) ?>">
                                    <td class="<?= esc(table_td_class()) ?>">
                                        <span class="block truncate max-w-[200px]" title="<?= esc((string) ($page['url'] ?? '')) ?>">
                                            <?php
                                                $pageLabel = (string) ($page['page_title'] ?? $page['url'] ?? '-');
                                $pageUrl   = (string) ($page['url'] ?? '');
                                ?>
                                            <?= esc($pageLabel) ?>
                                            <?php if ($pageLabel !== $pageUrl && $pageUrl !== ''): ?>
                                                <span class="block text-xs text-gray-400 truncate"><?= esc($pageUrl) ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="<?= esc(table_td_class('primary')) ?>"><?= esc(number_format((int) ($page['views'] ?? 0))) ?></td>
                                    <td class="<?= esc(table_td_class('muted')) ?>"><?= esc((string) ($page['percentage'] ?? 0)) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-400"><?= lang('Analytics.no_data') ?></p>
        <?php endif; ?>
    </section>

    <!-- Top Referrers -->
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-base font-semibold text-gray-900 mb-4"><?= lang('Analytics.top_referrers_title') ?></h3>
        <?php if (! empty($referrers)): ?>
            <div class="<?= esc(table_wrapper_class()) ?>">
                <div class="<?= esc(table_scroll_class()) ?>">
                    <table class="<?= esc(table_class()) ?>">
                        <thead class="<?= esc(table_head_class()) ?>">
                            <tr>
                                <th class="<?= esc(table_th_class()) ?>"><?= lang('Analytics.col_referrer') ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= lang('Analytics.col_views') ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= lang('Analytics.col_share') ?></th>
                            </tr>
                        </thead>
                        <tbody class="<?= esc(table_body_class()) ?>">
                            <?php foreach ($referrers as $ref): ?>
                                <tr class="<?= esc(table_row_class()) ?>">
                                    <td class="<?= esc(table_td_class()) ?>"><?= esc((string) ($ref['domain'] ?? '-')) ?></td>
                                    <td class="<?= esc(table_td_class('primary')) ?>"><?= esc(number_format((int) ($ref['views'] ?? 0))) ?></td>
                                    <td class="<?= esc(table_td_class('muted')) ?>"><?= esc((string) ($ref['percentage'] ?? 0)) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-400"><?= lang('Analytics.no_data') ?></p>
        <?php endif; ?>
    </section>

</div>

<!-- Device Breakdown -->
<?php
$deviceMap = [
    'desktop' => lang('Analytics.device_desktop'),
    'mobile'  => lang('Analytics.device_mobile'),
    'tablet'  => lang('Analytics.device_tablet'),
    'bot'     => lang('Analytics.device_bot'),
    'unknown' => lang('Analytics.device_unknown'),
];
    $deviceTotal = 0;
    foreach ($deviceMap as $key => $_label) {
        $deviceTotal += (int) ($devices[$key] ?? 0);
    }
    ?>
<section class="mt-6 bg-white border border-gray-200 rounded-xl shadow-sm p-5">
    <h3 class="text-base font-semibold text-gray-900 mb-4"><?= lang('Analytics.device_title') ?></h3>
    <?php if ($deviceTotal > 0): ?>
        <div class="space-y-3">
            <?php foreach ($deviceMap as $key => $label): ?>
                <?php
                    $count = (int) ($devices[$key] ?? 0);
                $pct   = $deviceTotal > 0 ? round($count / $deviceTotal * 100, 1) : 0;
                ?>
                <div class="flex items-center gap-3">
                    <span class="w-20 text-sm text-gray-600 shrink-0"><?= esc($label) ?></span>
                    <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-brand-500 h-full" style="width: <?= esc((string) $pct) ?>%"></div>
                    </div>
                    <span class="w-24 text-right text-sm text-gray-700 shrink-0"><?= esc(number_format($count)) ?> <span class="text-gray-400">(<?= esc((string) $pct) ?>%)</span></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-sm text-gray-400"><?= lang('Analytics.no_data') ?></p>
    <?php endif; ?>
</section>
