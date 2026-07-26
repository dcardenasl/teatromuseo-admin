<?php $permission = $permission ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($permission)): ?>
    <?php $itemId = (string) ($permission['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.iam.permissions'),
        'backLabel' => 'Iam.permissions_title',
        'eyebrow' => 'Iam.permissions_details',
        'title' => (string) ($permission['code'] ?? '—'),
        'subtitle' => (string) ($permission['description'] ?? ''),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('Iam.permissions_details') ?></h3>
        <dl class="mt-4 divide-y divide-gray-100 text-sm">
            <div class="py-3 first:pt-0">
                <dt class="text-gray-500"><?= lang('Iam.field_code') ?></dt>
                <dd class="mt-1 text-gray-900"><code class="text-xs"><?= esc((string) ($permission['code'] ?? '-')) ?></code></dd>
            </div>
            <div class="py-3">
                <dt class="text-gray-500"><?= lang('Iam.field_description') ?></dt>
                <dd class="mt-1 text-gray-900 whitespace-pre-line"><?= esc((string) ($permission['description'] ?? '-')) ?></dd>
            </div>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Iam.permissions_details',
        'items' => [
            ['label' => 'Iam.field_application', 'value' => trim((string) ($permission['application_name'] ?? '')) !== '' ? (string) $permission['application_name'] . (! empty($permission['application_id']) ? ' (#' . (int) $permission['application_id'] . ')' : '') : '-'],
            ['label' => 'Iam.field_resource', 'value' => (string) ($permission['resource'] ?? '-')],
            ['label' => 'Iam.field_action', 'value' => (string) ($permission['action'] ?? '-')],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($permission['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php if (is_superadmin()): ?>
        <?php ob_start(); ?>
        <a href="<?= route_to('admin.iam.permissions.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
        <?php $actionsContent = ob_get_clean(); ?>

        <?php ob_start(); ?>
        <form method="post" action="<?= route_to('admin.iam.permissions.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($permission['code'] ?? $permission['resource'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= esc(lang('App.delete')) ?>
            </button>
        </form>
        <?php $dangerContent = ob_get_clean(); ?>

        <?= view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]) ?>
    <?php endif; ?>
    <?php $asideContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => $asideContent,
    ]) ?>
<?php endif; ?>
