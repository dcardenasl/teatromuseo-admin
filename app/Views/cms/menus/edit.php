<?php
$item = $item ?? [];
$itemId = (string) ($item['id'] ?? '');
$showUrl = $itemId !== '' ? route_to('admin.cms.menus.show', $itemId) : route_to('admin.cms.menus');
?>

<?= view('components/display/admin_page_header', [
    'backUrl' => $showUrl,
    'backLabel' => 'Menus.menus_back_to_detail',
    'eyebrow' => 'Menus.menus_details',
    'title' => 'Menus.menus_edit',
    'subtitle' => (string) ($item['menu_key'] ?? ''),
]) ?>

<form method="post" action="<?= route_to('admin.cms.menus.update', $itemId) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>
    <input type="hidden" name="return_to" value="<?= esc($returnTo ?? '', 'attr') ?>">

    <div class="lg:col-span-2 space-y-6">
        <?= view('cms/menus/_translations', get_defined_vars()) ?>
        <?php ob_start(); ?>
        <?= view('components/form/text', [
            'name' => 'menu_key',
            'label' => 'Menus.field_menu_key',
            'required' => true,
            'value' => $item['menu_key'] ?? '',
            'placeholder' => 'Menus.field_menu_key_placeholder',
            'help' => 'Menus.field_menu_key_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'location',
            'label' => 'Menus.field_location',
            'required' => true,
            'value' => $item['location'] ?? '',
            'placeholder' => 'Menus.field_location_placeholder',
            'help' => 'Menus.field_location_help',
            'errors' => $errors ?? []
        ]) ?>
        <?php $mainFields = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'Menus.menus_edit',
            'description' => 'Menus.menus_details',
            'content' => $mainFields,
            'bodyClass' => 'space-y-4',
        ]) ?>
    </div>

    <aside class="space-y-6">
        <?php ob_start(); ?>
        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Menus.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'Menus.field_is_active_on',
            'off_label' => 'Menus.field_is_active_off',
            'help' => 'Menus.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>
        <?php $metaFields = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'Menus.menus_details',
            'content' => $metaFields,
            'bodyClass' => 'space-y-4',
        ]) ?>

        <?php ob_start(); ?>
        <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.update')) ?></button>
        <a href="<?= esc($showUrl) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
        <?php $actionsContent = ob_get_clean(); ?>

        <?php ob_start(); ?>
        <button type="submit" form="delete-menu-form" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
        <?php $dangerContent = ob_get_clean(); ?>

        <?= view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]) ?>
    </aside>
</form>

<form id="delete-menu-form" method="post" action="<?= route_to('admin.cms.menus.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['name'] ?? $item['menu_key'] ?? null), 'js') ?>', () => $el.submit())">
    <?= csrf_field() ?>
</form>
