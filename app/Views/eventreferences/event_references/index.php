<?php
/** @var array $limitOptions */
$eventReferenceEventLabels = array_map('strval', $events ?? []);
?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.eventreferences.event_references.data') ?>',
        pageUrl: '<?= route_to('admin.eventreferences.event_references') ?>',
        routes: {
            showBase: '<?= route_to('admin.eventreferences.event_references') ?>',
            editBase: '<?= route_to('admin.eventreferences.event_references') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('EventReferences.event_references_title'),
        'actionsView' => 'eventreferences/event_references/partials/toolbar_actions',
    ]) ?>


    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.eventreferences.event_references'),
        'clearUrl'           => route_to('admin.eventreferences.event_references'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'eventreferences/event_references/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],
            'events' => $events ?? [],
        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600" x-show="loading">
        <?= lang('EventReferences.event_references_loading') ?>
    </div>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => has_permission('event.event-references.write') ? route_to('admin.eventreferences.event_references.create') : null,
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
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('event_id')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('EventReferences.field_event_id')])) ?>">
                                <span><?= lang('EventReferences.field_event_id') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('event_id')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('source_system')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('source_system')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('EventReferences.field_source_system')])) ?>">
                                <span><?= lang('EventReferences.field_source_system') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('source_system')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('source_type')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('source_type')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('EventReferences.field_source_type')])) ?>">
                                <span><?= lang('EventReferences.field_source_type') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('source_type')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('source_id')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('source_id')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('EventReferences.field_source_id')])) ?>">
                                <span><?= lang('EventReferences.field_source_id') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('source_id')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('relation')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('relation')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('EventReferences.field_relation')])) ?>">
                                <span><?= lang('EventReferences.field_relation') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('relation')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('metadata')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('metadata')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('EventReferences.field_metadata')])) ?>">
                                <span><?= lang('EventReferences.field_metadata') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('metadata')"></span>
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
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="(<?= esc(json_encode($eventReferenceEventLabels)) ?>)[String(row.event_id)] ?? '—'"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.source_system ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.source_type ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.source_id ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.relation ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="typeof row.metadata === 'object' && row.metadata !== null ? JSON.stringify(row.metadata) : String(row.metadata ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                    <?php if (has_permission('event.event-references.write')): ?>
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
