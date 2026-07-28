<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.museum.collection_items.data') ?>',
        pageUrl: '<?= route_to('admin.museum.collection_items') ?>',
        routes: {
            showBase: '<?= route_to('admin.museum.collection_items') ?>',
            editBase: '<?= route_to('admin.museum.collection_items') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Museum.collection_items_title'),
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
            'actionUrl' => route_to('admin.museum.collection_items.create'),
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
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('summary')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('summary')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_summary')])) ?>">
                                <span><?= lang('Museum.field_summary') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('summary')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('curiosidad')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('curiosidad')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_curiosidad')])) ?>">
                                <span><?= lang('Museum.field_curiosidad') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('curiosidad')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('contenido')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('contenido')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_contenido')])) ?>">
                                <span><?= lang('Museum.field_contenido') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('contenido')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('origin')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('origin')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_origin')])) ?>">
                                <span><?= lang('Museum.field_origin') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('origin')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('period')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('period')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_period')])) ?>">
                                <span><?= lang('Museum.field_period') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('period')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('creator')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('creator')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_creator')])) ?>">
                                <span><?= lang('Museum.field_creator') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('creator')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('ubicacion')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('ubicacion')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_ubicacion')])) ?>">
                                <span><?= lang('Museum.field_ubicacion') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('ubicacion')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('materials')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('materials')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_materials')])) ?>">
                                <span><?= lang('Museum.field_materials') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('materials')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('cover_file_id')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('cover_file_id')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_cover_file_id')])) ?>">
                                <span><?= lang('Museum.field_cover_file_id') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('cover_file_id')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('gallery_file_ids')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('gallery_file_ids')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_gallery_file_ids')])) ?>">
                                <span><?= lang('Museum.field_gallery_file_ids') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('gallery_file_ids')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('show_in_totem')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('show_in_totem')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_show_in_totem')])) ?>">
                                <span><?= lang('Museum.field_show_in_totem') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('show_in_totem')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('internal_notes')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('internal_notes')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_internal_notes')])) ?>">
                                <span><?= lang('Museum.field_internal_notes') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('internal_notes')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('collection_number')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('collection_number')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_collection_number')])) ?>">
                                <span><?= lang('Museum.field_collection_number') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('collection_number')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('collection_group')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('collection_group')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_collection_group')])) ?>">
                                <span><?= lang('Museum.field_collection_group') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('collection_group')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('physical_description')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('physical_description')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_physical_description')])) ?>">
                                <span><?= lang('Museum.field_physical_description') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('physical_description')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('dimensions')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('dimensions')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_dimensions')])) ?>">
                                <span><?= lang('Museum.field_dimensions') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('dimensions')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('ingress_type')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('ingress_type')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_ingress_type')])) ?>">
                                <span><?= lang('Museum.field_ingress_type') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('ingress_type')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('donated_by')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('donated_by')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_donated_by')])) ?>">
                                <span><?= lang('Museum.field_donated_by') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('donated_by')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('tags')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('tags')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_tags')])) ?>">
                                <span><?= lang('Museum.field_tags') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('tags')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('links')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('links')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_links')])) ?>">
                                <span><?= lang('Museum.field_links') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('links')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('company_history')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('company_history')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Museum.field_company_history')])) ?>">
                                <span><?= lang('Museum.field_company_history') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('company_history')"></span>
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
                            <td class="<?= esc(table_td_class('primary')) ?>" x-text="String(row.name ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.category ?? row.category_name ?? row.category_id ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.inventory_code ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.status ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.summary ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.curiosidad ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.contenido ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.origin ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.period ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.creator ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.ubicacion ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.materials ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.cover_file_id ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.gallery_file_ids ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center">
                                    <template x-if="row.show_in_totem">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <template x-if="!row.show_in_totem">
                                        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                </span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.internal_notes ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.collection_number ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.collection_group ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.physical_description ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.dimensions ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.ingress_type ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.donated_by ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.tags ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.links ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.company_history ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center">
                                    <template x-if="row.is_active">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <template x-if="!row.is_active">
                                        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                </span>
                            </td>
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
