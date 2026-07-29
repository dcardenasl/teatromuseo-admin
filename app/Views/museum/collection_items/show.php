<?php $collectionItem = $collectionItem ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($collectionItem)): ?>
    <?php $itemId = (string) ($collectionItem['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.museum.collection_items'),
        'backLabel' => 'Museum.collection_items_title',
        'eyebrow' => 'Museum.collection_items_details',
        'title' => (string) ($collectionItem['name'] ?? $collectionItem['title'] ?? $collectionItem['id'] ?? lang('Museum.collection_items_details')),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('Museum.collection_items_details')) ?></h3>
        </div>
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_name',
                'value' => $collectionItem['name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_slug',
                'value' => $collectionItem['slug'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_category_id',
                'value' => ($categories[(string) ($collectionItem['category_id'] ?? '')] ?? ($collectionItem['category_id'] ?? '—'))
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_inventory_code',
                'value' => $collectionItem['inventory_code'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_status',
                'value' => $collectionItem['status'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_summary',
                'value' => $collectionItem['summary'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_curiosidad',
                'value' => $collectionItem['curiosidad'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_contenido',
                'value' => $collectionItem['contenido'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_origin',
                'value' => $collectionItem['origin'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_period',
                'value' => $collectionItem['period'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_creator',
                'value' => $collectionItem['creator'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_ubicacion',
                'value' => $collectionItem['ubicacion'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_materials',
                'value' => $collectionItem['materials'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_cover_file_id',
                'value' => $collectionItem['cover_file_id'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_gallery_file_ids',
                'value' => $collectionItem['gallery_file_ids'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_show_in_totem',
                'value' => view('components/table/boolean_cell', ['value' => $collectionItem['show_in_totem'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_internal_notes',
                'value' => $collectionItem['internal_notes'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_collection_number',
                'value' => $collectionItem['collection_number'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_collection_group',
                'value' => $collectionItem['collection_group'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_physical_description',
                'value' => $collectionItem['physical_description'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_dimensions',
                'value' => $collectionItem['dimensions'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_ingress_type',
                'value' => $collectionItem['ingress_type'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_donated_by',
                'value' => $collectionItem['donated_by'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_tags',
                'value' => $collectionItem['tags'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_links',
                'value' => $collectionItem['links'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_company_history',
                'value' => $collectionItem['company_history'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Museum.field_is_active',
                'value' => view('components/table/boolean_cell', ['value' => $collectionItem['is_active'] ?? false]),
                'isHtml' => true
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TableColumns.created_at',
                'value' => $collectionItem['created_at'] ?? '—',
            ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <a href="<?= route_to('admin.museum.collection_items.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>

    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <form method="post" action="<?= route_to('admin.museum.collection_items.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($collectionItem['name'] ?? $collectionItem['title'] ?? $collectionItem['id'] ?? null), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]),
    ]) ?>
<?php endif; ?>
