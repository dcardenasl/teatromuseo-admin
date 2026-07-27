<?php
/** @var array $limitOptions */
$ticketTypeEventLabels = array_map('strval', $events ?? []);
$ticketTypeOccurrenceLabels = array_map('strval', $occurrences ?? []);
?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.tickettypes.ticket_types.data') ?>',
        pageUrl: '<?= route_to('admin.tickettypes.ticket_types') ?>',
        routes: {
            showBase: '<?= route_to('admin.tickettypes.ticket_types') ?>',
            editBase: '<?= route_to('admin.tickettypes.ticket_types') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('TicketTypes.ticket_types_title'),
        'actionsView' => 'tickettypes/ticket_types/partials/toolbar_actions',
    ]) ?>


    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.tickettypes.ticket_types'),
        'clearUrl'           => route_to('admin.tickettypes.ticket_types'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'tickettypes/ticket_types/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],
            'events' => $events ?? [],
            'occurrences' => $occurrences ?? [],
        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600" x-show="loading">
        <?= lang('TicketTypes.ticket_types_loading') ?>
    </div>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => route_to('admin.tickettypes.ticket_types.create'),
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
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('event_id')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TicketTypes.field_event_id')])) ?>">
                                <span><?= lang('TicketTypes.field_event_id') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('event_id')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('occurrence_id')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('occurrence_id')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TicketTypes.field_occurrence_id')])) ?>">
                                <span><?= lang('TicketTypes.field_occurrence_id') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('occurrence_id')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('name')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('name')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TicketTypes.field_name')])) ?>">
                                <span><?= lang('TicketTypes.field_name') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('name')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('price')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('price')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TicketTypes.field_price')])) ?>">
                                <span><?= lang('TicketTypes.field_price') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('price')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('capacity')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('capacity')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TicketTypes.field_capacity')])) ?>">
                                <span><?= lang('TicketTypes.field_capacity') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('capacity')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('available_spots')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('available_spots')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TicketTypes.field_available_spots')])) ?>">
                                <span><?= lang('TicketTypes.field_available_spots') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('available_spots')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('sales_start')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('sales_start')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TicketTypes.field_sales_start')])) ?>">
                                <span><?= lang('TicketTypes.field_sales_start') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('sales_start')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('sales_end')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('sales_end')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TicketTypes.field_sales_end')])) ?>">
                                <span><?= lang('TicketTypes.field_sales_end') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('sales_end')"></span>
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
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="(<?= esc(json_encode($ticketTypeEventLabels)) ?>)[String(row.event_id)] ?? '—'"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="(<?= esc(json_encode($ticketTypeOccurrenceLabels)) ?>)[String(row.occurrence_id)] ?? '—'"></td>
                            <td class="<?= esc(table_td_class('primary')) ?>" x-text="String(row.name ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.price ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.capacity ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.available_spots ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.sales_start)"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.sales_end)"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                    <a :href="editUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
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
