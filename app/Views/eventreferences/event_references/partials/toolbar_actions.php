<?php if (has_permission('event.event-references.write')): ?>
    <a href="<?= route_to('admin.eventreferences.event_references.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
        <?= lang('EventReferences.event_references_new') ?>
    </a>
<?php endif; ?>
