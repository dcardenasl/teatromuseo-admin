<?php $event = $event ?? []; ?>

<?php if (! empty($error)): ?>
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <p class="text-sm text-red-600"><?= esc($error) ?></p>
    </div>
<?php elseif (! empty($event)): ?>
    <?php
        $itemId = (string) ($event['id'] ?? '');
    $eventTypeLabels = [
        'function' => lang('Events.option_event_type_function'),
        'festival' => lang('Events.option_event_type_festival'),
        'course' => lang('Events.option_event_type_course'),
        'workshop' => lang('Events.option_event_type_workshop'),
        'other' => lang('Events.option_event_type_other'),
    ];
    $eventStatusLabels = [
        'draft' => lang('Events.option_status_draft'),
        'published' => lang('Events.option_status_published'),
        'cancelled' => lang('Events.option_status_cancelled'),
    ];
    $eventType = strtolower((string) ($event['event_type'] ?? ''));
    $eventStatus = strtolower((string) ($event['status'] ?? ''));
    $eventTypeLabel = $eventTypeLabels[$eventType] ?? ($eventType !== '' ? $eventType : '—');
    $eventStatusLabel = $eventStatusLabels[$eventStatus] ?? ($eventStatus !== '' ? $eventStatus : '—');
    $eventSlug = trim((string) ($event['slug'] ?? ''));
    $subtitleParts = [];
    if ($eventSlug !== '') {
        $subtitleParts[] = lang('Events.field_slug') . ': ' . $eventSlug;
    }
    if ($eventTypeLabel !== '—') {
        $subtitleParts[] = $eventTypeLabel;
    }
    $eventStatusBadgeClass = match ($eventStatus) {
        'published' => 'bg-green-100 text-green-800 ring-green-200',
        'draft' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'cancelled' => 'bg-red-100 text-red-800 ring-red-200',
        default => 'bg-gray-100 text-gray-700 ring-gray-200',
    };
    $eventTypeBadge = '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">' . esc($eventTypeLabel) . '</span>';
    $eventStatusBadge = '<span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset ' . esc($eventStatusBadgeClass) . '">' . esc($eventStatusLabel) . '</span>';
    ?>

    <?= view('components/display/admin_page_header', [
        'backUrl' => route_to('admin.events.events'),
        'backLabel' => 'Events.events_title',
        'eyebrow' => 'Events.events_details',
        'title' => (string) ($event['name'] ?? $event['title'] ?? $event['id'] ?? lang('Events.events_details')),
        'subtitle' => $subtitleParts !== [] ? implode(' · ', $subtitleParts) : null,
        'badge' => $eventStatusBadge,
    ]) ?>

    <?php ob_start(); ?>
    <section class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-700"><?= esc(lang('Events.events_details')) ?></h3>
        </div>
        <dl class="divide-y divide-gray-100">
            <?= view('components/display/field_row', [
                'label' => 'Events.field_slug',
                'value' => $eventSlug !== '' ? '<span class="font-mono text-sm text-gray-700">' . esc($eventSlug) . '</span>' : '—',
                'isHtml' => true,
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Events.field_event_type',
                'value' => $eventTypeBadge,
                'isHtml' => true,
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Events.field_description',
                'value' => $event['description'] ?? '—'
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Events.field_status',
                'value' => $eventStatusBadge,
                'isHtml' => true,
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'TableColumns.created_at',
                'value' => $event['created_at'] ?? '—',
            ]) ?>
            <?= view('components/display/field_row', [
                'label' => 'Events.field_uuid',
                'value' => ! empty($event['uuid']) ? '<span class="font-mono text-xs text-gray-500">' . esc((string) $event['uuid']) . '</span>' : '—',
                'isHtml' => true,
            ]) ?>
        </dl>
    </section>
    <?php $mainContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <a href="<?= route_to('admin.events.events.edit', $itemId) ?>" class="<?= esc(action_button_class('primary')) ?>"><?= lang('App.edit') ?></a>

    <?php $actionsContent = ob_get_clean(); ?>

    <?php ob_start(); ?>
    <form method="post" action="<?= route_to('admin.events.events.delete', $itemId) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($event['title'] ?? null), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
        <button type="submit" class="<?= esc(action_button_class('danger')) ?>">
            <?= ui_icon('trash', 'h-3.5 w-3.5') ?>
            <?= esc(lang('App.delete')) ?>
        </button>
    </form>
    <?php $dangerContent = ob_get_clean(); ?>

    <?= view('components/display/admin_resource_layout', [
        'main' => $mainContent,
        'aside' => view('components/display/admin_actions_panel', [
            'content' => $actionsContent,
            'dangerContent' => $dangerContent,
        ]),
    ]) ?>
<?php endif; ?>
