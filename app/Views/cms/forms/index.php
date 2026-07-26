<?php /** @var array $limitOptions */ ?>

<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
    x-data="{
        ...remoteTable({
            apiUrl: '<?= route_to('admin.cms.forms.data') ?>',
            pageUrl: '<?= route_to('admin.cms.forms') ?>',
            defaultSort: '-created_at',
            routes: {
                showBase: '<?= route_to('admin.cms.forms') ?>',
                editBase: '<?= route_to('admin.cms.forms') ?>'
            },
            limitOptions: <?= esc(json_encode(array_map('strval', $limitOptions ?? [10, 25, 50, 100]))) ?>
        }),
        isTruthy(value) {
            return value === true || value === 1 || value === '1';
        },
        formName(row) {
            if (row.name) {
                return String(row.name);
            }

            if (Array.isArray(row.translations) && row.translations.length > 0) {
                const translation = row.translations.find((item) => item && item.name) || row.translations.find((item) => item && item.slug) || row.translations.find((item) => item);
                return String(translation?.name || row.form_key || '-');
            }

            return String(row.form_key || '-');
        },
        fieldCount(row) {
            if (Array.isArray(row.fields)) {
                return row.fields.length;
            }

            return Number(row.fields_count ?? row.field_count ?? 0);
        },
        deleteAction(row) {
            return `<?= rtrim(route_to('admin.cms.forms'), '/') ?>/${row.id}/delete`;
        }
    }"
    x-init="init()">

    <?= view('layouts/partials/table_toolbar', [
        'title'       => lang('Forms.title'),
        'actionsView' => 'cms/forms/partials/toolbar_actions',
    ]) ?>

    <?= view('layouts/partials/filter_panel', [
        'actionUrl'          => route_to('admin.cms.forms'),
        'clearUrl'           => route_to('admin.cms.forms'),
        'hasFilters'         => has_active_filters(request()->getGet(), ['limit' => '25']),
        'reactiveHasFilters' => true,
        'filterDefaults'     => ['limit' => '25'],
        'fieldsView'         => 'cms/forms/partials/filters',
        'fieldsData'         => [
            'limitOptions' => $limitOptions ?? [10, 25, 50, 100],
        ],
        'submitLabel' => lang('App.search'),
    ]) ?>

    <template x-if="loading && rows.length === 0">
        <?= view('components/display/loading_state', [
            'title'       => 'Forms.loading',
            'description' => 'App.loading_refreshing',
            'icon'        => 'list',
        ]) ?>
    </template>

    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', [
            'title'       => 'App.no_results',
            'description' => 'Forms.empty',
            'icon'        => 'list',
            'actionUrl'   => route_to('admin.cms.forms.create'),
            'actionLabel' => 'Forms.btn_create',
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
                            <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('form_key')">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('form_key')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Forms.col_key')])) ?>">
                                    <span><?= lang('Forms.col_key') ?></span>
                                    <span aria-hidden="true" x-text="sortIcon('form_key')"></span>
                                </button>
                            </th>
                            <th class="<?= esc(table_th_class()) ?>"><?= lang('Forms.col_name') ?></th>
                            <th class="<?= esc(table_th_class()) ?>"><?= lang('App.translations') ?></th>
                            <th class="<?= esc(table_th_class()) ?>"><?= lang('Forms.col_captcha') ?></th>
                            <th class="<?= esc(table_th_class()) ?> text-center"><?= lang('Forms.col_fields') ?></th>
                            <th class="<?= esc(table_th_class()) ?>" :aria-sort="sortAria('is_active')">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('is_active')" aria-label="<?= esc(lang('TableA11y.sort_by', [lang('Forms.col_active')])) ?>">
                                    <span><?= lang('Forms.col_active') ?></span>
                                    <span aria-hidden="true" x-text="sortIcon('is_active')"></span>
                                </button>
                            </th>
                            <th class="<?= esc(table_th_class()) ?>"><?= lang('Forms.col_actions') ?></th>
                        </tr>
                    </thead>
                    <tbody class="<?= esc(table_body_class()) ?>">
                        <template x-for="row in rows" :key="String(row.id ?? row.form_key ?? Math.random())">
                            <tr class="<?= esc(table_row_class()) ?>">
                                <td class="<?= esc(table_td_class()) ?>">
                                    <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700" x-text="String(row.form_key || '-')"></code>
                                </td>
                                <td class="<?= esc(table_td_class('primary')) ?>" x-text="formName(row)"></td>
                                <td class="<?= esc(table_td_class()) ?>">
                                    <?= view('components/table/translation_badges', ['languages' => $languages ?? [], 'requiredFields' => ['name']]) ?>
                                </td>
                                <td class="<?= esc(table_td_class()) ?>">
                                    <span
                                        :class="isTruthy(row.has_captcha) ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600'"
                                        x-text="isTruthy(row.has_captcha) ? '<?= esc(lang('Forms.captcha_on'), 'js') ?>' : '<?= esc(lang('Forms.captcha_off'), 'js') ?>'"
                                    ></span>
                                </td>
                                <td class="<?= esc(table_td_class('muted')) ?> text-center" x-text="fieldCount(row)"></td>
                                <td class="<?= esc(table_td_class()) ?>">
                                    <span
                                        :class="isTruthy(row.is_active) ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800'"
                                        x-text="isTruthy(row.is_active) ? '<?= esc(lang('App.yes'), 'js') ?>' : '<?= esc(lang('App.no'), 'js') ?>'"
                                    ></span>
                                </td>
                                <td class="<?= esc(table_td_class()) ?>">
                                    <div class="flex items-center gap-2">
                                        <a :href="showUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.view') ?></a>
                                        <?php if (has_permission('cms.forms.write')): ?>
                                            <a :href="editUrl(row.id)" class="<?= esc(action_button_class()) ?>"><?= lang('App.edit') ?></a>
                                        <?php endif; ?>
                                        <?php if (has_permission('cms.forms.admin')): ?>
                                            <button type="button" class="<?= esc(action_button_class('danger')) ?>"
                                                @click="$store.confirm.show(window.confirmDeleteMessage(formName(row)), () => { const f = document.createElement('form'); f.method = 'post'; f.action = deleteAction(row); const i = document.createElement('input'); i.type = 'hidden'; i.name = '<?= csrf_token() ?>'; i.value = '<?= csrf_hash() ?>'; f.appendChild(i); document.body.appendChild(f); f.submit(); })"
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
