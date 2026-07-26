<?php
$roles = $roles ?? (is_array($user['roles'] ?? null) ? $user['roles'] : []);
$user = $user ?? [];
$canModifyTarget = ! empty($user) ? can_act_on_user($user) : false;
?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($user)): ?>
    <?php
        $uid = (string) ($user['id'] ?? '');
    $displayName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?: (string) ($user['email'] ?? '—');
    ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.users'),
        'backLabel' => 'Users.back_to_list',
        'eyebrow' => 'Users.details',
        'title' => $displayName,
        'subtitle' => (string) ($user['email'] ?? ''),
        'badge' => '<span class="inline-flex rounded-full px-2 py-1 text-xs ' . esc(status_badge($user['status'] ?? '')) . '">' . esc(localized_status((string) ($user['status'] ?? '-'))) . '</span>',
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('Users.details') ?></h3>
        <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500"><?= lang('Users.first_name') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($user['first_name'] ?? '-')) ?></dd>
            </div>
            <div>
                <dt class="text-gray-500"><?= lang('Users.last_name') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($user['last_name'] ?? '-')) ?></dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-gray-500"><?= lang('Users.email') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($user['email'] ?? '-')) ?></dd>
            </div>
        </dl>
    </section>

    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Users.roles') ?></h3>
            <?php if ($canModifyTarget): ?>
                <a href="<?= route_to('admin.users.edit', $uid) ?>" class="text-xs text-brand-600 hover:text-brand-700"><?= lang('Users.manage_roles') ?> &rarr;</a>
            <?php endif; ?>
        </div>

        <?php if ($roles === []): ?>
            <p class="mt-3 text-sm text-gray-500"><?= lang('Users.no_roles') ?></p>
        <?php else: ?>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php foreach ($roles as $role): ?>
                    <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 text-brand-700 border border-brand-100 px-3 py-1 text-xs">
                        <span class="font-medium"><?= esc((string) ($role['name'] ?? '-')) ?></span>
                        <span class="text-brand-500/80"><?= esc((string) ($role['code'] ?? '')) ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Users.details',
        'items' => [
            ['label' => 'Users.status', 'value' => '<span class="inline-flex rounded-full px-2 py-1 text-xs ' . esc(status_badge($user['status'] ?? '')) . '">' . esc(localized_status((string) ($user['status'] ?? '-'))) . '</span>', 'isHtml' => true],
            ['label' => 'Users.email_verified', 'value' => ! empty($user['email_verified_at']) ? esc(format_date($user['email_verified_at'])) : (is_email_verified($user) ? lang('App.yes') : lang('App.no')), 'isHtml' => true],
            ['label' => 'Users.created_at', 'value' => format_date($user['created_at'] ?? null)],
            ['label' => 'Users.updated_at', 'value' => format_date($user['updated_at'] ?? null)],
        ],
    ]) ?>

    <?php ob_start(); ?>
    <?php if (($user['status'] ?? '') === 'pending_approval'): ?>
        <form method="post" action="<?= route_to('admin.users.approve', $uid) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                <?= lang('Users.approve') ?>
            </button>
        </form>
    <?php endif; ?>
    <?php if ($canModifyTarget): ?>
        <a href="<?= route_to('admin.users.edit', $uid) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center"><?= lang('App.edit') ?></a>
    <?php endif; ?>
    <a href="<?= route_to('admin.audit') . '?user_id=' . rawurlencode($uid) ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center"><?= lang('Users.view_audit') ?></a>
    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?php if ($canModifyTarget): ?>
        <form method="post" action="<?= route_to('admin.users.delete', $uid) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($displayName), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?> w-full justify-center">
                <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
                <?= lang('App.delete') ?>
            </button>
        </form>
    <?php endif; ?>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_actions_panel', [
        'title' => 'Users.quick_actions',
        'content' => $actionsContent,
        'dangerContent' => $dangerContent,
    ]) ?>
    <?php $asideContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => $asideContent,
    ]) ?>
<?php endif; ?>
