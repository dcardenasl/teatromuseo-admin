<section class="bg-white border border-gray-200 rounded-xl shadow-sm p-6"
    x-data="Object.assign({
        deleteLabel(row) {
            const preferredKeys = ['name', 'title', 'label', 'code', 'slug', 'key'];
            for (const key of preferredKeys) {
                const value = row?.[key];
                if (typeof value === 'string' && value.trim() !== '') {
                    return value.trim();
                }
            }

            const fields = <?= esc(json_encode(array_values(array_map(static fn (array $field): string => (string) ($field['name'] ?? ''), $fields))), 'attr') ?>;
            for (const key of fields) {
                const value = row?.[key];
                if (typeof value === 'string' && value.trim() !== '') {
                    return value.trim();
                }
            }

            return String(row?.id ?? '');
        }
    }, remoteTable({
        apiUrl: '<?= route_to('admin.universal.data', $resource) ?>',
        pageUrl: '<?= route_to('admin.universal.index', $resource) ?>',
        mode: '<?= esc($resource) ?>',
        limitOptions: [10, 25, 50, 100]
    })" x-init="init()">
    
    <div class="flex items-center justify-between gap-4 border-b border-gray-200 pb-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <p class="text-xs text-gray-500">Zero-Code Metadata Driven Admin Dashboard</p>
        </div>
        <div>
            <a href="/admin/universal/<?= esc($resource) ?>/create" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                + Create <?= esc($title) ?>
            </a>
        </div>
    </div>

    <template x-if="loading">
        <?= view('components/display/loading_state', [
            'title'       => 'App.loading',
            'description' => 'App.loading_refreshing',
            'icon'        => 'database',
        ]) ?>
    </template>
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700" x-show="error" x-text="errorMessage"></div>

    <template x-if="!loading && !error && rows.length === 0">
        <?= view('components/display/empty_state', ['icon' => 'database']) ?>
    </template>
    
    <template x-if="!loading && !error && rows.length > 0">
        <div class="<?= esc(table_wrapper_class() ?? 'overflow-hidden rounded-lg border border-gray-200 shadow-sm') ?>">
            <div class="<?= esc(table_scroll_class() ?? 'overflow-x-auto') ?>">
            <table class="<?= esc(table_class() ?? 'min-w-full divide-y divide-gray-200') ?>">
                <thead class="<?= esc(table_head_class() ?? 'bg-gray-50') ?>">
                    <tr>
                        <?php foreach ($fields as $field): ?>
                            <th class="<?= esc(table_th_class() ?? 'px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider') ?>" :aria-sort="sortAria('<?= esc($field['name']) ?>')">
                                <button type="button" class="inline-flex items-center gap-1 hover:text-gray-700" @click="toggleSort('<?= esc($field['name']) ?>')">
                                    <span><?= esc($field['title'] ?? ucfirst($field['name'])) ?></span>
                                    <span aria-hidden="true" x-text="sortIcon('<?= esc($field['name']) ?>')"></span>
                                </button>
                            </th>
                        <?php endforeach; ?>
                        <th class="<?= esc(table_th_class() ?? 'px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider') ?>">Actions</th>
                    </tr>
                </thead>
                <tbody class="<?= esc(table_body_class() ?? 'bg-white divide-y divide-gray-200') ?>">
                    <template x-for="row in rows" :key="String(row.id ?? Math.random())">
                        <tr class="<?= esc(table_row_class() ?? 'hover:bg-gray-50') ?>">
                            <?php foreach ($fields as $field): ?>
                                <?php if (($field['type'] ?? '') === 'boolean'): ?>
                                    <td class="<?= esc(table_td_class() ?? 'px-6 py-4 whitespace-nowrap text-sm text-gray-600') ?>">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                              :class="row.<?= esc($field['name']) ?> ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                              x-text="row.<?= esc($field['name']) ?> ? 'Yes' : 'No'">
                                        </span>
                                    </td>
                                <?php else: ?>
                                    <td class="<?= esc(table_td_class() ?? 'px-6 py-4 whitespace-nowrap text-sm text-gray-600') ?>" x-text="String(row.<?= esc($field['name']) ?> ?? '-')"></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <td class="<?= esc(table_td_class() ?? 'px-6 py-4 whitespace-nowrap text-sm text-gray-600') ?>">
                                <div class="flex items-center gap-2">
                                    <a :href="'/admin/universal/<?= esc($resource) ?>/' + row.id + '/edit'" class="text-xs text-indigo-600 hover:text-indigo-900 font-semibold">Edit</a>
                                    <form :action="'/admin/universal/<?= esc($resource) ?>/' + row.id + '/delete'" method="post" @submit.prevent="$store.confirm.show(window.confirmDeleteMessage(deleteLabel(row)), () => $el.submit())">
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-900 font-semibold bg-transparent border-0 p-0 cursor-pointer">Delete</button>
                                    </form>
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
