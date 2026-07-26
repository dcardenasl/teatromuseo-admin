<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.api_keys'),
    'backLabel' => 'ApiKeys.back_to_list',
    'eyebrow' => 'ApiKeys.title',
    'title' => 'ApiKeys.create',
]) ?>

<form method="post" action="<?= route_to('admin.api_keys.store') ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('ApiKeys.create') ?></h3>
            <div class="mt-4 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700" for="name"><?= lang('ApiKeys.name') ?></label>
            <input id="name" name="name" type="text" value="<?= esc(old('name', '')) ?>" required
                class="mt-1 w-full rounded-lg border px-3 py-2 <?= has_field_error('name') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-brand-500 focus:ring-brand-500' ?>">
            <?= render_field_error('name') ?>
        </div>

        <?php $labels = [
            'rate_limit_requests' => lang('ApiKeys.rate_limit_requests'),
            'rate_limit_window'   => lang('ApiKeys.rate_limit_window'),
            'user_rate_limit'     => lang('ApiKeys.user_rate_limit'),
            'ip_rate_limit'       => lang('ApiKeys.ip_rate_limit'),
        ]; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($labels as $field => $label): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="<?= esc($field) ?>"><?= esc($label) ?></label>
                    <input id="<?= esc($field) ?>" name="<?= esc($field) ?>" type="number" min="1" value="<?= esc(old($field, '')) ?>"
                        class="mt-1 w-full rounded-lg border px-3 py-2 <?= has_field_error($field) ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-brand-500 focus:ring-brand-500' ?>">
                    <?= render_field_error($field) ?>
                </div>
            <?php endforeach; ?>
        </div>

            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('ApiKeys.create')) . '</button>'
                . '<a href="' . esc(route_to('admin.api_keys'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
