

<?php if (has_permission('cms.collections.write')): ?>
<a href="<?= route_to('admin.cms.collections.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
    <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
    <?= lang('Collections.collections_new') ?>
</a>
<?php endif; ?>
