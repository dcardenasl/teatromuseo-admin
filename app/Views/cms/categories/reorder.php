<?= view('components/display/reorder', [
    'items'          => $items ?? [],
    'saveUrl'        => route_to('admin.cms.categories.save_order'),
    'displayKey'     => 'name',
    'subtitleKeys'   => ['collection_name', 'parent_label'],
    'subtitleLabels' => [
        'collection_name' => lang('Categories.field_collection_id'),
        'parent_label'    => lang('Categories.field_parent_id'),
    ],
    'dragLabel'      => 'Arrastrar',
    'noChangesLabel' => 'Sin cambios',
    'pendingLabel'   => 'Cambios pendientes',
    'backUrl'        => route_to('admin.cms.categories'),
    'title'          => $title ?? lang('App.reorder'),
]);
