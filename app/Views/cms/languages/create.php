<?php
$item = $item ?? [];
$fallbackOptions = [];
foreach ($languages ?? [] as $langItem) {
    if (isset($langItem['id']) && isset($langItem['name'])) {
        $fallbackOptions[$langItem['id']] = $langItem['name'] . ' (' . ($langItem['code'] ?? '') . ')';
    }
}
?>

<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.cms.languages'),
    'backLabel' => 'App.back',
    'eyebrow' => 'CmsLanguages.languages_details',
    'title' => 'CmsLanguages.languages_create',
]) ?>

<form method="post" action="<?= route_to('admin.cms.languages.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2 space-y-6">
        <?php ob_start(); ?>
        <?= view('components/form/text', [
            'name' => 'code',
            'label' => 'CmsLanguages.field_code',
            'required' => true,
            'value' => $item['code'] ?? '',
            'placeholder' => 'CmsLanguages.field_code_placeholder',
            'help' => 'CmsLanguages.field_code_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'name',
            'label' => 'CmsLanguages.field_name',
            'required' => true,
            'value' => $item['name'] ?? '',
            'placeholder' => 'CmsLanguages.field_name_placeholder',
            'help' => 'CmsLanguages.field_name_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/text', [
            'name' => 'native_name',
            'label' => 'CmsLanguages.field_native_name',
            'required' => false,
            'value' => $item['native_name'] ?? '',
            'placeholder' => 'CmsLanguages.field_native_name_placeholder',
            'help' => 'CmsLanguages.field_native_name_help',
            'errors' => $errors ?? []
        ]) ?>
        <?php $mainFields = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'CmsLanguages.languages_create',
            'description' => 'CmsLanguages.languages_details',
            'content' => $mainFields,
            'bodyClass' => 'space-y-4',
        ]) ?>
    </div>

    <aside class="space-y-6">
        <?php ob_start(); ?>
        <?= view('components/form/boolean', [
            'name' => 'is_default',
            'label' => 'CmsLanguages.field_is_default',
            'value' => $item['is_default'] ?? false,
            'on_label' => 'CmsLanguages.field_is_default_on',
            'off_label' => 'CmsLanguages.field_is_default_off',
            'help' => 'CmsLanguages.field_is_default_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/boolean', [
            'name' => 'is_active',
            'label' => 'CmsLanguages.field_is_active',
            'value' => $item['is_active'] ?? false,
            'on_label' => 'CmsLanguages.field_is_active_on',
            'off_label' => 'CmsLanguages.field_is_active_off',
            'help' => 'CmsLanguages.field_is_active_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'fallback_language_id',
            'label' => 'CmsLanguages.field_fallback_language_id',
            'options' => $fallbackOptions,
            'required' => false,
            'value' => $item['fallback_language_id'] ?? '',
            'placeholder' => 'CmsLanguages.field_fallback_language_placeholder',
            'help' => 'CmsLanguages.field_fallback_language_help',
            'errors' => $errors ?? []
        ]) ?>
        <?php $metaFields = ob_get_clean(); ?>

        <?= view('components/display/form_section', [
            'title' => 'CmsLanguages.languages_details',
            'content' => $metaFields,
            'bodyClass' => 'space-y-4',
        ]) ?>

        <?php ob_start(); ?>
        <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.create')) ?></button>
        <a href="<?= route_to('admin.cms.languages') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center py-2.5"><?= esc(lang('App.cancel')) ?></a>
        <?php $actionsContent = ob_get_clean(); ?>

        <?= view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
        ]) ?>
    </aside>
</form>
