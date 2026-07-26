<?= view('components/display/reorder', [
    'items' => $items ?? [],
    'saveUrl' => route_to('admin.cms.languages.save_order'),
    'displayKey' => 'code',
    'backUrl' => route_to('admin.cms.languages'),
    'title' => $title ?? lang('App.reorder'),
]);
