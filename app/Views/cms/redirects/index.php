<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.cms.redirects.data') ?>',
        pageUrl: '<?= route_to('admin.cms.redirects') ?>',
        defaultSort: '-created_at',
        routes: {
            showBase: '<?= route_to('admin.cms.redirects') ?>',
            editBase: '<?= route_to('admin.cms.redirects') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Redirects.redirects_title'),
        'actionsView' => 'cms/redirects/partials/toolbar_actions',
    ]) ?>
    <?php /* CSV scaffold hooks: components/table/export_button, components/form/export_import, components/form/import_preview */ ?>

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.cms.redirects'),
        'clearUrl'           => route_to('admin.cms.redirects'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'cms/redirects/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],

        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <template x-if="loading && rows.length === 0">
        <?= view('components/display/loading_state', [
            'title'       => 'Redirects.redirects_loading',
            'description' => 'App.loading_refreshing',
            'icon'        => 'corner-up-right',
        ]) ?>
    </template>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => route_to('admin.cms.redirects.create'),
            'actionLabel' => 'App.create',
        ]) ?>
    </template>
    <template x-if="!error && rows.length > 0">
        <div class="<?= esc(table_wrapper_class()) ?> relative">
            <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center transition-all duration-200" x-cloak>
                <div class="flex items-center gap-2 rounded-lg bg-white/95 px-4 py-2 shadow-sm border border-gray-100">
                    <?= ui_icon('refresh-ccw', 'h-4 w-4 animate-spin text-brand-600') ?>
                    <span class="text-xs font-semibold text-gray-700"><?= esc(lang('App.loading_refreshing')) ?></span>
                </div>
            </div>
            <div class="<?= esc(table_scroll_class()) ?>">
            <table class="<?= esc(table_class()) ?>">
                <thead class="<?= esc(table_head_class()) ?>">
                    <tr>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('old_path')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('old_path')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Redirects.field_old_path')])) ?>">
                                <span><?= lang('Redirects.field_old_path') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('old_path')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('new_url')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('new_url')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Redirects.field_new_url')])) ?>">
                                <span><?= lang('Redirects.field_new_url') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('new_url')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('redirect_type')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('redirect_type')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Redirects.field_redirect_type')])) ?>">
                                <span><?= lang('Redirects.field_redirect_type') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('redirect_type')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_active')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_active')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Redirects.field_is_active')])) ?>">
                                <span><?= lang('Redirects.field_is_active') ?></span>
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
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.old_path ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.new_url ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10" x-text="String(row.redirect_type ?? '-')"></span>
                            </td>
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
                                    <button type="button" class="<?= esc(action_button_class('danger')) ?>"
                                        @click="$store.confirm.show(window.confirmDeleteMessage(String(row.old_path ?? row.new_url ?? row.note ?? '')), () => { const f = document.createElement('form'); f.method = 'post'; f.action = `<?= rtrim(route_to('admin.cms.redirects'), '/') ?>/${row.id}/delete`; const i=document.createElement('input');i.type='hidden';i.name='<?= csrf_token() ?>';i.value='<?= csrf_hash() ?>';f.appendChild(i); document.body.appendChild(f); f.submit(); })"
                                    ><?= lang('App.delete') ?></button>
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
