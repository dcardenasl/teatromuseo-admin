<div class="mb-4">
    <a href="<?= route_to('admin.museum.techniques') ?>" class="text-sm text-brand-600 hover:text-brand-700">&larr; <?= esc(lang('App.back')) ?></a>
</div>

<?= view('components/display/reorder', [
    'items' => $items ?? [],
    'saveUrl' => route_to('admin.museum.techniques.save_order'),
    'displayKey' => 'name',
    'backUrl' => route_to('admin.museum.techniques'),
    'title' => $title ?? lang('App.reorder'),
]) ?>
