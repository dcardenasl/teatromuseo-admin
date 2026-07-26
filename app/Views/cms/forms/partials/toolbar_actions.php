<?php if (has_permission('cms.forms.write')): ?>
    <a href="<?= route_to('admin.cms.forms.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
        <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
        <?= lang('Forms.btn_create') ?>
    </a>
<?php endif; ?>
