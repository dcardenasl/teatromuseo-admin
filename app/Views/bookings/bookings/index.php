<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.bookings.bookings.data') ?>',
        pageUrl: '<?= route_to('admin.bookings.bookings') ?>',
        routes: {
            showBase: '<?= route_to('admin.bookings.bookings') ?>',
            editBase: '<?= route_to('admin.bookings.bookings') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Bookings.bookings_title'),
        'actionsView' => 'bookings/bookings/partials/toolbar_actions',
    ]) ?>


    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.bookings.bookings'),
        'clearUrl'           => route_to('admin.bookings.bookings'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'bookings/bookings/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],

        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600" x-show="loading">
        <?= lang('Bookings.bookings_loading') ?>
    </div>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => has_permission('event.bookings.write') ? route_to('admin.bookings.bookings.create') : null,
            'actionLabel' => 'App.create',
        ]) ?>
    </template>
    <template x-if="!loading && !error && rows.length > 0">
        <div class="<?= esc(table_wrapper_class()) ?>">
            <div class="<?= esc(table_scroll_class()) ?>">
            <table class="<?= esc(table_class()) ?>">
                <thead class="<?= esc(table_head_class()) ?>">
                    <tr>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('guest_email')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('guest_email')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Bookings.field_guest_email')])) ?>">
                                <span><?= lang('Bookings.field_guest_email') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('guest_email')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('total_amount')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('total_amount')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Bookings.field_total_amount')])) ?>">
                                <span><?= lang('Bookings.field_total_amount') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('total_amount')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('status')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('status')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Bookings.field_status')])) ?>">
                                <span><?= lang('Bookings.field_status') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('status')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('reserved_until')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('reserved_until')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Bookings.field_reserved_until')])) ?>">
                                <span><?= lang('Bookings.field_reserved_until') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('reserved_until')"></span>
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
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.guest_email ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.total_amount ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10" x-text="String(row.status ?? '-')"></span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.reserved_until)"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                    <?php if (has_permission('event.bookings.write')): ?>
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
