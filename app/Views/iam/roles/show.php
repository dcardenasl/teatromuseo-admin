<?php
$role = $role ?? [];
$allPermissions = $allPermissions ?? [];
$assignedPermissionIds = $assignedPermissionIds ?? [];
$assignedSet = array_flip(array_map('intval', $assignedPermissionIds));
$assignedItems = array_values(array_filter($allPermissions, static fn (array $p): bool => isset($assignedSet[(int) ($p['id'] ?? 0)])));

$canModify = can_modify_role($role);
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($role)): ?>
    <?php $itemId = (string) ($role['id'] ?? ''); ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.iam.roles'),
        'backLabel' => 'Iam.roles_title',
        'eyebrow' => 'Iam.roles_details',
        'title' => (string) ($role['name'] ?? $role['code'] ?? '—'),
        'subtitle' => (string) ($role['code'] ?? ''),
        'badge' => ! empty($role['is_system']) ? '<span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200">' . esc(lang('App.warning')) . '</span>' : null,
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Iam.permissions_assigned') ?></h3>
            <?php if ($canModify): ?>
                <a href="<?= route_to('admin.iam.roles.edit', $itemId) ?>" class="text-sm text-brand-600 hover:text-brand-700"><?= esc(lang('Iam.permissions_edit_link')) ?></a>
            <?php endif; ?>
        </div>

        <?php if ($assignedItems === []): ?>
            <p class="mt-3 text-sm text-gray-500"><?= lang('Iam.permissions_none_assigned') ?></p>
        <?php else: ?>
            <ul class="mt-4 divide-y divide-gray-100 border border-gray-100 rounded-lg">
                <?php foreach ($assignedItems as $perm): ?>
                    <li class="p-3 text-sm">
                        <code class="text-gray-900"><?= esc((string) ($perm['code'] ?? '-')) ?></code>
                        <?php if (! empty($perm['description'])): ?>
                            <span class="ml-2 text-gray-500"><?= esc((string) $perm['description']) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Iam.roles_details',
        'items' => [
            ['label' => 'Iam.field_code', 'value' => (string) ($role['code'] ?? '-')],
            ['label' => 'Iam.field_name', 'value' => (string) ($role['name'] ?? '-')],
            ['label' => 'Iam.field_application', 'value' => ! empty($role['application_name']) ? (string) $role['application_name'] . ' (#' . (int) $role['application_id'] . ')' : (! empty($role['application_id']) ? '#' . (int) $role['application_id'] : lang('Iam.role_global_label'))],
            ['label' => 'Iam.field_description', 'value' => (string) ($role['description'] ?? '—')],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($role['created_at'] ?? '-')],
        ],
    ]) ?>

    <?php if (! empty($role['is_system'])): ?>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
            <?= esc(lang('Iam.system_role_notice')) ?>
        </div>
    <?php endif; ?>

    <?php ob_start(); ?>
    <?php if ($canModify): ?>
        <a href="<?= route_to('admin.iam.roles.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center">
            <?= ui_icon('pencil', 'h-3.5 w-3.5') ?>
            <?= lang('App.edit') ?>
        </a>
    <?php endif; ?>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if (empty($role['is_system']) || is_superadmin()): ?>
        <form method="post" action="<?= route_to('admin.iam.roles.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($role['name'] ?? $role['code'] ?? null), 'js') ?>', () => $el.submit())">
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
