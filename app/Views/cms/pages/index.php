<?php
/** @var array $limitOptions */

$languageBadges = [];
foreach ($languages ?? [] as $key => $language) {
    $language = is_array($language) ? $language : ['id' => $key, 'name' => (string) $language];
    $langId   = isset($language['id']) && is_numeric($language['id']) ? (int) $language['id'] : (is_numeric($key) ? (int) $key : 0);

    if ($langId <= 0) {
        continue;
    }

    $langCode = (string) ($language['code'] ?? (is_string($key) && ! is_numeric($key) ? $key : $langId));

    $languageBadges[] = [
        'id'   => $langId,
        'name' => (string) ($language['name'] ?? $language['label'] ?? $langCode),
        'code' => $langCode,
    ];
}
?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.cms.pages.data') ?>',
        pageUrl: '<?= route_to('admin.cms.pages') ?>',
        defaultSort: '-created_at',
        routes: {
            showBase: '<?= route_to('admin.cms.pages') ?>',
            editBase: '<?= route_to('admin.cms.pages') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })" x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Pages.pages_title'),
        'actionsView' => 'cms/pages/partials/toolbar_actions',
    ]) ?>
    

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.cms.pages'),
        'clearUrl'           => route_to('admin.cms.pages'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'cms/pages/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],
            'pages' => $pages ?? [],
        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <template x-if="loading && rows.length === 0">
        <?= view('components/display/loading_state', [
            'title'       => 'Pages.pages_loading',
            'description' => 'App.loading_refreshing',
            'icon'        => 'file-text',
        ]) ?>
    </template>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title' => 'App.no_results',
            'description' => 'App.no_results_desc',
            'actionUrl' => route_to('admin.cms.pages.create'),
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
                        <th class="<?= esc(table_th_class()) ?>">
                            <span><?= lang('Pages.translation_title_label') ?></span>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>">
                            <span>Translations</span>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('page_type')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('page_type')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Pages.field_page_type')])) ?>">
                                <span><?= lang('Pages.field_page_type') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('page_type')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('status')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('status')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Pages.field_status')])) ?>">
                                <span><?= lang('Pages.field_status') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('status')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('parent_id')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('parent_id')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Pages.field_parent_id')])) ?>">
                                <span><?= lang('Pages.field_parent_id') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('parent_id')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_in_sitemap')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_in_sitemap')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Pages.field_is_in_sitemap')])) ?>">
                                <span><?= lang('Pages.field_is_in_sitemap') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('is_in_sitemap')"></span>
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
                            <td class="<?= esc(table_td_class()) ?> font-semibold text-gray-900" x-text="row.translations && row.translations.length > 0 ? (row.translations.find(t => t.title) || row.translations.find(t => t.slug) || row.translations.find(t => t)).title : (row.title || row.name || row.slug || '-')">
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <?= view('components/table/translation_badges', ['languages' => $languages ?? [], 'requiredFields' => ['title', 'slug']]) ?>
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10"
                                    x-text="row.page_type === 'home' ? '<?= esc(lang('Pages.page_type_home'), 'js') ?>' : row.page_type === 'generic' ? '<?= esc(lang('Pages.page_type_generic'), 'js') ?>' : row.page_type === 'contact' ? '<?= esc(lang('Pages.page_type_contact'), 'js') ?>' : row.page_type === 'privacy' ? '<?= esc(lang('Pages.page_type_privacy'), 'js') ?>' : row.page_type === 'terms' ? '<?= esc(lang('Pages.page_type_terms'), 'js') ?>' : row.page_type === 'about' ? '<?= esc(lang('Pages.page_type_about'), 'js') ?>' : row.page_type === 'history' ? '<?= esc(lang('Pages.page_type_history'), 'js') ?>' : row.page_type === 'events' ? '<?= esc(lang('Pages.page_type_events'), 'js') ?>' : row.page_type === 'maintenance' ? '<?= esc(lang('Pages.page_type_maintenance'), 'js') ?>' : String(row.page_type ?? '-')"
                                ></span>
                            </td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span
                                    :class="row.status === 'published' ? 'inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800' : row.status === 'archived' ? 'inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-800' : 'inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600'"
                                    x-text="row.status === 'published' ? '<?= esc(lang('Pages.status_published'), 'js') ?>' : row.status === 'archived' ? '<?= esc(lang('Pages.status_archived'), 'js') ?>' : '<?= esc(lang('Pages.status_draft'), 'js') ?>'"
                                ></span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.parent_id ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex items-center">
                                    <span
                                        :class="row.is_in_sitemap ? 'text-green-700' : 'text-red-600'"
                                        x-text="row.is_in_sitemap ? '<?= esc(lang('Pages.field_is_in_sitemap_on'), 'js') ?>' : '<?= esc(lang('Pages.field_is_in_sitemap_off'), 'js') ?>'"
                                    ></span>
                                </span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                    <a :href="editUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
                                    <button type="button" class="<?= esc(action_button_class('danger')) ?>"
                                        @click="$store.confirm.show(window.confirmDeleteMessage(String(row.translations && row.translations.length > 0 ? (row.translations.find(t => t.title) || row.translations.find(t => t.slug) || row.translations.find(t => t)).title : (row.title || row.name || row.slug || ''))), () => { const f = document.createElement('form'); f.method = 'post'; f.action = `<?= rtrim(route_to('admin.cms.pages'), '/') ?>/${row.id}/delete`; const i=document.createElement('input');i.type='hidden';i.name='<?= csrf_token() ?>';i.value='<?= csrf_hash() ?>';f.appendChild(i); document.body.appendChild(f); f.submit(); })"
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
