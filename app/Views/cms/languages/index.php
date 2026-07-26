<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.cms.languages.data') ?>',
        pageUrl: '<?= route_to('admin.cms.languages') ?>',
        defaultSort: '-created_at',
        routes: {
            showBase: '<?= route_to('admin.cms.languages') ?>',
            editBase: '<?= route_to('admin.cms.languages') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('CmsLanguages.languages_title'),
        'actionsView' => 'cms/languages/partials/toolbar_actions',
    ]) ?>
    

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.cms.languages'),
        'clearUrl'           => route_to('admin.cms.languages'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'cms/languages/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],

        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <template x-if="loading && rows.length === 0">
        <?= view('components/display/loading_state', [
            'title'       => 'CmsLanguages.languages_loading',
            'description' => 'App.loading_refreshing',
            'icon'        => 'globe',
        ]) ?>
    </template>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => route_to('admin.cms.languages.create'),
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
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('code')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('code')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('CmsLanguages.field_code')])) ?>">
                                <span><?= lang('CmsLanguages.field_code') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('code')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('name')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('name')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('CmsLanguages.field_name')])) ?>">
                                <span><?= lang('CmsLanguages.field_name') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('name')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('native_name')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('native_name')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('CmsLanguages.field_native_name')])) ?>">
                                <span><?= lang('CmsLanguages.field_native_name') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('native_name')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_default')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_default')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('CmsLanguages.field_is_default')])) ?>">
                                <span><?= lang('CmsLanguages.field_is_default') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('is_default')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_active')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_active')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('CmsLanguages.field_is_active')])) ?>">
                                <span><?= lang('CmsLanguages.field_is_active') ?></span>
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
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.code ?? '-')"></td>
                            <td class="<?= esc(table_td_class('primary')) ?>" x-text="String(row.name ?? '-')"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.native_name ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span
                                    :class="row.is_default ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800'"
                                    x-text="row.is_default ? '<?= esc(lang('App.yes'), 'js') ?>' : '<?= esc(lang('App.no'), 'js') ?>'"
                                ></span>
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span
                                    :class="row.is_active ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800'"
                                    x-text="row.is_active ? '<?= esc(lang('App.yes'), 'js') ?>' : '<?= esc(lang('App.no'), 'js') ?>'"
                                ></span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                    <a :href="editUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
                                    <button type="button" class="<?= esc(action_button_class('danger')) ?>"
                                        @click="$store.confirm.show(window.confirmDeleteMessage(String(row.name ?? row.code ?? row.native_name ?? '')), () => { const f = document.createElement('form'); f.method = 'post'; f.action = `<?= rtrim(route_to('admin.cms.languages'), '/') ?>/${row.id}/delete`; const i=document.createElement('input');i.type='hidden';i.name='<?= csrf_token() ?>';i.value='<?= csrf_hash() ?>';f.appendChild(i); document.body.appendChild(f); f.submit(); })"
                                    ><?= lang('App.delete') ?></button>
                                    <?php if (has_permission('cms.languages.write')): ?>
                                    <template x-if="!row.is_default">
                                        <form method="post" :action="'<?= site_url('admin/cms/languages') ?>/' + row.id + '/set-default'">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="<?= esc(action_button_class()) ?>"><?= esc(lang('CmsLanguages.languages_set_default')) ?></button>
                                        </form>
                                    </template>
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
