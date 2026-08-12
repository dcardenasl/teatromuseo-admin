<?php if (has_permission('catalog.collectionItem.create')): ?>
    <a href="<?= route_to('admin.museum.collection_items.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
        <?= lang('Museum.collection_items_new') ?>
    </a>
<?php endif; ?>
