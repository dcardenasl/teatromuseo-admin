<?php if (has_permission('event.tickets.write')): ?>
    <a href="<?= route_to('admin.tickets.tickets.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
        <?= lang('Tickets.tickets_new') ?>
    </a>
<?php endif; ?>
