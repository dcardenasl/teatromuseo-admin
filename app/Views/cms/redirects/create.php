<?php $item = $item ?? []; ?>

<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.cms.redirects'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Redirects.redirects_details',
    'title' => 'Redirects.redirects_create',
]) ?>

<form method="post" action="<?= route_to('admin.cms.redirects.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2 space-y-6">
        <?php ob_start(); ?>
        <?= view('components/form/text', [
            'name' => 'old_path',
            'label' => 'Redirects.field_old_path',
            'required' => true,
            'value' => $item['old_path'] ?? '',
            'placeholder' => 'Redirects.field_old_path_placeholder',
            'help' => 'Redirects.field_old_path_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'new_url',
            'label' => 'Redirects.field_new_url',
            'required' => true,
            'value' => $item['new_url'] ?? '',
            'placeholder' => 'Redirects.field_new_url_placeholder',
            'help' => 'Redirects.field_new_url_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/textarea', [
            'name' => 'note',
            'label' => 'Redirects.field_note',
            'required' => false,
            'value' => $item['note'] ?? '',
            'placeholder' => 'Redirects.field_note_placeholder',
            'help' => 'Redirects.field_note_help',
            'errors' => $errors ?? []
        ]) ?>
        <?php $mainFields = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'Redirects.redirects_create',
            'description' => 'Redirects.redirects_details',
            'content' => $mainFields,
            'bodyClass' => 'space-y-4',
        ]) ?>
    </div>

    <aside class="space-y-6">
        <?php ob_start(); ?>
        <?= view('components/form/select', [
            'name' => 'redirect_type',
            'label' => 'Redirects.field_redirect_type',
            'required' => false,
            'placeholder' => 'Redirects.field_redirect_type_placeholder',
            'help' => 'Redirects.field_redirect_type_help',
            'options' => [
                '301' => '301',
                '302' => '302'
            ],
            'value' => $item['redirect_type'] ?? '301',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'Redirects.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'Redirects.field_is_active_on',
            'off_label' => 'Redirects.field_is_active_off',
            'help' => 'Redirects.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>
        <?php $metaFields = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'Redirects.redirects_details',
            'content' => $metaFields,
            'bodyClass' => 'space-y-4',
        ]) ?>

        <?php ob_start(); ?>
        <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.create')) ?></button>
        <a href="<?= route_to('admin.cms.redirects') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
        <?php $actionsContent = ob_get_clean(); ?>

        <?= view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
        ]) ?>
    </aside>
</form>
