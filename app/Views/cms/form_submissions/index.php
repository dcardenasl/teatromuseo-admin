<?php
/** @var array $limitOptions */
/** @var string $activeStatus */
/** @var array<string, int> $counts */

$statusTabs = [
    ''         => ['label' => 'Todos',      'color' => 'gray'],
    'new'      => ['label' => lang('FormSubmissions.status_new'),      'color' => 'blue'],
    'read'     => ['label' => lang('FormSubmissions.status_read'),     'color' => 'gray'],
    'replied'  => ['label' => lang('FormSubmissions.status_replied'),  'color' => 'green'],
    'spam'     => ['label' => lang('FormSubmissions.status_spam'),     'color' => 'red'],
    'archived' => ['label' => lang('FormSubmissions.status_archived'), 'color' => 'yellow'],
];

$totalCount = array_sum($counts);
$badgeClass = fn (string $color): string => match($color) {
    'blue'   => 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'green'  => 'bg-green-50 text-green-700 ring-green-600/20',
    'red'    => 'bg-red-50 text-red-700 ring-red-600/20',
    'yellow' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
    default  => 'bg-gray-50 text-gray-700 ring-gray-600/20',
};
?>

<?php /* ── Status tab filter ────────────────────────────────────────────── */ ?>
<div class="mb-4 flex flex-wrap gap-2">
    <?php foreach ($statusTabs as $statusKey => $tab): ?>
        <?php
        $isActive = $activeStatus === $statusKey;
        $count = $statusKey === '' ? $totalCount : ($counts[$statusKey] ?? 0);
        $tabUrl = route_to('admin.cms.form_submissions') . ($statusKey !== '' ? '?status=' . $statusKey : '');
        $activeClass = $isActive
            ? 'bg-brand-600 text-white shadow-sm'
            : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200';
        ?>
        <a href="<?= esc($tabUrl) ?>"
           class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors <?= $activeClass ?>">
            <?= esc($tab['label']) ?>
            <?php if ($count > 0): ?>
                <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-xs font-semibold ring-1 ring-inset <?= $isActive ? 'bg-white/20 text-white ring-white/30' : esc($badgeClass($tab['color'])) ?>">
                    <?= $count ?>
                </span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<?php
$dataUrl = route_to('admin.cms.form_submissions.data') . ($activeStatus !== '' ? '?status=' . urlencode($activeStatus) : '');
?>
<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= esc($dataUrl) ?>',
        pageUrl: '<?= route_to('admin.cms.form_submissions') ?>',
        defaultSort: '-created_at',
        routes: { showBase: '<?= route_to('admin.cms.form_submissions') ?>' },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title' => lang('FormSubmissions.title'),
    ]) ?>

    <template x-if="loading && rows.length === 0">
        <?= view('components/display/loading_state', [
            'title'       => 'FormSubmissions.title',
            'description' => 'App.loading_refreshing',
            'icon'        => 'mail',
        ]) ?>
    </template>
    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title'       => 'App.no_results',
            'description' => 'App.no_results_desc',
        ]) ?>
    </template>

    <template x-if="!error && rows.length > 0">
        <div class="<?= esc(table_wrapper_class()) ?> relative">
            <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center" x-cloak>
                <div class="flex items-center gap-2 rounded-lg bg-white/95 px-4 py-2 shadow-sm border border-gray-100">
                    <?= ui_icon('refresh-ccw', 'h-4 w-4 animate-spin text-brand-600') ?>
                    <span class="text-xs font-semibold text-gray-700"><?= esc(lang('App.loading_refreshing')) ?></span>
                </div>
            </div>
            <div class="<?= esc(table_scroll_class()) ?>">
            <table class="<?= esc(table_class()) ?>">
                <thead class="<?= esc(table_head_class()) ?>">
                    <tr>
                        <th class="<?= esc(table_th_class()) ?>"><?= lang('FormSubmissions.field_name') ?></th>
                        <th class="<?= esc(table_th_class()) ?>"><?= lang('FormSubmissions.field_email') ?></th>
                        <th class="<?= esc(table_th_class()) ?>"><?= lang('FormSubmissions.field_status') ?></th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('created_at')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700"
                                @click="toggleSort('created_at')">
                                <span><?= lang('FormSubmissions.field_date') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('created_at')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>"><?= lang('TableColumns.actions') ?></th>
                    </tr>
                </thead>
                <tbody class="<?= esc(table_body_class()) ?>">
                    <template x-for="row in rows" :key="String(row.id ?? Math.random())">
                        <tr class="<?= esc(table_row_class()) ?>"
                            :class="row.status === 'new' ? 'font-semibold bg-blue-50/40' : ''">
                            <td class="<?= esc(table_td_class()) ?>">
                                <span x-text="String((row.form_data && row.form_data.name) ? row.form_data.name : '-')"></span>
                                <template x-if="row.status === 'new'">
                                    <span class="ml-1 inline-flex items-center rounded-full bg-blue-100 px-1.5 py-0.5 text-xs font-semibold text-blue-700">
                                        <?= lang('FormSubmissions.status_new') ?>
                                    </span>
                                </template>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>"
                                x-text="String((row.form_data && row.form_data.email) ? row.form_data.email : '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <?php
                                $statusLabels = json_encode([
                                    'new'      => lang('FormSubmissions.status_new'),
                                    'read'     => lang('FormSubmissions.status_read'),
                                    'replied'  => lang('FormSubmissions.status_replied'),
                                    'spam'     => lang('FormSubmissions.status_spam'),
                                    'archived' => lang('FormSubmissions.status_archived'),
                                ]);
?>
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="{
                                        'bg-blue-50 text-blue-700 ring-blue-600/20':      row.status === 'new',
                                        'bg-gray-50 text-gray-600 ring-gray-500/10':      row.status === 'read',
                                        'bg-green-50 text-green-700 ring-green-600/20':   row.status === 'replied',
                                        'bg-red-50 text-red-700 ring-red-600/20':         row.status === 'spam',
                                        'bg-yellow-50 text-yellow-800 ring-yellow-600/20': row.status === 'archived',
                                    }"
                                    x-text="(<?= htmlspecialchars($statusLabels, ENT_QUOTES) ?>)[row.status] ?? row.status">
                                </span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>">
                                    <?= lang('App.view') ?>
                                </a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            </div>
        </div>
    </template>

    <?= view('layouts/partials/remote_pagination') ?>
</section>
