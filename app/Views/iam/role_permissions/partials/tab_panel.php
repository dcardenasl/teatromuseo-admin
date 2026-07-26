<?php
$role = $role ?? [];
$applications = $applications ?? [];
$assignedIds = $assignedIds ?? [];
$roleId = (string) ($role['id'] ?? '');
?>

<?php
$initialSelected = count($assignedIds);

/**
 * @param array<int, array<string, mixed>> $permissions
 * @return array<string, array{label: string, permissions: array<int, array<string, mixed>>}>
 */
$groupPermissionsByResource = static function (array $permissions): array {
    $groups = [];

    foreach ($permissions as $permission) {
        $code = (string) ($permission['code'] ?? '');
        $resource = (string) ($permission['resource'] ?? '');

        if ($resource === '' && $code !== '') {
            $parts = explode('.', $code);
            $resource = $parts[1] ?? 'misc';
        }

        if ($resource === '') {
            $resource = 'misc';
        }

        if (! isset($groups[$resource])) {
            $groups[$resource] = [
                'label' => str_replace(['_', '-'], ' ', $resource),
                'resource' => $resource,
                'permissions' => [],
            ];
        }

        $groups[$resource]['permissions'][] = $permission;
    }

    ksort($groups);

    foreach ($groups as &$group) {
        usort($group['permissions'], static function (array $left, array $right): int {
            $leftAction = (string) ($left['action'] ?? '');
            $rightAction = (string) ($right['action'] ?? '');

            return [$leftAction, (string) ($left['code'] ?? '')] <=> [$rightAction, (string) ($right['code'] ?? '')];
        });
    }
    unset($group);

    return $groups;
};

$groupOrder = static function (string $resource): int {
    return match ($resource) {
        'pages' => 10,
        'entries' => 20,
        'collections' => 30,
        'menus' => 40,
        'blocks' => 50,
        'categories' => 60,
        'tags' => 70,
        'settings' => 80,
        'languages' => 90,
        'redirects' => 100,
        'forms' => 110,
        'submissions' => 120,
        'analytics' => 130,
        'users' => 200,
        'files' => 210,
        'api_keys' => 220,
        'iam' => 230,
        default => 999,
    };
};

/**
 * @return array<string, string>
 */
