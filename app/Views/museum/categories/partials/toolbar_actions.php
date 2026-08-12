<?php if (has_permission('catalog.category.update')): ?>
    <a href="<?= route_to('admin.museum.categories.reorder') ?>" class="<?= esc(action_button_class('neutral')) ?>">
        <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
        <?= esc(lang('App.reorder')) ?>
    </a>
<?php endif; ?>

<?php if (has_permission('catalog.category.create')): ?>
    <a href="<?= route_to('admin.museum.categories.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
        <?= lang('Museum.categories_new') ?>
    </a>
<?php endif; ?>
