<?php
/** @var array<int, array{id:int,name:string}> $applications */
$item         = $item ?? [];
$applications = $applications ?? [];
$selectedApp  = (int) old('application_id', (int) ($item['application_id'] ?? 0));
?>
<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.iam.permissions'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Iam.permissions_title',
    'title' => 'Iam.permissions_edit',
]) ?>

<form id="permission-delete-form" method="post" action="<?= route_to('admin.iam.permissions.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($item['code'] ?? $item['resource'] ?? null), 'js') ?>', () => $el.submit())">
    <?= csrf_field() ?>
</form>

<form method="post" action="<?= route_to('admin.iam.permissions.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Iam.permissions_edit')) ?></h3>
            <div class="mt-4 space-y-4">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700" for="application_id"><?= esc(lang('Iam.field_application')) ?> <span class="text-red-500">*</span></label>
                <?php if ($applications === []): ?>
                    <p class="mt-2 text-sm text-amber-700"><?= esc(lang('Iam.no_applications')) ?></p>
                    <input type="hidden" name="application_id" value="<?= esc((string) $selectedApp) ?>">
                <?php else: ?>
                    <select id="application_id" name="application_id" required class="<?= esc(input_class('application_id')) ?>">
                        <?php foreach ($applications as $app): ?>
                            <option value="<?= esc((string) $app['id']) ?>" <?= $selectedApp === (int) $app['id'] ? 'selected' : '' ?>>
                                <?= esc($app['name']) ?> (#<?= (int) $app['id'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <?= render_field_error('application_id') ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="code"><?= esc(lang('Iam.field_code')) ?> <span class="text-red-500">*</span></label>
                <input id="code" name="code" type="text" required maxlength="100"
                    value="<?= esc(old('code', (string) ($item['code'] ?? ''))) ?>"
                    class="<?= esc(input_class('code')) ?>">
                <?= render_field_error('code') ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="resource"><?= esc(lang('Iam.field_resource')) ?> <span class="text-red-500">*</span></label>
                <input id="resource" name="resource" type="text" required maxlength="50"
                    value="<?= esc(old('resource', (string) ($item['resource'] ?? ''))) ?>"
                    class="<?= esc(input_class('resource')) ?>">
                <?= render_field_error('resource') ?>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="action"><?= esc(lang('Iam.field_action')) ?> <span class="text-red-500">*</span></label>
                <input id="action" name="action" type="text" required maxlength="50"
                    value="<?= esc(old('action', (string) ($item['action'] ?? ''))) ?>"
                    class="<?= esc(input_class('action')) ?>">
                <?= render_field_error('action') ?>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700" for="description"><?= esc(lang('Iam.field_description')) ?></label>
            <textarea id="description" name="description" rows="3" maxlength="500"
                class="<?= esc(input_class('description')) ?>"><?= esc(old('description', (string) ($item['description'] ?? ''))) ?></textarea>
            <?= render_field_error('description') ?>
        </div>

            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.update')) . '</button>'
                . '<a href="' . esc(route_to('admin.iam.permissions'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
            'dangerContent' => '<button type="submit" form="permission-delete-form" class="' . esc(action_button_class('danger'), 'attr') . '">'
                . ui_icon('trash', 'h-3.5 w-3.5') . esc(lang('App.delete')) . '</button>',
        ]) ?>
    </aside>
</form>
