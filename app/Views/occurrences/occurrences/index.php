<?php
/** @var array $limitOptions */
$occurrenceStatusLabels = [
    'draft' => lang('Occurrences.option_status_draft'),
    'published' => lang('Occurrences.option_status_published'),
    'cancelled' => lang('Occurrences.option_status_cancelled'),
];
$occurrenceEventLabels = array_map('strval', $events ?? []);
$occurrenceVenueLabels = array_map('strval', $venues ?? []);
?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.occurrences.occurrences.data') ?>',
        pageUrl: '<?= route_to('admin.occurrences.occurrences') ?>',
        routes: {
            showBase: '<?= route_to('admin.occurrences.occurrences') ?>',
            editBase: '<?= route_to('admin.occurrences.occurrences') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Occurrences.occurrences_title'),
        'actionsView' => 'occurrences/occurrences/partials/toolbar_actions',
    ]) ?>


    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.occurrences.occurrences'),
        'clearUrl'           => route_to('admin.occurrences.occurrences'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'occurrences/occurrences/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],
            'events' => $events ?? [],
            'venues' => $venues ?? [],
        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600" x-show="loading">
        <?= lang('Occurrences.occurrences_loading') ?>
    </div>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => has_permission('event.occurrences.write') ? route_to('admin.occurrences.occurrences.create') : null,
            'actionLabel' => 'App.create',
        ]) ?>
    </template>
    <template x-if="!loading && !error && rows.length > 0">
        <div class="<?= esc(table_wrapper_class()) ?>">
            <div class="<?= esc(table_scroll_class()) ?>">
            <table class="<?= esc(table_class()) ?>">
                <thead class="<?= esc(table_head_class()) ?>">
                    <tr>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('event_id')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('event_id')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Occurrences.field_event_id')])) ?>">
                                <span><?= lang('Occurrences.field_event_id') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('event_id')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('venue_id')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('venue_id')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Occurrences.field_venue_id')])) ?>">
                                <span><?= lang('Occurrences.field_venue_id') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('venue_id')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('start_time')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('start_time')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Occurrences.field_start_time')])) ?>">
                                <span><?= lang('Occurrences.field_start_time') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('start_time')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('end_time')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('end_time')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Occurrences.field_end_time')])) ?>">
                                <span><?= lang('Occurrences.field_end_time') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('end_time')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('status')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('status')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Occurrences.field_status')])) ?>">
                                <span><?= lang('Occurrences.field_status') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('status')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('capacity')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('capacity')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Occurrences.field_capacity')])) ?>">
                                <span><?= lang('Occurrences.field_capacity') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('capacity')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('available_spots')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('available_spots')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Occurrences.field_available_spots')])) ?>">
                                <span><?= lang('Occurrences.field_available_spots') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('available_spots')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('created_at')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('created_at')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TableColumns.created_at')])) ?>">
                                <span><?= lang('TableColumns.created_at') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('created_at')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>"><?= lang('TableColumns.actions') ?></th>
                    </tr>
                </thead>
                <tbody class="<?= esc(table_body_class()) ?>">
                    <template x-for="row in rows" :key="String(row.id ?? Math.random())">
                        <tr class="<?= esc(table_row_class()) ?>">
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="(<?= esc(json_encode($occurrenceEventLabels)) ?>)[String(row.event_id)] ?? '—'"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="(<?= esc(json_encode($occurrenceVenueLabels)) ?>)[String(row.venue_id)] ?? '—'"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate({ date: row.start_time, timezone: row.timezone })"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate({ date: row.end_time, timezone: row.timezone })"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="(<?= esc(json_encode($occurrenceStatusLabels)) ?>)[String(row.status)] ?? String(row.status ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.capacity ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.available_spots ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                    <?php if (has_permission('event.occurrences.write')): ?>
                                        <a :href="editUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
                                    <?php endif; ?>
                                </div>
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
