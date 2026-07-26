<?php $language = $language ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($language)): ?>
    <?php $itemId = (string) ($language['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.cms.languages'),
        'backLabel' => 'CmsLanguages.languages_title',
        'eyebrow' => 'CmsLanguages.languages_details',
        'title' => (string) ($language['native_name'] ?? $language['name'] ?? $language['code'] ?? '—'),
        'subtitle' => (string) ($language['code'] ?? ''),
        'badge' => view('components/table/boolean_cell', ['value' => $language['is_active'] ?? false]),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('CmsLanguages.languages_details') ?></h3>
        <dl class="mt-4 divide-y divide-gray-100 text-sm">
            <?= view('components/display/field_row', [
                'label' => 'CmsLanguages.field_code',
                'value' => $language['code'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'CmsLanguages.field_name',
                'value' => $language['name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'CmsLanguages.field_native_name',
                'value' => $language['native_name'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'CmsLanguages.field_fallback_language_id',
                'value' => $language['fallback_language_id'] ?? '—'
            ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'CmsLanguages.languages_details',
        'items' => [
            ['label' => 'CmsLanguages.field_is_default', 'value' => view('components/table/boolean_cell', ['value' => $language['is_default'] ?? false]), 'isHtml' => true],
            ['label' => 'CmsLanguages.field_is_active', 'value' => view('components/table/boolean_cell', ['value' => $language['is_active'] ?? false]), 'isHtml' => true],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($language['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.languages.write') && !($language['is_default'] ?? false)): ?>
        <form method="post" action="<?= route_to('admin.cms.languages.set_default', $itemId) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('primary')) ?> w-full justify-center">
                <?= esc(lang('CmsLanguages.languages_set_default') ?? 'Set Default') ?>
            </button>
        </form>
    <?php endif; ?>
    <?php if (has_permission('cms.languages.write')): ?>
        <a href="<?= route_to('admin.cms.languages.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
        <a href="<?= route_to('admin.cms.languages.reorder') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center">
            <?= ui_icon('layers', 'h-3.5 w-3.5') ?>
            <?= esc(lang('CmsLanguages.field_sort_order') ?? lang('App.reorder')) ?>
        </a>
    <?php endif; ?>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.languages.write')): ?>
        <form method="post" action="<?= route_to('admin.cms.languages.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($language['name'] ?? $language['code'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        </form>
    <?php endif; ?>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_actions_panel', [
        'content' => $actionsContent,
        'dangerContent' => $dangerContent,
    ]) ?>
    <?php $asideContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => $asideContent,
    ]) ?>
<?php endif; ?>
