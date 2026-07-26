<?php if (! empty($collections)): ?>
    <div class="mb-6 bg-white border border-gray-200 rounded-xl shadow-sm p-5 max-w-4xl">
        <form method="get" action="<?= current_url() ?>" id="collection-filter-form">
            <label for="collection_id" class="block text-sm font-semibold text-gray-700 mb-2"><?= esc(lang('Entries.field_collection_id') ?? 'Colección') ?></label>
            <select name="collection_id" id="collection_id" 
                    class="<?= input_class('collection_id') ?> max-w-xs" 
                    onchange="document.getElementById('collection-filter-form').submit()">
                <?php foreach ($collections as $id => $label): ?>
                    <option value="<?= esc($id, 'attr') ?>" <?= (string)$selectedCollectionId === (string)$id ? 'selected' : '' ?>>
                        <?= esc($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
<?php endif; ?>

<?= view('components/display/reorder', [
    'items'        => $items ?? [],
    'saveUrl'      => route_to('admin.cms.entries.save_order'),
    'displayKey'   => 'title',
    'subtitleKeys' => ['collection_key', 'slug'],
    'backUrl'      => route_to('admin.cms.entries'),
    'title'        => $title ?? lang('App.reorder'),
]);
