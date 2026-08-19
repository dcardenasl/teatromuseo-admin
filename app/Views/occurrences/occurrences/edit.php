<?php
$item = $item ?? [];
$occurrenceEventLabel = $events[(string) ($item['event_id'] ?? '')] ?? lang('Occurrences.occurrences_details');
$scheduleTimezone = (string) env('EVENT_SCHEDULE_TIMEZONE', 'America/Santiago');
$occurrenceLabel = trim($occurrenceEventLabel . (! empty($item['start_time']) ? ' · ' . format_date($item['start_time'], null, $scheduleTimezone) : ''));
?>
<?= view('components/display/admin_page_header', [
    'backUrl' => route_to('admin.occurrences.occurrences'),
    'backLabel' => 'App.back',
    'eyebrow' => 'Occurrences.occurrences_title',
    'title' => 'Occurrences.occurrences_edit',
]) ?>

<?= view('layouts/partials/event_lookup_status', ['available' => $lookupAvailable ?? true]) ?>

<?php if (has_permission('event.occurrences.delete')): ?>
    <form id="delete-item-form" method="post" action="<?= route_to('admin.occurrences.occurrences.delete', (string) ($item['id'] ?? '')) ?>" x-data @submit.prevent="$store.confirm.show('<?= esc(confirm_delete_message($occurrenceLabel), 'js') ?>', () => $el.submit())">
        <?= csrf_field() ?>
    </form>
<?php endif; ?>

<form method="post" action="<?= route_to('admin.occurrences.occurrences.update', (string) ($item['id'] ?? '')) ?>" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <?= csrf_field() ?>

    <div class="lg:col-span-2">
        <section class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-900"><?= esc(lang('Occurrences.occurrences_edit')) ?></h3>
            <div class="mt-4 space-y-4">

        <?= view('components/form/relation', [
            'name' => 'event_id',
            'label' => 'Occurrences.field_event_id',
            'required' => true,
            'options' => $events ?? [],
            'placeholder' => 'Occurrences.field_event_id_placeholder',
            'help' => 'Occurrences.field_event_id_help',
            'value' => $item['event_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/relation', [
            'name' => 'venue_id',
            'label' => 'Occurrences.field_venue_id',
            'required' => false,
            'options' => $venues ?? [],
            'placeholder' => 'Occurrences.field_venue_id_placeholder',
            'help' => 'Occurrences.field_venue_id_help',
            'value' => $item['venue_id'] ?? '',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/datetime', [
            'name' => 'start_time',
            'label' => 'Occurrences.field_start_time',
            'required' => true,
            'value' => $item['start_time'] ?? '',
            'placeholder' => 'Occurrences.field_start_time_placeholder',
            'help' => 'Occurrences.field_start_time_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/datetime', [
            'name' => 'end_time',
            'label' => 'Occurrences.field_end_time',
            'required' => true,
            'value' => $item['end_time'] ?? '',
            'placeholder' => 'Occurrences.field_end_time_placeholder',
            'help' => 'Occurrences.field_end_time_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/select', [
            'name' => 'status',
            'label' => 'Occurrences.field_status',
            'required' => true,
            'value' => $item['status'] ?? '',
            'placeholder' => 'Occurrences.field_status_placeholder',
            'help' => 'Occurrences.field_status_help',
            'options' => [
                'draft' => lang('Occurrences.option_status_draft'),
                'published' => lang('Occurrences.option_status_published'),
                'cancelled' => lang('Occurrences.option_status_cancelled'),
            ],
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'capacity',
            'label' => 'Occurrences.field_capacity',
            'required' => true,
            'value' => $item['capacity'] ?? '',
            'placeholder' => 'Occurrences.field_capacity_placeholder',
            'help' => 'Occurrences.field_capacity_help',
            'errors' => $errors ?? []
        ]) ?>

        <?= view('components/form/number', [
            'name' => 'available_spots',
            'label' => 'Occurrences.field_available_spots',
            'required' => true,
            'value' => $item['available_spots'] ?? '',
            'placeholder' => 'Occurrences.field_available_spots_placeholder',
            'help' => 'Occurrences.field_available_spots_help',
            'errors' => $errors ?? []
        ]) ?>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <?= view('components/display/admin_actions_panel', [
            'content' => '<button type="submit" class="' . esc(action_button_class('primary'), 'attr') . '">' . esc(lang('App.update')) . '</button>'
                . '<a href="' . esc(route_to('admin.occurrences.occurrences'), 'attr') . '" class="' . esc(action_button_class(), 'attr') . '">' . esc(lang('App.cancel')) . '</a>',
            'dangerContent' => has_permission('event.occurrences.delete') ? '<button type="submit" form="delete-item-form" class="' . esc(action_button_class('danger'), 'attr') . '">'
                . ui_icon('trash', 'h-3.5 w-3.5') . esc(lang('App.delete')) . '</button>' : '',
        ]) ?>
    </aside>
</form>
