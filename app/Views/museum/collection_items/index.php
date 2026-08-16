<?php /** @var array $limitOptions */ ?>
<?php
$showInTotemOn = lang('Museum.field_show_in_totem_on');
$showInTotemOff = lang('Museum.field_show_in_totem_off');
$isActiveOn = lang('Museum.field_is_active_on');
$isActiveOff = lang('Museum.field_is_active_off');
$publicationStatusLabels = [
    'draft' => lang('Museum.status_draft'),
    'published' => lang('Museum.status_published'),
    'archived' => lang('Museum.status_archived'),
];
$publicationStatusClasses = [
    'draft' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'published' => 'bg-green-50 text-green-700 ring-green-200',
    'archived' => 'bg-gray-100 text-gray-600 ring-gray-200',
];
?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.museum.collection_items.data') ?>',
        pageUrl: '<?= route_to('admin.museum.collection_items') ?>',
        routes: {
            showBase: '<?= route_to('admin.museum.collection_items') ?>',
            editBase: '<?= route_to('admin.museum.collection_items') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })">

    <?= view('layouts/partials/table_toolbar', [
        'title' => lang('Museum.collection_items_title'),
        'actionsView' => 'museum/collection_items/partials/toolbar_actions',
    ]) ?>

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.museum.collection_items'),
        'clearUrl'           => route_to('admin.museum.collection_items'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'museum/collection_items/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],
            'categories' => $categories ?? [],
        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600" x-show="loading">
        <?= lang('Museum.collection_items_loading') ?>
    </div>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => has_permission('catalog.collectionItem.create') ? route_to('admin.museum.collection_items.create') : null,
            'actionLabel' => 'App.create',
        ]) ?>
    </template>

    <template x-if="!loading && !error && rows.length > 0">
        <div class="<?= esc(table_wrapper_class()) ?>">
            <div class="<?= esc(table_scroll_class()) ?>">
                <table class="<?= esc(table_class()) ?>">
                    <thead class="<?= esc(table_head_class()) ?>">
                        <tr>
                            <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('name')">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('name')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_name')])) ?>">
                                    <span><?= lang('Museum.field_name') ?></span>
                                    <span aria-hidden="true" x-text="sortIcon('name')"></span>
                                </button>
                            </th>
                            <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('category_id')">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('category_id')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_category_id')])) ?>">
                                    <span><?= lang('Museum.field_category_id') ?></span>
                                    <span aria-hidden="true" x-text="sortIcon('category_id')"></span>
                                </button>
                            </th>
                            <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('inventory_code')">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('inventory_code')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_inventory_code')])) ?>">
                                    <span><?= lang('Museum.field_inventory_code') ?></span>
                                    <span aria-hidden="true" x-text="sortIcon('inventory_code')"></span>
                                </button>
                            </th>
                            <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('status')">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('status')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_status')])) ?>">
                                    <span><?= lang('Museum.field_status') ?></span>
                                    <span aria-hidden="true" x-text="sortIcon('status')"></span>
                                </button>
                            </th>
                            <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('show_in_totem')">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('show_in_totem')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_show_in_totem')])) ?>">
                                    <span><?= lang('Museum.field_show_in_totem') ?></span>
                                    <span aria-hidden="true" x-text="sortIcon('show_in_totem')"></span>
                                </button>
                            </th>
                            <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_active')">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_active')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_is_active')])) ?>">
                                    <span><?= lang('Museum.field_is_active') ?></span>
                                    <span aria-hidden="true" x-text="sortIcon('is_active')"></span>
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
                                <td class="<?= esc(table_td_class('primary')) ?>">
                                    <div class="space-y-1">
                                        <div class="font-medium text-gray-900" x-text="String(row.name ?? '-')"></div>
                                        <div class="text-xs text-gray-500" x-show="String(row.summary ?? '').trim() !== ''" x-text="String(row.summary ?? '')"></div>
                                    </div>
                                </td>
                                <td class="<?= esc(table_td_class('muted')) ?>">
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10" x-text="String(row.category ?? row.category_name ?? row.category_id ?? '-')"></span>
                                </td>
                                <td class="<?= esc(table_td_class('muted')) ?>">
                                    <div class="space-y-1">
                                        <div class="font-mono text-xs text-gray-700" x-text="String(row.inventory_code ?? '-')"></div>
                                        <div class="text-xs text-gray-500" x-show="String(row.collection_number ?? '').trim() !== ''" x-text="'N° ' + String(row.collection_number)"></div>
                                    </div>
                                </td>
                                <td class="<?= esc(table_td_class('muted')) ?>">
                                    <span
                                        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                        :class="(() => { const value = String(row.status ?? '').toLowerCase(); return <?= esc(json_encode($publicationStatusClasses)) ?>[value] ?? 'bg-gray-50 text-gray-600 ring-gray-500/10'; })()"
                                        x-text="(() => { const value = String(row.status ?? '').toLowerCase(); return <?= esc(json_encode($publicationStatusLabels)) ?>[value] ?? String(row.status ?? '-'); })()"></span>
                                </td>
                                <td class="<?= esc(table_td_class()) ?>">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                        :class="row.show_in_totem === true || row.show_in_totem === 1 || row.show_in_totem === '1' ? 'bg-green-50 text-green-700 ring-green-200' : 'bg-gray-100 text-gray-600 ring-gray-200'"
                                        x-text="row.show_in_totem === true || row.show_in_totem === 1 || row.show_in_totem === '1' ? <?= esc(json_encode($showInTotemOn)) ?> : <?= esc(json_encode($showInTotemOff)) ?>"></span>
                                </td>
                                <td class="<?= esc(table_td_class()) ?>">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                        :class="row.is_active === true || row.is_active === 1 || row.is_active === '1' ? 'bg-green-50 text-green-700 ring-green-200' : 'bg-gray-100 text-gray-600 ring-gray-200'"
                                        x-text="row.is_active === true || row.is_active === 1 || row.is_active === '1' ? <?= esc(json_encode($isActiveOn)) ?> : <?= esc(json_encode($isActiveOff)) ?>"></span>
                                </td>
                                <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                                <td class="<?= esc(table_td_class()) ?>">
                                    <div class="flex items-center gap-2">
                                        <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                        <?php if (has_permission('catalog.collectionItem.update')): ?>
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
