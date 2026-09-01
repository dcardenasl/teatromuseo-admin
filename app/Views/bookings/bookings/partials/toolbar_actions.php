<?php if (has_permission('event.bookings.write')): ?>
    <a href="<?= route_to('admin.bookings.bookings.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
        <?= lang('Bookings.bookings_new') ?>
    </a>
<?php endif; ?>
