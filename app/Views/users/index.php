<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="remoteTable({
        apiUrl: '<?= route_to('admin.users.data') ?>',
        pageUrl: '<?= route_to('admin.users') ?>',
        mode: 'users',
        defaultSort: '-created_at',
        routes: {
            showBase: '<?= route_to('admin.users') ?>',
            editBase: '<?= route_to('admin.users') ?>'
        },
        limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
    })">
    <?= view('layouts/partials/table_toolbar', [
        'title' => lang('Users.title'),
        'actionsView' => 'users/partials/toolbar_actions',
        'showViewToggle' => true,
        'showDensityToggle' => true,
    ]) ?>

    <?= view('layouts/partials/filter_panel', [
        'actionUrl' => route_to('admin.users'),
        'clearUrl' => route_to('admin.users'),
        'hasFilters' => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults' => ['limit' => '25'],
        'fieldsView' => 'users/partials/filters',
        'fieldsData' => [
            'statusOptions' => $statusOptions ?? [],
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],
        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <template x-if="loading && rows.length === 0">
        <?= view('components/display/loading_state', [
            'title'       => 'Users.loading',
            'description' => 'App.loading_refreshing',
            'icon'        => 'users',
        ]) ?>
    </template>
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'icon'        => 'users',
            'actionUrl'   => route_to('admin.users.create'),
            'actionLabel' => 'App.create',
        ]) ?>
    </template>
    <template x-if="!error && rows.length > 0 && viewMode === 'table'">
        <div class="<?= esc(table_wrapper_class()) ?> relative">
            <div x-show="loading" class="absolute inset-0 bg-white/60 backdrop-blur-[1px] z-10 flex items-center justify-center transition-all duration-200" x-cloak>
                <div class="flex items-center gap-2 rounded-lg bg-white/95 px-4 py-2 shadow-sm border border-gray-100">
                    <?= ui_icon('refresh-ccw', 'h-4 w-4 animate-spin text-brand-600') ?>
                    <span class="text-xs font-semibold text-gray-700"><?= esc(lang('App.loading_refreshing')) ?></span>
                </div>
            </div>
            <div class="<?= esc(table_scroll_class()) ?>">
            <table class="<?= esc(table_class()) ?>" :class="'density-' + density">
                <thead class="<?= esc(table_head_class()) ?>">
                    <tr>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('first_name')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('first_name')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TableColumns.name')])) ?>">
                                <span><?= lang('TableColumns.name') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('first_name')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('email')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('email')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TableColumns.email')])) ?>">
                                <span><?= lang('TableColumns.email') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('email')"></span>
                            </button>
                        </th>
                        <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('status')">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('status')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('TableColumns.status')])) ?>">
                                <span><?= lang('TableColumns.status') ?></span>
                                <span aria-hidden="true" x-text="sortIcon('status')"></span>
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
                            <td class="<?= esc(table_td_class('primary')) ?>" x-text="fullName(row)"></td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="String(row.email ?? '-')"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs" :class="statusBadgeClass(row.status)" x-text="statusLabel(row.status)"></span>
                            </td>
                            <td class="<?= esc(table_td_class('muted')) ?>" x-text="formatDate(row.created_at)"></td>
                            <td class="<?= esc(table_td_class()) ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="userShowUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('Users.view') ?></a>
                                    <a :href="userEditUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            </div>
        </div>
    </template>

    <template x-if="!error && rows.length > 0 && viewMode === 'grid'">
        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <template x-for="row in rows" :key="String(row.id ?? Math.random())">
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <h4 class="min-w-0 truncate text-base font-semibold text-gray-900" x-text="fullName(row)"></h4>
                        <span class="inline-flex shrink-0 rounded-full px-2 py-1 text-xs"
                              :class="statusBadgeClass(row.status)"
                              x-text="statusLabel(row.status)"></span>
                    </div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= esc(lang('Users.email')) ?></dt>
                            <dd class="mt-1 break-all text-gray-900" x-text="String(row.email ?? '-')"></dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= esc(lang('Users.roles')) ?></dt>
                            <dd class="mt-1 text-gray-900"
                                x-text="Array.isArray(row.roles) && row.roles.length > 0 ? row.roles.map(role => String(role.name ?? role.code ?? '-')).join(', ') : '-'">
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500"><?= esc(lang('Users.created_at')) ?></dt>
                            <dd class="mt-1 text-gray-900" x-text="formatDate(row.created_at)"></dd>
                        </div>
                    </dl>
                    <div class="mt-5 flex items-center gap-2 border-t border-gray-100 pt-4">
                        <a :href="userShowUrl(row.id)" class="<?= esc(action_button_class()) ?> flex-1 justify-center"><?= esc(lang('Users.view')) ?></a>
                        <a :href="userEditUrl(row.id)" class="<?= esc(action_button_class('primary')) ?> flex-1 justify-center"><?= esc(lang('App.edit')) ?></a>
                    </div>
                </article>
            </template>
        </div>
    </template>

    <?= view('layouts/partials/remote_pagination') ?>
</section>
