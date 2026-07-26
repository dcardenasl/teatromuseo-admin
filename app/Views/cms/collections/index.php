<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.cms.collections.data') ?>',
        pageUrl: '<?= route_to('admin.cms.collections') ?>',
        defaultSort: '-created_at',
        routes: {
            showBase: '<?= route_to('admin.cms.collections') ?>',
            editBase: '<?= route_to('admin.cms.collections') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Collections.collections_title'),
        'actionsView' => 'cms/collections/partials/toolbar_actions',
    ]) ?>
    

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.cms.collections'),
        'clearUrl'           => route_to('admin.cms.collections'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'cms/collections/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],

        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <template x-if="loading && rows.length === 0">
        <?= view('components/display/loading_state', [
            'title'       => 'Collections.collections_loading',
            'description' => 'App.loading_refreshing',
            'icon'        => 'layers-3',
        ]) ?>
    </template>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => route_to('admin.cms.collections.create'),
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
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('collection_key')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('collection_key')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Collections.field_collection_key')])) ?>">
                                <span><?= lang('Collections.field_collection_key') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('collection_key')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>">
                            <span><?= lang('App.translations') ?></span>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_active')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_active')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Collections.field_is_active')])) ?>">
                                <span><?= lang('Collections.field_is_active') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('is_active')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('requires_approval')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('requires_approval')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Collections.field_requires_approval')])) ?>">
                                <span><?= lang('Collections.field_requires_approval') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('requires_approval')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('enables_categories')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('enables_categories')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Collections.field_enables_categories')])) ?>">
                                <span><?= lang('Collections.field_enables_categories') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('enables_categories')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('enables_tags')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('enables_tags')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Collections.field_enables_tags')])) ?>">
                                <span><?= lang('Collections.field_enables_tags') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('enables_tags')"></span>
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
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.collection_key ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <?= view('components/table/translation_badges', ['languages' => $languages ?? [], 'requiredFields' => ['name']]) ?>
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
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center">
                                    <template x-if="row.requires_approval">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <template x-if="!row.requires_approval">
                                        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                </span>
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center">
                                    <template x-if="row.enables_categories">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <template x-if="!row.enables_categories">
                                        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                </span>
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center">
                                    <template x-if="row.enables_tags">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <template x-if="!row.enables_tags">
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
                                    <?php if (has_permission('cms.collections.write')): ?>
                                        <a :href="editUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
                                        <button type="button" class="<?= esc(action_button_class('danger')) ?>"
                                            @click="$store.confirm.show(window.confirmDeleteMessage(String(row.collection_key ?? row.name ?? row.slug ?? '')), () => { const f = document.createElement('form'); f.method = 'post'; f.action = `<?= rtrim(route_to('admin.cms.collections'), '/') ?>/${row.id}/delete`; const i=document.createElement('input');i.type='hidden';i.name='<?= csrf_token() ?>';i.value='<?= csrf_hash() ?>';f.appendChild(i); document.body.appendChild(f); f.submit(); })"
                                        ><?= lang('App.delete') ?></button>
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