$actionBadgeClass = static function (string $action): string {
    return match ($action) {
        'read' => 'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
        'write' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
        'admin' => 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
        default => 'bg-gray-100 text-gray-700 ring-1 ring-gray-200',
    };
};
?>
<form method="post" action="<?= route_to('admin.iam.role_permissions.save', $roleId) ?>" class="space-y-4"
      x-data="{
          search: '',
          isDirty: false,
          selectedCount: <?= $initialSelected ?>,
          updateCount() {
              this.selectedCount = Array.from(this.$el.querySelectorAll('input[name=\'permission_ids[]\']:checked')).length;
          }
      }"
      @change="isDirty = true; updateCount()">
    <?= csrf_field() ?>
    <input type="hidden" name="code" value="<?= esc((string) ($role['code'] ?? '')) ?>">
    <input type="hidden" name="name" value="<?= esc((string) ($role['name'] ?? '')) ?>">
    <input type="hidden" name="description" value="<?= esc((string) ($role['description'] ?? '')) ?>">
    <input type="hidden" name="application_id" value="<?= esc((string) ($role['application_id'] ?? '')) ?>">
    <input type="hidden" name="permission_ids[]" value="">

    <!-- Orientation callout -->
    <div class="rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800 flex items-center justify-between gap-4">
        <p>
            <strong><?= esc((string) ($role['name'] ?? $role['code'] ?? '')) ?></strong>
            &mdash; <?= esc(lang('Iam.role_permissions_hint')) ?>
        </p>
        <span class="shrink-0 text-xs font-semibold text-blue-700 tabular-nums">
            <span x-text="selectedCount"></span> <?= esc(lang('Iam.role_permissions_selected_label')) ?>
        </span>
    </div>

    <div class="flex items-center gap-2 max-w-md">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <?= ui_icon('search', 'h-4 w-4') ?>
            </span>
            <input type="text" x-model="search" placeholder="<?= esc(lang('Iam.permissions_search_placeholder')) ?>" class="pl-9 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200">
        </div>
        <button type="button" x-show="search !== ''" @click="search = ''" class="text-xs text-gray-500 hover:text-gray-700" x-cloak><?= esc(lang('App.clear') ?? 'Clear') ?></button>
    </div>

    <?php foreach ($applications as $application): ?>
        <?php $permissions = $application['permissions'] ?? []; ?>
        <?php $permissionGroups = $groupPermissionsByResource($permissions); ?>
        <div class="border-b border-gray-200 pb-4">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900"><?= esc((string) ($application['name'] ?? $application['code'] ?? '')) ?></h3>
                <span class="text-xs text-gray-500"><?= count($permissions) ?> <?= esc(lang('Iam.permissions_title')) ?></span>
            </div>
            <div class="space-y-4">
                <?php uasort($permissionGroups, static fn (array $left, array $right) => $groupOrder((string) ($left['resource'] ?? '')) <=> $groupOrder((string) ($right['resource'] ?? ''))); ?>
                <?php foreach ($permissionGroups as $group): ?>
                    <?php
                        $groupPermissions = $group['permissions'];
                    $resourceKey = (string) ($group['resource'] ?? $group['label'] ?? '');
                    ?>
                    <section class="rounded-xl border border-gray-200 bg-gray-50/60 p-3">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <h4 class="text-sm font-semibold text-gray-800">
                                    <?= esc(ucfirst((string) $group['label'])) ?>
                                </h4>
                                <span class="text-xs text-gray-500"><?= count($groupPermissions) ?> permisos</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                                    onclick="window.togglePermissionGroup('<?= esc($resourceKey) ?>', true, this.closest('form'))">
                                    Seleccionar todo
                                </button>
                                <button type="button"
                                    class="rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50"
                                    onclick="window.togglePermissionGroup('<?= esc($resourceKey) ?>', false, this.closest('form'))">
                                    Ninguno
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-3">
                            <?php foreach ($groupPermissions as $permission): ?>
                                <?php
                                    $permissionId = (string) ($permission['id'] ?? '');
                                $permissionCode = strtolower((string) ($permission['code'] ?? ''));
                                $permissionDescription = strtolower((string) ($permission['description'] ?? ''));
                                $permissionAction = strtolower((string) ($permission['action'] ?? ''));
                                ?>
                                <label class="inline-flex items-start gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm hover:bg-gray-50"
                                    x-show="search === '' || '<?= esc($permissionCode) ?>'.includes(search.toLowerCase()) || '<?= esc($permissionDescription) ?>'.includes(search.toLowerCase())"
                                    x-cloak>
                                    <input type="checkbox" name="permission_ids[]" value="<?= esc($permissionId) ?>"
                                        data-resource="<?= esc($resourceKey) ?>"
                                        <?= in_array($permissionId, $assignedIds, true) ? 'checked' : '' ?>
                                        class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    <span class="min-w-0">
                                        <span class="mb-1 flex items-center gap-2">
                                            <code class="font-medium text-gray-900"><?= esc((string) ($permission['code'] ?? '-')) ?></code>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide <?= esc($actionBadgeClass($permissionAction)) ?>">
                                                <?= esc($permissionAction ?: 'other') ?>
                                            </span>
                                        </span>
                                        <span class="block text-xs text-gray-500"><?= esc((string) ($permission['description'] ?? '')) ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
    </div>
    <?php endforeach; ?>

    <div class="flex items-center gap-3">
        <button type="submit" class="<?= esc(action_button_class('primary')) ?>"><?= esc(lang('App.save')) ?></button>
        <p x-show="isDirty" x-cloak class="text-xs font-medium text-yellow-700 flex items-center gap-1">
            <?= ui_icon('triangle-alert', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.unsaved_changes')) ?>
        </p>
    </div>
</form>

<script>
window.togglePermissionGroup = window.togglePermissionGroup || function (resource, state, form) {
    if (!form) {
        return;
    }

    const selector = 'input[type="checkbox"][data-resource="' + resource + '"]';
    const checkboxes = Array.from(form.querySelectorAll(selector));

    checkboxes.forEach((checkbox) => {
        checkbox.checked = state;
    });

    const checked = form.querySelectorAll('input[name="permission_ids[]"]:checked').length;
    const counter = form.querySelector('[x-text="selectedCount"]');
    if (counter && counter.__x && counter.__x.$data) {
        counter.__x.$data.selectedCount = checked;
        counter.__x.$data.isDirty = true;
    }

    form.dispatchEvent(new Event('change', { bubbles: true }));
};
</script>
