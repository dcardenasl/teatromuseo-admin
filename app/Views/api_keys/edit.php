<?php $id = (string) ($apiKey['id'] ?? ''); ?>

<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.api_keys.show', $id),
    'backLabel' => 'ApiKeys.back_to_details',
    'eyebrow' => 'ApiKeys.title',
    'title' => 'ApiKeys.edit',
]) ?>

<form method="post" action="<?= route_to('admin.api_keys.update', $id) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('ApiKeys.edit') ?></h3>
            <div class="mt-4 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700" for="name"><?= lang('ApiKeys.name') ?></label>
            <input id="name" name="name" type="text" value="<?= esc(old('name', $apiKey['name'] ?? '')) ?>"
                class="mt-1 w-full rounded-lg border px-3 py-2 <?= has_field_error('name') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-brand-500 focus:ring-brand-500' ?>">
            <?= render_field_error('name') ?>
        </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700" for="is_active"><?= lang('ApiKeys.status') ?></label>
                    <?php $currentActive = old('is_active', isset($apiKey['is_active']) ? ((int) ((bool) $apiKey['is_active'])) : 1); ?>
                    <select id="is_active" name="is_active"
                        class="mt-1 w-full rounded-lg border px-3 py-2 <?= has_field_error('is_active') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-brand-500 focus:ring-brand-500' ?>">
                        <?php foreach (($statusOptions ?? []) as $option): ?>
                            <?php
                            $value = (string) ($option['value'] ?? '');
                            if ($value === '') {
                                continue;
                            }
                            $label = (string) ($option['label'] ?? $value);
                            ?>
                            <option value="<?= esc($value) ?>" <?= (string) $currentActive === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= render_field_error('is_active') ?>
                </div>
        
                <?php $fieldMapping = [
                    'rate_limit_requests' => $apiKey['rate_limit_requests'] ?? '',
                    'rate_limit_window'   => $apiKey['rate_limit_window'] ?? '',
                    'user_rate_limit'     => $apiKey['user_rate_limit'] ?? '',
                    'ip_rate_limit'       => $apiKey['ip_rate_limit'] ?? '',
                ];
$labels = [
    'rate_limit_requests' => lang('ApiKeys.rate_limit_requests'),
    'rate_limit_window'   => lang('ApiKeys.rate_limit_window'),
    'user_rate_limit'     => lang('ApiKeys.user_rate_limit'),
    'ip_rate_limit'       => lang('ApiKeys.ip_rate_limit'),
]; ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($labels as $field => $label): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="<?= esc($field) ?>"><?= esc($label) ?></label>
                            <input id="<?= esc($field) ?>" name="<?= esc($field) ?>" type="number" min="1" value="<?= esc(old($field, $fieldMapping[$field])) ?>"
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
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.save')) . '</button>'
                . '<a href="' . esc(route_to('admin.api_keys.show', $id), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
        ]) ?>
    </aside>
</form>
