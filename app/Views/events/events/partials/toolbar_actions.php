<?php if (has_permission('event.events.write')): ?>
    <a href="<?= route_to('admin.events.events.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
        <?= lang('Events.events_new') ?>
    </a>
<?php endif; ?>
