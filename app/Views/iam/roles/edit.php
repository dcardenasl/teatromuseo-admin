<?php
/** @var array<int, array{id:int,name:string}> $applications */
$item                  = $item ?? [];
$applications          = $applications ?? [];
$allPermissions        = $allPermissions ?? [];
$assignedPermissionIds = $assignedPermissionIds ?? [];
$isSystem              = (bool) ($item['is_system'] ?? false);
$selectedApp           = old('application_id', $item['application_id'] ?? '');
$selectedApp           = $selectedApp === null ? '' : (string) $selectedApp;

$oldPermIds    = (array) old('permission_ids', $assignedPermissionIds);
$oldPermIdsStr = array_map('strval', $oldPermIds);

$grantableIds       = array_map(
    static fn (array $p): string => (is_superadmin() || actor_owns_permission((string) ($p['code'] ?? ''))) ? (string) ($p['id'] ?? '') : '',
    $allPermissions
);
$assignedIdsStr     = array_map('strval', $assignedPermissionIds);
$lockedAssignedIds  = array_values(array_diff($assignedIdsStr, $grantableIds));
?>
<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.iam.roles'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Iam.roles_title',
    'title' => 'Iam.roles_edit',
]) ?>

<?php if (! $isSystem): ?>
    <form id="role-delete-form" method="post" action="<?= route_to('admin.iam.roles.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['name'] ?? $item['code'] ?? null), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

<form method="post" action="<?= route_to('admin.iam.roles.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Iam.roles_edit')) ?></h3>

            <?php if ($isSystem): ?>
                <p class="mt-2 text-sm text-amber-700"><?= esc(lang('Iam.system_role_notice')) ?></p>
            <?php endif; ?>

            <div class="mt-4 space-y-4">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700" for="application_id"><?= esc(lang('Iam.field_application')) ?></label>
                <select id="application_id" name="application_id" class="<?= esc(input_class('application_id')) ?>">
                    <option value="" <?= $selectedApp === '' ? 'selected' : '' ?>><?= esc(lang('Iam.role_global_label')) ?></option>
                    <?php foreach ($applications as $app): ?>
                        <option value="<?= esc((string) $app['id']) ?>" <?= $selectedApp === (string) $app['id'] ? 'selected' : '' ?>>
                            <?= esc($app['name']) ?> (#<?= (int) $app['id'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= render_field_error('application_id') ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="code"><?= esc(lang('Iam.field_code')) ?> <span class="text-red-500">*</span></label>
                <input id="code" name="code" type="text" required maxlength="100"
                    value="<?= esc(old('code', (string) ($item['code'] ?? ''))) ?>"
                    class="<?= esc(input_class('code')) ?>">
                <?= render_field_error('code') ?>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="name"><?= esc(lang('Iam.field_name')) ?> <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" required maxlength="100"
                    value="<?= esc(old('name', (string) ($item['name'] ?? ''))) ?>"
                    class="<?= esc(input_class('name')) ?>">
                <?= render_field_error('name') ?>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="description"><?= esc(lang('Iam.field_description')) ?></label>
                <textarea id="description" name="description" rows="3" maxlength="500"
                    class="<?= esc(input_class('description')) ?>"><?= esc(old('description', (string) ($item['description'] ?? ''))) ?></textarea>
                <?= render_field_error('description') ?>
            </div>
        </div>

        <div class="pt-2 border-t border-gray-100">
            <span class="block text-sm font-medium text-gray-700"><?= esc(lang('Iam.permissions_assigned')) ?></span>
            <p class="text-xs text-gray-500 mt-1"><?= esc(lang('Iam.permissions_help_edit')) ?></p>

            <?php if ($allPermissions === []): ?>
                <p class="mt-2 text-sm text-gray-500 italic"><?= esc(lang('Iam.permissions_none_available')) ?></p>
            <?php else: ?>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2 max-h-72 overflow-y-auto pr-1">
                    <?php foreach ($allPermissions as $perm): ?>
                        <?php $pid = (string) ($perm['id'] ?? ''); ?>
                        <?php $grantable = is_superadmin() || actor_owns_permission((string) ($perm['code'] ?? '')); ?>
                        <label class="inline-flex items-start gap-2 text-sm rounded-lg border border-gray-200 px-3 py-2 <?= $grantable ? 'hover:bg-gray-50' : 'bg-gray-50 opacity-70' ?>" title="<?= $grantable ? '' : esc(lang('Iam.permissions_locked_tooltip')) ?>">
                            <input type="checkbox" name="permission_ids[]" value="<?= esc($pid) ?>"
                                <?= in_array($pid, $oldPermIdsStr, true) ? 'checked' : '' ?>
                                <?= $grantable ? '' : 'disabled' ?>
                                class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span>
                                <code class="font-medium text-gray-900"><?= esc((string) ($perm['code'] ?? '-')) ?></code>
                                <?php if (! $grantable): ?><span class="ml-1 text-xs text-amber-600" aria-hidden="true">locked</span><?php endif; ?>
                                <?php if (! empty($perm['description'])): ?>
                                    <span class="block text-xs text-gray-500"><?= esc((string) $perm['description']) ?></span>
                                <?php endif; ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php // Sentinel: ensures `permission_ids` is always posted, so the form?>
            <?php // can clear all permissions when no checkboxes are ticked. Filtered?>
            <?php // out by RoleStoreRequest::normalizedPermissionIds (non-positive).?>
            <input type="hidden" name="permission_ids[]" value="">

            <?php foreach ($lockedAssignedIds as $lockedId): ?>
                <input type="hidden" name="permission_ids[]" value="<?= esc((string) $lockedId) ?>">
            <?php endforeach; ?>
            <?php if ($lockedAssignedIds !== []): ?>
                <p class="mt-2 text-xs text-amber-700"><?= esc(lang('Iam.permissions_some_locked')) ?></p>
            <?php endif; ?>
            <?= render_field_error('permission_ids') ?>
        </div>

            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.update')) . '</button>'
                . '<a href="' . esc(route_to('admin.iam.roles'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
            'dangerContent' => ! $isSystem
                ? '<button type="submit" form="role-delete-form" class="' . esc(action_button_class('danger'), 'attr') . '">'
                    . ui_icon('trash', 'h-3.5 w-3.5') . esc(lang('App.delete')) . '</button>'
                : '',
        ]) ?>
    </aside>
</form>
