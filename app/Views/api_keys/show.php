<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($apiKey)): ?>
    <?php
    $id = (string) ($apiKey['id'] ?? '');
    $generatedApiKey = (string) (session('generatedApiKey') ?? '');
    $generatedApiKeyName = (string) (session('generatedApiKeyName') ?? ($apiKey['name'] ?? ''));
    ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.api_keys'),
        'backLabel' => 'ApiKeys.back_to_list',
        'eyebrow' => 'ApiKeys.details',
        'title' => (string) ($apiKey['name'] ?? '—'),
        'subtitle' => (string) ($apiKey['key_prefix'] ?? ''),
        'badge' => '<span class="inline-flex rounded-full px-2 py-1 text-xs ' . (! empty($apiKey['is_active']) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700') . '">' . (! empty($apiKey['is_active']) ? lang('ApiKeys.active') : lang('ApiKeys.inactive')) . '</span>',
    ]) ?>

    <?php ob_start(); ?>
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('ApiKeys.details') ?></h3>
            <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-gray-500"><?= lang('ApiKeys.name') ?></dt>
                    <dd class="mt-1 text-gray-900"><?= esc((string) ($apiKey['name'] ?? '-')) ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500"><?= lang('ApiKeys.key_prefix') ?></dt>
                    <dd class="mt-1 text-gray-900 font-mono text-xs"><?= esc((string) ($apiKey['key_prefix'] ?? '-')) ?></dd>
                </div>
                <div>
                    <dt class="text-gray-500"><?= lang('ApiKeys.status') ?></dt>
                    <dd class="mt-1">
                        <?php $is_active = ! empty($apiKey['is_active']); ?>
                        <span class="inline-flex rounded-full px-2 py-1 text-xs <?= $is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' ?>">
                            <?= $is_active ? lang('ApiKeys.active') : lang('ApiKeys.inactive') ?>
                        </span>
                    </dd>
                </div>
            </dl>
        </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'ApiKeys.quick_actions',
        'items' => [
            ['label' => 'ApiKeys.rate_limit_requests', 'value' => (string) ($apiKey['rate_limit_requests'] ?? '-')],
            ['label' => 'ApiKeys.rate_limit_window', 'value' => (string) ($apiKey['rate_limit_window'] ?? '-')],
            ['label' => 'ApiKeys.user_rate_limit', 'value' => (string) ($apiKey['user_rate_limit'] ?? '-')],
            ['label' => 'ApiKeys.ip_rate_limit', 'value' => (string) ($apiKey['ip_rate_limit'] ?? '-')],
            ['label' => 'ApiKeys.created_at', 'value' => format_date($apiKey['created_at'] ?? null)],
            ['label' => 'ApiKeys.updated_at', 'value' => format_date($apiKey['updated_at'] ?? null)],
        ],
    ]) ?>

    <?php if (has_permission('apikeys.write')): ?>
        <?php ob_start(); ?>
        <a href="<?= route_to('admin.api_keys.edit', $id) ?>" class="<?= esc(action_button_class('primary')) ?> w-full justify-center text-center"><?= lang('App.edit') ?></a>
        <a href="<?= route_to('admin.api_keys.create') ?>" class="<?= esc(action_button_class()) ?> w-full justify-center text-center"><?= lang('ApiKeys.create') ?></a>
        <?php $actionsContent = ob_get_clean(); ?>

        <?php ob_start(); ?>
        <form method="post" action="<?= route_to('admin.api_keys.delete', $id) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($apiKey['name'] ?? null), 'js') ?>', () => $el.submit())">
            <?= csrf_field() ?>
            <button type="submit" class="<?= esc(action_button_class('danger')) ?> w-full justify-center"><?= lang('App.delete') ?></button>
        </form>
        <?php $dangerContent = ob_get_clean(); ?>

        <?= view('components/display/admin_actions_panel', [
            'title' => 'ApiKeys.quick_actions',
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]) ?>
    <?php else: ?>
        <span class="inline-flex items-center gap-2 rounded-md bg-amber-50 text-amber-800 border border-amber-200 px-3 py-1.5 text-xs">
            <?= lang('ApiKeys.read_only_badge') ?>
        </span>
    <?php endif; ?>
    <?php $asideContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => $asideContent,
    ]) ?>

    <?php if ($generatedApiKey !== ''): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            x-data="{ copied: false, revealed: false, key: '<?= esc($generatedApiKey, 'js') ?>' }">
            <div class="w-full max-w-2xl rounded-xl border border-gray-200 bg-white p-5 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900"><?= lang('ApiKeys.raw_key_one_time_title') ?></h3>
                <p class="mt-2 text-sm text-gray-600"><?= lang('ApiKeys.raw_key_one_time_body') ?></p>
                <p class="mt-1 text-sm text-gray-500"><?= esc($generatedApiKeyName) ?></p>

                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3">
                    <label class="text-xs font-medium uppercase tracking-wide text-amber-700"><?= lang('ApiKeys.raw_key') ?></label>
                    <div class="mt-2 flex items-center gap-2">
                        <code class="flex-1 overflow-auto rounded-md bg-white px-3 py-2 text-xs text-gray-900" x-text="revealed ? key : '*****************************'"></code>
                        <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50" @click="revealed = !revealed" x-text="revealed ? '<?= esc(lang('ApiKeys.hide_key')) ?>' : '<?= esc(lang('ApiKeys.show_key')) ?>'"></button>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2">
                    <button type="button" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="navigator.clipboard.writeText(key).then(() => { copied = true; setTimeout(() => copied = false, 2000); })" x-text="copied ? '<?= esc(lang('ApiKeys.copied')) ?>' : '<?= esc(lang('ApiKeys.copy_key')) ?>'"></button>
                    <a href="<?= route_to('admin.api_keys.show', $id) ?>" class="rounded-lg bg-brand-600 px-3 py-2 text-sm text-white hover:bg-brand-700"><?= lang('App.close') ?></a>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
