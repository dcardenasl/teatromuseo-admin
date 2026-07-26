<?php
$applications = $applications ?? [];
$roles = $roles ?? [];
$assignments = $assignments ?? [];
$activeRoleId = (string) ($activeRoleId ?? '');
?>

<div class="mb-4 flex items-center justify-between">
    <div>
        <h2 class="text-xl font-semibold text-gray-900"><?= esc(lang('Iam.role_permissions_title')) ?></h2>
        <p class="text-sm text-gray-500"><?= esc(lang('Iam.role_permissions_help')) ?></p>
    </div>
</div>

<?php if (! empty($error)): ?>
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= esc((string) $error) ?></div>
<?php elseif ($roles === []): ?>
    <p class="text-sm text-gray-500"><?= esc(lang('Iam.roles_empty')) ?></p>
<?php else: ?>
    <div x-data="{ tab: '<?= esc($activeRoleId) ?>' }" class="space-y-4">
        <div class="flex gap-2 overflow-x-auto border-b border-gray-200">
            <?php foreach ($roles as $role): ?>
                <?php $roleId = (string) ($role['id'] ?? ''); ?>
                <button type="button"
                    class="whitespace-nowrap border-b-2 px-3 py-2 text-sm"
                    :class="tab === '<?= esc($roleId) ?>' ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    @click="tab = '<?= esc($roleId) ?>'">
                    <?= esc((string) ($role['name'] ?? $role['code'] ?? $roleId)) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($roles as $role): ?>
            <?php $roleId = (string) ($role['id'] ?? ''); ?>
            <section x-show="tab === '<?= esc($roleId) ?>'" x-cloak>
                <?= view('iam/role_permissions/partials/tab_panel', [
                    'role' => $role,
                    'applications' => $applications,
                    'assignedIds' => array_map('strval', $assignments[$roleId] ?? []),
                ]) ?>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
