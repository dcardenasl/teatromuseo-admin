<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.cms.menus.data') ?>',
        pageUrl: '<?= route_to('admin.cms.menus') ?>',
        defaultSort: '-created_at',
        routes: {
            showBase: '<?= route_to('admin.cms.menus') ?>',
            editBase: '<?= route_to('admin.cms.menus') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Menus.menus_title'),
        'actionsView' => 'cms/menus/partials/toolbar_actions',
    ]) ?>
    

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.cms.menus'),
        'clearUrl'           => route_to('admin.cms.menus'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'cms/menus/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],

        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <template x-if="loading && rows.length === 0">
        <?= view('components/display/loading_state', [
            'title'       => 'Menus.menus_loading',
            'description' => 'App.loading_refreshing',
            'icon'        => 'menu',
        ]) ?>
    </template>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => route_to('admin.cms.menus.create'),
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
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('menu_key')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('menu_key')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Menus.field_menu_key')])) ?>">
                                <span><?= lang('Menus.field_menu_key') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('menu_key')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>">
                            <span><?= lang('App.translations') ?></span>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>">
                            <span><?= lang('Menus.field_location') ?></span>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_active')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_active')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Menus.field_is_active')])) ?>">
                                <span><?= lang('Menus.field_is_active') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('is_active')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>">
                            <span><?= lang('Menus.menus_items_count') ?></span>
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
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="font-medium text-gray-900 font-mono text-sm" x-text="String(row.menu_key ?? '-')"></span>
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <?= view('components/table/translation_badges', ['languages' => $languages ?? [], 'requiredFields' => ['name']]) ?>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>">
                                <template x-if="row.location">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-mono text-gray-700" x-text="row.location"></span>
                                </template>
                                <template x-if="!row.location">
                                    <span class="text-gray-400">—</span>
                                </template>
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span
                                    x-text="row.is_active ? '<?= esc(lang('Menus.field_is_active_on')) ?>' : '<?= esc(lang('Menus.field_is_active_off')) ?>'"
                                    :class="row.is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-200'"
                                    class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-semibold"
                                ></span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>">
                                <span class="inline-flex items-center gap-1">
                                    <?= ui_icon('list', 'h-3.5 w-3.5 text-gray-400') ?>
                                    <span x-text="String(row.items_count ?? '0')"></span>
                                </span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                    <a :href="editUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
                                    <button type="button" class="<?= esc(action_button_class('danger')) ?>"
                                        @click="$store.confirm.show(window.confirmDeleteMessage(String(row.menu_key ?? row.name ?? row.slug ?? '')), () => { const f = document.createElement('form'); f.method = 'post'; f.action = `<?= rtrim(route_to('admin.cms.menus'), '/') ?>/${row.id}/delete`; const i=document.createElement('input');i.type='hidden';i.name='<?= csrf_token() ?>';i.value='<?= csrf_hash() ?>';f.appendChild(i); document.body.appendChild(f); f.submit(); })"
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
