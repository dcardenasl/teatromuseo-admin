<?php $redirect = $redirect ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($redirect)): ?>
    <?php $itemId = (string) ($redirect['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.cms.redirects'),
        'backLabel' => 'Redirects.redirects_title',
        'eyebrow' => 'Redirects.redirects_details',
        'title' => (string) ($redirect['old_path'] ?? '—'),
        'subtitle' => (string) ($redirect['new_url'] ?? ''),
        'badge' => view('components/table/boolean_cell', ['value' => $redirect['is_active'] ?? false]),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('Redirects.redirects_details') ?></h3>
        <dl class="mt-4 divide-y divide-gray-100 text-sm">
            <?= view('components/display/field_row', [
                'label' => 'Redirects.field_old_path',
                'value' => $redirect['old_path'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Redirects.field_new_url',
                'value' => $redirect['new_url'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Redirects.field_note',
                'value' => $redirect['note'] ?? '—'
            ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Redirects.redirects_details',
        'items' => [
            [
                'label' => 'Redirects.field_redirect_type',
                'value' => ! empty($redirect['redirect_type']) ? '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">' . esc($redirect['redirect_type']) . '</span>' : '—',
                'isHtml' => true,
            ],
            ['label' => 'Redirects.field_is_active', 'value' => view('components/table/boolean_cell', ['value' => $redirect['is_active'] ?? false]), 'isHtml' => true],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($redirect['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.redirects.write')): ?>
        <a href="<?= route_to('admin.cms.redirects.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
    <?php endif; ?>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (has_permission('cms.redirects.write')): ?>
        <form method="post" action="<?= route_to('admin.cms.redirects.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($redirect['old_path'] ?? $redirect['new_url'] ?? null), 'js') ?>', () => $el.submit())">
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
