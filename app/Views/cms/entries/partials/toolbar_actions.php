    <a href="<?= route_to('admin.cms.entries.reorder') ?>" class="<?= esc(action_button_class('neutral')) ?>">
        <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
        <?= esc(lang('App.reorder')) ?>
    </a>

<a href="<?= route_to('admin.cms.entries.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
    <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
    <?= lang('Entries.entries_new') ?>
</a>
