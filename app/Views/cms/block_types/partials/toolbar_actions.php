

<form method="post" action="<?= route_to('admin.cms.block_types.refresh_cache') ?>" class="inline"
      title="<?= esc(lang('BlockTypes.block_types_refresh_cache_help')) ?>">
    <?= csrf_field() ?>
    <button type="submit" class="<?= esc(action_button_class('secondary')) ?>">
        <?= ui_icon('refresh-cw', 'h-3.5 w-3.5') ?>
        <?= lang('BlockTypes.block_types_refresh_cache') ?>
    </button>
</form>
<a href="<?= route_to('admin.cms.block_types.create') ?>" class="<?= esc(action_button_class('primary')) ?>">
    <?= ui_icon('plus', 'h-3.5 w-3.5') ?>
    <?= lang('BlockTypes.block_types_new') ?>
</a>
