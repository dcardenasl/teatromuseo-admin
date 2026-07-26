<?php
/** @var array $submission */
$submission = $submission ?? [];
$formData   = $submission['form_data'] ?? [];
$status     = $submission['status'] ?? 'new';
$itemId     = (string) ($submission['id'] ?? '');

$statusColors = [
    'new'      => 'bg-blue-50 text-blue-700 ring-blue-600/20',
    'read'     => 'bg-gray-50 text-gray-600 ring-gray-500/10',
    'replied'  => 'bg-green-50 text-green-700 ring-green-600/20',
    'spam'     => 'bg-red-50 text-red-700 ring-red-600/20',
    'archived' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
];
$statusClass = $statusColors[$status] ?? $statusColors['read'];

$actionButtons = [
    'replied'  => ['label' => lang('FormSubmissions.status_replied'),  'color' => 'success'],
    'spam'     => ['label' => lang('FormSubmissions.status_spam'),     'color' => 'danger'],
    'archived' => ['label' => lang('FormSubmissions.status_archived'), 'color' => 'default'],
];
if ($status !== 'new' && $status !== 'read') {
    $actionButtons['read'] = ['label' => lang('FormSubmissions.status_read'), 'color' => 'default'];
}
unset($actionButtons[$status]);
?>

<div class="mb-4">
    <a href="<?= route_to('admin.cms.form_submissions') ?>" class="text-sm text-brand-600 hover:text-brand-700">
        &larr; <?= lang('FormSubmissions.title') ?>
    </a>
</div>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($submission)): ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <?php /* ── Main: form data ──────────────────────────────────────────── */ ?>
    <section class="lg:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h3 class="text-lg font-semibold text-gray-900 mb-4"><?= lang('FormSubmissions.detail_title') ?></h3>

        <dl class="divide-y divide-gray-100 text-sm">
            <?php if (! empty($formData['name'])): ?>
                <?= view('components/display/field_row', [
                    'label' => 'FormSubmissions.field_name',
                    'value' => $formData['name'],
                ]) ?>
            <?php endif; ?>

            <?php if (! empty($formData['email'])): ?>
                <?= view('components/display/field_row', [
                    'label' => 'FormSubmissions.field_email',
                    'value' => '<a href="mailto:' . esc($formData['email']) . '" class="text-brand-600 hover:underline">' . esc($formData['email']) . '</a>',
                    'isHtml' => true,
                ]) ?>
            <?php endif; ?>

            <?php if (! empty($formData['phone'])): ?>
                <?= view('components/display/field_row', [
                    'label' => 'FormSubmissions.field_phone',
                    'value' => $formData['phone'],
                ]) ?>
            <?php endif; ?>

            <?php if (! empty($formData['company'])): ?>
                <?= view('components/display/field_row', [
                    'label' => 'FormSubmissions.field_company',
                    'value' => $formData['company'],
                ]) ?>
            <?php endif; ?>
        </dl>

        <?php if (! empty($formData['message'])): ?>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                    <?= lang('FormSubmissions.field_message') ?>
                </dt>
                <dd class="text-sm text-gray-900 whitespace-pre-wrap leading-relaxed bg-gray-50 rounded-lg p-4 border border-gray-100">
                    <?= esc($formData['message']) ?>
                </dd>
            </div>
        <?php endif; ?>

        <?php /* Other form fields we don't know about (e.g., custom fields) */ ?>
        <?php
        $knownFields = ['name', 'email', 'phone', 'company', 'message'];
$extraFields = array_diff_key($formData, array_flip($knownFields));
?>
        <?php if (! empty($extraFields)): ?>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <?php foreach ($extraFields as $key => $val): ?>
                        <?php
                        // Checkbox-group fields submit as an array of selected option values.
                        $valText = is_array($val) ? implode(', ', array_map('strval', $val)) : (string) $val;
                        ?>
                        <div>
                            <dt class="text-gray-500"><?= esc(ucfirst(str_replace('_', ' ', $key))) ?></dt>
                            <dd class="mt-1 text-gray-900"><?= esc($valText) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        <?php endif; ?>
    </section>

    <?php /* ── Sidebar: meta + actions ──────────────────────────────────── */ ?>
    <aside class="space-y-4">

        <?php /* Status card */ ?>
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h4 class="text-sm font-semibold text-gray-700 mb-3"><?= lang('FormSubmissions.field_status') ?></h4>

            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-sm font-medium ring-1 ring-inset <?= esc($statusClass) ?>">
                <?= esc(lang('FormSubmissions.status_' . $status) ?: $status) ?>
            </span>

            <?php if (! empty($actionButtons)): ?>
                <div class="mt-4 space-y-2">
                    <?php foreach ($actionButtons as $newStatus => $btn): ?>
                        <form method="post" action="<?= route_to('admin.cms.form_submissions.update_status', $itemId) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="status" value="<?= esc($newStatus) ?>">
                            <button type="submit" class="w-full <?= esc(action_button_class($btn['color'])) ?>">
                                <?= esc($btn['label']) ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php /* Metadata card */ ?>
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h4 class="text-sm font-semibold text-gray-700 mb-3"><?= lang('App.details') ?></h4>
            <dl class="space-y-2 text-sm">
                <div>
                    <dt class="text-gray-500"><?= lang('FormSubmissions.field_date') ?></dt>
                    <dd class="text-gray-900"><?= esc((string) ($submission['created_at'] ?? '-')) ?></dd>
                </div>
                <?php if (! empty($submission['ip_address']) && ! ($submission['is_anonymized'] ?? false)): ?>
                    <div>
                        <dt class="text-gray-500"><?= lang('FormSubmissions.field_ip') ?></dt>
                        <dd class="text-gray-900 font-mono text-xs"><?= esc($submission['ip_address']) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if (! empty($submission['form_key'])): ?>
                    <div>
                        <dt class="text-gray-500"><?= lang('FormSubmissions.field_form_key') ?></dt>
                        <dd class="text-gray-900 font-mono text-xs"><?= esc($submission['form_key']) ?></dd>
                    </div>
                <?php endif; ?>
                <?php if (! empty($submission['page_id'])): ?>
                    <div>
                        <dt class="text-gray-500"><?= lang('FormSubmissions.field_page_id') ?></dt>
                        <dd class="text-gray-900"><?= esc((string) $submission['page_id']) ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </section>

    </aside>
</div>
<?php endif; ?>
