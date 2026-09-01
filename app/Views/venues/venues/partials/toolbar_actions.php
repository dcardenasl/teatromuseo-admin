<?php if (has_permission('event.venues.write')): ?>
    <a href="<?= route_to('admin.venues.venues.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
        <?= lang('Venues.venues_new') ?>
    </a>
<?php endif; ?>
