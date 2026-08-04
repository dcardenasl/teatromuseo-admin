<?php if (has_permission('event.event-types.write')): ?>
    <a href="<?= route_to('admin.events.event_types.reorder') ?>" class="<?= esc(action_button_class('neutral')) ?>">
        <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
        <?= esc(lang('App.reorder')) ?>
    </a>
    <a href="<?= route_to('admin.events.event_types.create') ?>" class="<?= esc(action_button_class('primary')) ?>"><?= ui_icon('plus') ?> <?= esc(lang('Events.event_types_new')) ?></a>
<?php endif; ?>
