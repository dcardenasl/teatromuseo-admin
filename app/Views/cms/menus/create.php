<?php $item = $item ?? []; ?>

<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.cms.menus'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Menus.menus_details',
    'title' => 'Menus.menus_create',
]) ?>

<form method="post" action="<?= route_to('admin.cms.menus.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

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
            'title' => 'Menus.menus_create',
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
        <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.create')) ?></button>
        <a href="<?= route_to('admin.cms.menus') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
        <?php $actionsContent = ob_get_clean(); ?>

        <?= view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
        ]) ?>
    </aside>
</form>
