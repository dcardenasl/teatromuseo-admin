<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.museum.collection_items'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Museum.collection_items_title',
    'title' => 'Museum.collection_items_create',
]) ?>

<form method="post" action="<?= route_to('admin.museum.collection_items.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Museum.collection_items_create')) ?></h3>
            <div class="mt-4 space-y-4">

        <?= view('components/form/text', [
            'name' => 'name',
            'label' => 'Museum.field_name',
            'required' => true,
            'value' => $item['name'] ?? '',
            'placeholder' => 'Museum.field_name_placeholder',
            'help' => 'Museum.field_name_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/relation', [
            'name' => 'category_id',
            'label' => 'Museum.field_category_id',
            'required' => true,
            'options' => $categories ?? [],
            'placeholder' => 'Museum.field_category_id_placeholder',
            'help' => 'Museum.field_category_id_help',
            'value' => $item['category_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'inventory_code',
            'label' => 'Museum.field_inventory_code',
            'required' => true,
            'value' => $item['inventory_code'] ?? '',
            'placeholder' => 'Museum.field_inventory_code_placeholder',
            'help' => 'Museum.field_inventory_code_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'status',
            'label' => 'Museum.field_status',
            'required' => true,
            'value' => $item['status'] ?? '',
            'placeholder' => 'Museum.field_status_placeholder',
            'help' => 'Museum.field_status_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'summary',
            'label' => 'Museum.field_summary',
            'required' => false,
            'value' => $item['summary'] ?? '',
            'placeholder' => 'Museum.field_summary_placeholder',
            'help' => 'Museum.field_summary_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'curiosidad',
            'label' => 'Museum.field_curiosidad',
            'required' => false,
            'value' => $item['curiosidad'] ?? '',
            'placeholder' => 'Museum.field_curiosidad_placeholder',
            'help' => 'Museum.field_curiosidad_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'contenido',
            'label' => 'Museum.field_contenido',
            'required' => false,
            'value' => $item['contenido'] ?? '',
            'placeholder' => 'Museum.field_contenido_placeholder',
            'help' => 'Museum.field_contenido_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'origin',
            'label' => 'Museum.field_origin',
            'required' => false,
            'value' => $item['origin'] ?? '',
            'placeholder' => 'Museum.field_origin_placeholder',
            'help' => 'Museum.field_origin_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'period',
            'label' => 'Museum.field_period',
            'required' => false,
            'value' => $item['period'] ?? '',
            'placeholder' => 'Museum.field_period_placeholder',
            'help' => 'Museum.field_period_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'creator',
            'label' => 'Museum.field_creator',
            'required' => false,
            'value' => $item['creator'] ?? '',
            'placeholder' => 'Museum.field_creator_placeholder',
            'help' => 'Museum.field_creator_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'ubicacion',
            'label' => 'Museum.field_ubicacion',
            'required' => false,
            'value' => $item['ubicacion'] ?? '',
            'placeholder' => 'Museum.field_ubicacion_placeholder',
            'help' => 'Museum.field_ubicacion_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'materials',
            'label' => 'Museum.field_materials',
            'required' => false,
            'value' => $item['materials'] ?? '',
            'placeholder' => 'Museum.field_materials_placeholder',
            'help' => 'Museum.field_materials_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'cover_file_id',
            'label' => 'Museum.field_cover_file_id',
            'required' => false,
            'value' => $item['cover_file_id'] ?? '',
            'placeholder' => 'Museum.field_cover_file_id_placeholder',
            'help' => 'Museum.field_cover_file_id_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'gallery_file_ids',
            'label' => 'Museum.field_gallery_file_ids',
            'required' => false,
            'value' => $item['gallery_file_ids'] ?? '',
            'placeholder' => 'Museum.field_gallery_file_ids_placeholder',
            'help' => 'Museum.field_gallery_file_ids_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'show_in_totem',
            'label' => 'Museum.field_show_in_totem',
            'value' => $item['show_in_totem'] ?? false,
            'on_label' => 'Museum.field_show_in_totem_on',
            'off_label' => 'Museum.field_show_in_totem_off',
            'help' => 'Museum.field_show_in_totem_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'internal_notes',
            'label' => 'Museum.field_internal_notes',
            'required' => false,
            'value' => $item['internal_notes'] ?? '',
            'placeholder' => 'Museum.field_internal_notes_placeholder',
            'help' => 'Museum.field_internal_notes_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'collection_number',
            'label' => 'Museum.field_collection_number',
            'required' => false,
            'value' => $item['collection_number'] ?? '',
            'placeholder' => 'Museum.field_collection_number_placeholder',
            'help' => 'Museum.field_collection_number_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'collection_group',
            'label' => 'Museum.field_collection_group',
            'required' => false,
            'value' => $item['collection_group'] ?? '',
            'placeholder' => 'Museum.field_collection_group_placeholder',
            'help' => 'Museum.field_collection_group_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'physical_description',
            'label' => 'Museum.field_physical_description',
            'required' => false,
            'value' => $item['physical_description'] ?? '',
            'placeholder' => 'Museum.field_physical_description_placeholder',
            'help' => 'Museum.field_physical_description_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'dimensions',
            'label' => 'Museum.field_dimensions',
            'required' => false,
            'value' => $item['dimensions'] ?? '',
            'placeholder' => 'Museum.field_dimensions_placeholder',
            'help' => 'Museum.field_dimensions_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'ingress_type',
            'label' => 'Museum.field_ingress_type',
            'required' => false,
            'value' => $item['ingress_type'] ?? '',
            'placeholder' => 'Museum.field_ingress_type_placeholder',
            'help' => 'Museum.field_ingress_type_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'donated_by',
            'label' => 'Museum.field_donated_by',
            'required' => false,
            'value' => $item['donated_by'] ?? '',
            'placeholder' => 'Museum.field_donated_by_placeholder',
            'help' => 'Museum.field_donated_by_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'tags',
            'label' => 'Museum.field_tags',
            'required' => false,
            'value' => $item['tags'] ?? '',
            'placeholder' => 'Museum.field_tags_placeholder',
            'help' => 'Museum.field_tags_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'links',
            'label' => 'Museum.field_links',
            'required' => false,
            'value' => $item['links'] ?? '',
            'placeholder' => 'Museum.field_links_placeholder',
            'help' => 'Museum.field_links_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'company_history',
            'label' => 'Museum.field_company_history',
            'required' => false,
            'value' => $item['company_history'] ?? '',
            'placeholder' => 'Museum.field_company_history_placeholder',
            'help' => 'Museum.field_company_history_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Museum.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'Museum.field_is_active_on',
            'off_label' => 'Museum.field_is_active_off',
            'help' => 'Museum.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.museum.collection_items'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
