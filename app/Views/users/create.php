<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.users'),
    'backLabel' => 'Users.back_to_list',
    'eyebrow' => 'Users.title',
    'title' => 'Users.create',
]) ?>

<form method="post" action="<?= route_to('admin.users.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="<?= card_class() ?>">
            <h3 class="<?= section_heading_class() ?>"><?= lang('Users.create') ?></h3>
            <div class="mt-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700" for="first_name"><?= lang('Users.first_name') ?></label>
                <input id="first_name" name="first_name" type="text" value="<?= esc(old('first_name', '')) ?>" required
                    class="<?= input_class('first_name') ?>" <?= field_aria_attrs('first_name', required: true) ?>>
                <?= render_field_error('first_name') ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="last_name"><?= lang('Users.last_name') ?></label>
                <input id="last_name" name="last_name" type="text" value="<?= esc(old('last_name', '')) ?>" required
                    class="<?= input_class('last_name') ?>" <?= field_aria_attrs('last_name', required: true) ?>>
                <?= render_field_error('last_name') ?>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700" for="email"><?= lang('Users.email') ?></label>
            <input id="email" name="email" type="email" value="<?= esc(old('email', '')) ?>" required
                class="<?= input_class('email') ?>" <?= field_aria_attrs('email', required: true) ?>>
            <?= render_field_error('email') ?>
        </div>

        <div>
            <span class="block text-sm font-medium text-gray-700"><?= lang('Users.roles') ?></span>
            <p class="text-xs text-gray-500 mt-1"><?= lang('Users.roles_help_create') ?></p>
            <?php $oldRoleIds = (array) old('role_ids', []); ?>
            <?php if (! empty($assignableRoles)): ?>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <?php foreach ($assignableRoles as $role): ?>
                        <label class="inline-flex items-start gap-2 text-sm rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50">
                            <input type="checkbox" name="role_ids[]" value="<?= (int) $role['id'] ?>"
                                <?= in_array((string) $role['id'], array_map('strval', $oldRoleIds), true) ? 'checked' : '' ?>
                                class="mt-1 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span>
                                <span class="font-medium text-gray-900"><?= esc($role['name']) ?></span>
                                <span class="block text-xs text-gray-500"><?= esc($role['code']) ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-gray-500 mt-2"><?= lang('Users.roles_help_default') ?></p>
            <?php else: ?>
                <p class="mt-2 text-sm text-gray-500 italic"><?= lang('Users.roles_none_assignable') ?></p>
            <?php endif; ?>
            <?= render_field_error('role_ids') ?>
        </div>

            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('Users.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.users'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
