<?php /** @var array $stats */ ?>
<?php /** @var array $languages */ ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= esc(lang('Translations.audit_subtitle')) ?></p>
        </div>
    </div>

    <!-- Stats / Progress Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($stats as $statIndex => $stat): ?>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2 min-h-[32px]">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="inline-flex items-center justify-center font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs border border-blue-200 shrink-0">
                                <?= esc(strtoupper($stat['code'])) ?>
                            </span>
                            <span class="text-sm font-semibold text-gray-700 truncate"><?= esc($stat['name']) ?></span>
                        </div>
                        <span class="text-lg font-bold text-gray-900 shrink-0"><?= esc($stat['percentage']) ?>%</span>
                    </div>

                    <!-- Always rendered (default vs. positional label) so the progress bar below stays aligned across all cards. -->
                    <div class="flex">
                        <?php if ($stat['is_default']): ?>
                            <span class="inline-flex items-center rounded-full px-1.5 py-0.5 font-semibold bg-green-50 text-green-700 border border-green-200 text-[10px] leading-3 whitespace-nowrap">
                                <?= esc(lang('CmsLanguages.field_is_default')) ?>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center rounded-full px-1.5 py-0.5 font-semibold bg-gray-50 text-gray-600 border border-gray-200 text-[10px] leading-3 whitespace-nowrap">
                                <?= esc(lang('Translations.language_position', ['position' => $statIndex + 1])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Progress bar -->
                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-blue-600 h-full transition-all duration-500" style="width: <?= esc($stat['percentage']) ?>%; background-color: #2563eb;"></div>
                    </div>
                </div>

                <div class="flex justify-between text-xs text-gray-500 pt-1">
                    <span><?= esc(lang('Translations.translated') ?? 'Translated') ?>: <?= esc($stat['completed_elements']) ?> / <?= esc($stat['total_elements']) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Audit Issues Table Section -->
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
        x-data="{
            translationRoutes: {
                page: '<?= route_to('admin.cms.pages') ?>',
                menu: '<?= route_to('admin.cms.menus') ?>',
                setting: '<?= route_to('admin.cms.settings') ?>',
                category: '<?= route_to('admin.cms.categories') ?>',
                tag: '<?= route_to('admin.cms.tags') ?>',
                collection: '<?= route_to('admin.cms.collections') ?>',
                entry: '<?= route_to('admin.cms.entries') ?>',
                form: '<?= route_to('admin.cms.forms') ?>'
            },
            ...remoteTable({
                apiUrl: '<?= route_to('admin.cms.translations.audit.data') ?>',
                pageUrl: '<?= route_to('admin.cms.translations.audit') ?>',
                limitOptions: ['10', '25', '50', '100']
            }),
            dict: <?= esc(json_encode([
                'resources' => [
                    'page' => lang('Translations.resource_page'),
                    'menu' => lang('Translations.resource_menu'),
                    'menu_item' => lang('Translations.resource_menu_item'),
                    'setting' => lang('Translations.resource_setting'),
                    'category' => lang('Translations.resource_category'),
                    'collection' => lang('Translations.resource_collection'),
                    'tag' => lang('Translations.resource_tag'),
                    'entry' => lang('Translations.resource_entry'),
                    'form' => lang('Translations.resource_form'),
                    'form_field' => lang('Translations.resource_form_field'),
                    'block_instance' => lang('Translations.resource_block_instance'),
                ],
                'statuses' => [
                    'missing' => lang('Translations.status_missing'),
                    'incomplete' => lang('Translations.status_incomplete'),
                    'mismatch' => lang('Translations.status_mismatch'),
                    'outdated' => lang('Translations.status_outdated'),
                ],
                'details' => [
                    'missing_all' => lang('Translations.detail_missing_all'),
                    'missing_required_fields' => lang('Translations.detail_missing_required_fields'),
                    'inconsistent_fields' => lang('Translations.detail_inconsistent_fields'),
                ],
                'fields' => [
                    'title' => lang('Translations.field_title'),
                    'slug' => lang('Translations.field_slug'),
                    'label' => lang('Translations.field_label'),
                    'setting_value' => lang('Translations.field_setting_value'),
                    'name' => lang('Translations.field_name'),
                    'submit_label' => lang('Translations.field_submit_label'),
                    'block_data' => lang('Translations.field_block_data'),
                    'custom_url' => lang('Translations.field_custom_url'),
                    'excerpt' => lang('Translations.field_excerpt'),
                    'meta_title' => lang('Translations.field_meta_title'),
                    'meta_description' => lang('Translations.field_meta_description'),
                    'og_image_file_id' => lang('Translations.field_og_image_file_id'),
                    'og_type' => lang('Translations.field_og_type'),
                    'canonical_url' => lang('Translations.field_canonical_url'),
                    'robots' => lang('Translations.field_robots'),
                    'schema_data' => lang('Translations.field_schema_data'),
                    'description' => lang('Translations.field_description'),
                    'listing_title' => lang('Translations.field_listing_title'),
                    'listing_intro' => lang('Translations.field_listing_intro'),
                    'default_meta_title' => lang('Translations.field_default_meta_title'),
                    'default_meta_description' => lang('Translations.field_default_meta_description'),
                ]
            ])) ?>,
            translateResource(res) {
                return this.dict.resources[res] || res;
            },
            translateStatus(status) {
                return this.dict.statuses[status] || status;
            },
            translateDetail(row) {
                if (row.status === 'missing') {
                    return this.dict.details.missing_all;
                }
                const parts = row.detail.split(': ');
                if (parts.length > 1) {
                    const fields = parts.slice(1).join(': ').split(', ').map(f => this.dict.fields[f.trim()] || f.trim()).join(', ');
                    if (row.status === 'mismatch') {
                        return this.dict.details.inconsistent_fields.replace('{fields}', fields);
                    }
                    return this.dict.details.missing_required_fields.replace('{fields}', fields);
                }
                return row.detail;
            },
            editUrl(row) {
                // A relative path+query on purpose (not buildUrl(), which always
                // resolves to an absolute http(s) URL): BaseWebController's
                // return_to guard only accepts a same-app absolute *path*.
                const q = new URLSearchParams(this.query).toString();
                const returnTo = q !== '' ? `${this.pageUrl}?${q}` : this.pageUrl;
                return window.resolveCmsTranslationEditUrl(this.translationRoutes, row, returnTo);
            },
            nextPendingUrl() {
                const row = this.rows.find((candidate) => ['missing', 'incomplete', 'mismatch'].includes(candidate.status));
                return row ? this.editUrl(row) : '#';
            }
        }" x-init="init()">
        
        <div class="pb-5 border-b border-gray-200 space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-gray-900"><?= esc(lang('Translations.missing_incomplete')) ?></h2>
                    <p class="text-xs text-gray-500 mt-0.5"><?= esc(lang('Translations.missing_incomplete_desc')) ?></p>
                </div>
                <a :href="nextPendingUrl()" :aria-disabled="rows.length === 0" :class="rows.length === 0 ? 'pointer-events-none opacity-50' : ''" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">
                    <?= ui_icon('arrow-right', 'h-3.5 w-3.5') ?>
                    <?= esc(lang('Translations.action_next_pending')) ?>
                </a>
            </div>

            <!-- Filters -->
            <form data-table-filter-form="1" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
                <input type="search" name="search" placeholder="<?= esc(lang('Translations.search_issues')) ?>" class="w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 lg:col-span-4" data-table-debounce="300">
                <select name="language_id" class="w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 lg:col-span-2">
                    <option value=""><?= esc(lang('Translations.all_active_languages')) ?></option>
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?= esc($lang['id']) ?>"><?= esc($lang['native_name'] ?? $lang['name']) ?> (<?= esc(strtoupper($lang['code'])) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <select name="resource" class="w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 lg:col-span-2">
                    <option value=""><?= esc(lang('Translations.all_resources')) ?></option>
                    <?php foreach (['page', 'menu', 'menu_item', 'setting', 'category', 'tag', 'collection', 'entry', 'form', 'form_field', 'block_instance'] as $resource): ?>
                        <option value="<?= esc($resource) ?>"><?= esc(lang('Translations.resource_' . $resource)) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 lg:col-span-2">
                    <option value=""><?= esc(lang('Translations.all_statuses')) ?></option>
                    <?php foreach (['missing', 'incomplete', 'mismatch'] as $status): ?>
                        <option value="<?= esc($status) ?>"><?= esc(lang('Translations.status_' . $status)) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="<?= esc(filter_submit_button_class(true)) ?> lg:col-span-2">
                    <?= ui_icon('search', 'h-3.5 w-3.5') ?>
                    <?= esc(lang('App.search')) ?>
                </button>
            </form>
        </div>

        <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600 text-center" x-show="loading">
            <?= esc(lang('Translations.loading_report')) ?>
        </div>
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

        <template x-if="!loading && !error && rows.length === 0">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900"><?= esc(lang('Translations.all_complete')) ?></h3>
                <p class="mt-1 text-xs text-gray-500"><?= esc(lang('Translations.no_missing_elements')) ?></p>
            </div>
        </template>

        <template x-if="!loading && !error && rows.length > 0">
            <div class="<?= esc(table_wrapper_class()) ?>">
                <div class="<?= esc(table_scroll_class()) ?>">
                    <table class="<?= esc(table_class()) ?>">
                        <thead class="<?= esc(table_head_class()) ?>">
                            <tr>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_type')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_item_name')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_language')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_status')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_details')) ?></th>
                                <th class="<?= esc(table_th_class()) ?>"><?= esc(lang('Translations.field_action')) ?></th>
                            </tr>
                        </thead>
                        <tbody class="<?= esc(table_body_class()) ?>">
                            <template x-for="row in rows" :key="String(row.resource + '-' + row.resource_id + '-' + row.language_id)">
                                <tr class="<?= esc(table_row_class()) ?>">
                                    <td class="<?= esc(table_td_class('muted')) ?>">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 capitalize" x-text="translateResource(row.resource)"></span>
                                    </td>
                                    <td class="<?= esc(table_td_class('primary')) ?>" x-text="row.reference_name"></td>
                                    <td class="<?= esc(table_td_class()) ?>">
                                        <span class="font-bold text-xs uppercase bg-blue-50 text-blue-700 px-2 py-0.5 rounded border border-blue-200" x-text="row.language_code"></span>
                                    </td>
                                    <td class="<?= esc(table_td_class()) ?>">
                                        <span :class="row.status === 'missing' ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800' : row.status === 'mismatch' ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-orange-100 text-orange-800' : row.status === 'outdated' ? 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-orange-100 text-orange-800' : 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800'" x-text="translateStatus(row.status)"></span>
                                    </td>
                                    <td class="<?= esc(table_td_class('muted')) ?>" x-text="translateDetail(row)"></td>
                                    <td class="<?= esc(table_td_class()) ?>">
                                        <a :href="editUrl(row)" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-900">
                                            <?= esc(lang('Translations.action_translate')) ?>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </section>
</div>
