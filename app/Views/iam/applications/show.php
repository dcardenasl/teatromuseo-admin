<?php $application = $application ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($application)): ?>
    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.iam.applications'),
        'backLabel' => 'Iam.applications_title',
        'eyebrow' => 'Iam.applications_details',
        'title' => (string) ($application['name'] ?? $application['code'] ?? '—'),
        'subtitle' => (string) ($application['code'] ?? ''),
        'badge' => view('components/table/boolean_cell', ['value' => $application['is_active'] ?? false]),
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900"><?= lang('Iam.applications_details') ?></h3>
        <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
            <p class="font-semibold"><?= esc(lang('App.readonly_notice')) ?></p>
            <p class="mt-1 text-xs text-blue-800"><?= esc(lang('Iam.applications_managed_server_side')) ?></p>
        </div>
        <dl class="mt-4 divide-y divide-gray-100 text-sm">
            <div class="py-3 first:pt-0">
                <dt class="text-gray-500"><?= lang('Iam.field_code') ?></dt>
                <dd class="mt-1 text-gray-900"><code class="text-xs"><?= esc((string) ($application['code'] ?? '-')) ?></code></dd>
            </div>
            <div class="py-3">
                <dt class="text-gray-500"><?= lang('Iam.field_name') ?></dt>
                <dd class="mt-1 text-gray-900"><?= esc((string) ($application['name'] ?? '-')) ?></dd>
            </div>
            <div class="py-3">
                <dt class="text-gray-500"><?= lang('Iam.field_description') ?></dt>
                <dd class="mt-1 text-gray-900 whitespace-pre-line"><?= esc((string) ($application['description'] ?? '-')) ?></dd>
            </div>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <?= view('components/display/admin_meta_panel', [
        'title' => 'Iam.applications_details',
        'description' => 'Iam.applications_managed_server_side',
        'items' => [
            ['label' => 'Iam.field_active', 'value' => view('components/table/boolean_cell', ['value' => $application['is_active'] ?? false]), 'isHtml' => true],
            ['label' => 'TableColumns.created_at', 'value' => (string) ($application['created_at'] ?? '-')],
        ],
    ]) ?>
    <?php $asideContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => $asideContent,
    ]) ?>
<?php endif; ?>
