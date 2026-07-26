<?= view('components/display/reorder', [
    'items' => $items ?? [],
    'saveUrl' => route_to('admin.cms.pages.save_order'),
    'displayKey' => 'page_type',
    'backUrl' => route_to('admin.cms.pages'),
    'title' => $title ?? lang('App.reorder'),
]);
