<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.cms.settings.data') ?>',
        pageUrl: '<?= route_to('admin.cms.settings') ?>',
        defaultSort: '-created_at',
        routes: {
            showBase: '<?= route_to('admin.cms.settings') ?>',
            editBase: '<?= route_to('admin.cms.settings') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Settings.settings_title'),
        'actionsView' => 'cms/settings/partials/toolbar_actions',
    ]) ?>
    

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.cms.settings'),
        'clearUrl'           => route_to('admin.cms.settings'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'cms/settings/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],

        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <template x-if="loading && rows.length === 0">
        <?= view('components/display/loading_state', [
            'title'       => 'Settings.settings_loading',
            'description' => 'App.loading_refreshing',
            'icon'        => 'settings',
        ]) ?>
    </template>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => route_to('admin.cms.settings.create'),
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
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('setting_key')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('setting_key')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Settings.field_setting_key')])) ?>">
                                <span><?= lang('Settings.field_setting_key') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('setting_key')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('setting_value')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('setting_value')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Settings.field_setting_value')])) ?>">
                                <span><?= lang('Settings.field_setting_value') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('setting_value')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('setting_type')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('setting_type')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Settings.field_setting_type')])) ?>">
                                <span><?= lang('Settings.field_setting_type') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('setting_type')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('setting_group')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('setting_group')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Settings.field_setting_group')])) ?>">
                                <span><?= lang('Settings.field_setting_group') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('setting_group')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_translatable')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_translatable')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Settings.field_is_translatable')])) ?>">
                                <span><?= lang('Settings.field_is_translatable') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('is_translatable')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>">
                            <span><?= lang('App.translations') ?></span>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('description')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('description')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Settings.field_description')])) ?>">
                                <span><?= lang('Settings.field_description') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('description')"></span>
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
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.setting_key ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.setting_value ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10" x-text="String(row.setting_type ?? '-')"></span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.setting_group ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span
                                    :class="row.is_translatable ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800'"
                                    x-text="row.is_translatable ? '<?= esc(lang('App.yes'), 'js') ?>' : '<?= esc(lang('App.no'), 'js') ?>'"
                                ></span>
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <template x-if="row.is_translatable">
                                    <?= view('components/table/translation_badges', ['languages' => $languages ?? [], 'requiredFields' => ['setting_value']]) ?>
                                </template>
                                <template x-if="!row.is_translatable">
                                    <span class="text-xs text-gray-400">—</span>
                                </template>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.description ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                    <a :href="editUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
                                    <button type="button" class="<?= esc(action_button_class('danger')) ?>"
                                        @click="$store.confirm.show(window.confirmDeleteMessage(String(row.setting_key ?? row.setting_group ?? row.description ?? '')), () => { const f = document.createElement('form'); f.method = 'post'; f.action = `<?= rtrim(route_to('admin.cms.settings'), '/') ?>/${row.id}/delete`; const i=document.createElement('input');i.type='hidden';i.name='<?= csrf_token() ?>';i.value='<?= csrf_hash() ?>';f.appendChild(i); document.body.appendChild(f); f.submit(); })"
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
